<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 教师模型测试类
 * 测试教师的 CRUD 操作和关联关系
 */
class TeacherTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 测试创建教师
     */
    public function test_can_create_teacher(): void
    {
        $teacherData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'password' => bcrypt('password123'),
        ];

        $teacher = Teacher::create($teacherData);

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'name' => $teacherData['name'],
            'email' => $teacherData['email'],
        ]);

        $this->assertInstanceOf(Teacher::class, $teacher);
    }

    /**
     * 测试查询教师
     */
    public function test_can_query_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $foundTeacher = Teacher::find($teacher->id);

        $this->assertNotNull($foundTeacher);
        $this->assertEquals($teacher->id, $foundTeacher->id);
        $this->assertEquals($teacher->name, $foundTeacher->name);
    }

    /**
     * 测试更新教师
     */
    public function test_can_update_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $newName = $this->faker->name;

        $teacher->update(['name' => $newName]);

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'name' => $newName,
        ]);
    }

    /**
     * 测试删除教师（软删除）
     */
    public function test_can_delete_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $teacherId = $teacher->id;

        $teacher->delete();

        $this->assertSoftDeleted('teachers', ['id' => $teacherId]);
        $this->assertNull(Teacher::find($teacherId));
    }

    /**
     * 测试教师与学生的一对多关系
     */
    public function test_teacher_has_many_students(): void
    {
        $teacher = Teacher::factory()->create();
        $students = Student::factory()->count(3)->create(['teacher_id' => $teacher->id]);

        $this->assertCount(3, $teacher->students);
        $this->assertEquals($students->first()->teacher_id, $teacher->id);
    }

    /**
     * 测试硬删除的级联：硬删除教师时，关联的学生也会被硬删除
     * 注意：软删除不会触发数据库级联删除，只有硬删除才会
     */
    public function test_teacher_hard_delete_cascades_to_students(): void
    {
        $teacher = Teacher::factory()->create();
        $students = Student::factory()->count(2)->create(['teacher_id' => $teacher->id]);
        $studentIds = $students->pluck('id')->toArray();

        // 硬删除教师（绕过软删除）
        $teacher->forceDelete();

        // 检查学生是否也被硬删除（数据库外键约束会触发级联删除）
        foreach ($studentIds as $studentId) {
            $this->assertDatabaseMissing('students', ['id' => $studentId]);
        }
    }

    /**
     * 测试使用预加载查询学生数量
     */
    public function test_avoid_n_plus_one_with_eager_loading(): void
    {
        // 创建 3 个教师，每个教师有 2 个学生
        $teachers = Teacher::factory()->count(3)->create();
        foreach ($teachers as $teacher) {
            Student::factory()->count(2)->create(['teacher_id' => $teacher->id]);
        }

        // 重置查询日志
        DB::enableQueryLog();
        DB::flushQueryLog();

        $teachersWithoutEager = Teacher::all();
        $queriesWithoutEager = count(DB::getQueryLog());

        foreach ($teachersWithoutEager as $teacher) {
            $teacher->students; // 这里会产生额外的查询
        }
        $queriesWithNPlusOne = count(DB::getQueryLog());

        // 重置查询日志
        DB::flushQueryLog();

        $teachersWithEager = Teacher::with('students')->get();
        $queriesWithEager = count(DB::getQueryLog());

        // 使用预加载应该比不使用预加载查询次数少
        $this->assertLessThan($queriesWithNPlusOne, $queriesWithEager, '使用预加载应该减少查询次数');
    }

    /**
     * 测试使用 withCount 预加载学生数量
     */
    public function test_can_use_with_count_to_avoid_n_plus_one(): void
    {
        $teacher = Teacher::factory()->create();
        Student::factory()->count(5)->create(['teacher_id' => $teacher->id]);

        // 重置查询日志
        DB::enableQueryLog();
        DB::flushQueryLog();

        $teachers = Teacher::withCount('students')->get();

        // 应该只需要一次查询
        $this->assertLessThanOrEqual(2, count(DB::getQueryLog()));

        $this->assertEquals(5, $teachers->first()->students_count);
    }
}
