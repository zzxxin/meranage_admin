<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 学生模型测试类
 * 测试学生的 CRUD 操作和关联关系
 */
class StudentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 测试创建学生
     */
    public function test_can_create_student(): void
    {
        $teacher = Teacher::factory()->create();

        $studentData = [
            'teacher_id' => $teacher->id,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'student_number' => $this->faker->unique()->numerify('STU####'),
            'password' => bcrypt('password123'),
        ];

        $student = Student::create($studentData);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => $studentData['name'],
            'email' => $studentData['email'],
            'teacher_id' => $teacher->id,
        ]);

        $this->assertInstanceOf(Student::class, $student);
    }

    /**
     * 测试查询学生
     */
    public function test_can_query_student(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $foundStudent = Student::find($student->id);

        $this->assertNotNull($foundStudent);
        $this->assertEquals($student->id, $foundStudent->id);
        $this->assertEquals($student->name, $foundStudent->name);
    }

    /**
     * 测试更新学生
     */
    public function test_can_update_student(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $newName = $this->faker->name;

        $student->update(['name' => $newName]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => $newName,
        ]);
    }

    /**
     * 测试删除学生（软删除）
     */
    public function test_can_delete_student(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $studentId = $student->id;

        $student->delete();

        $this->assertSoftDeleted('students', ['id' => $studentId]);
        $this->assertNull(Student::find($studentId));
    }

    /**
     * 测试学生与教师的多对一关系
     */
    public function test_student_belongs_to_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $this->assertInstanceOf(Teacher::class, $student->teacher);
        $this->assertEquals($teacher->id, $student->teacher->id);
    }

    /**
     * 测试避免 N+1 问题：使用预加载查询教师信息
     */
    public function test_avoid_n_plus_one_with_eager_loading(): void
    {
        // 创建一个教师和 5 个学生
        $teacher = Teacher::factory()->create();
        $students = Student::factory()->count(5)->create(['teacher_id' => $teacher->id]);

        // 重置查询日志
        DB::enableQueryLog();
        DB::flushQueryLog();

        $studentsWithoutEager = Student::all();
        $initialQueries = count(DB::getQueryLog());

        foreach ($studentsWithoutEager as $student) {
            $student->teacher; // 这里会产生额外的查询
        }
        $queriesWithNPlusOne = count(DB::getQueryLog());

        // 重置查询日志
        DB::flushQueryLog();

        $studentsWithEager = Student::with('teacher')->get();
        $queriesWithEager = count(DB::getQueryLog());

        // 使用预加载应该比不使用预加载查询次数少
        $this->assertLessThan($queriesWithNPlusOne, $queriesWithEager, '使用预加载应该减少查询次数');
    }

    /**
     * 测试学生必须属于一个教师
     */
    public function test_student_must_belong_to_teacher(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Student::create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password123'),
            // 缺少 teacher_id，应该抛出异常
        ]);
    }

    /**
     * 测试学生的邮箱唯一性
     */
    public function test_student_email_must_be_unique(): void
    {
        $teacher = Teacher::factory()->create();
        $email = $this->faker->unique()->safeEmail;

        Student::factory()->create([
            'teacher_id' => $teacher->id,
            'email' => $email,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Student::factory()->create([
            'teacher_id' => $teacher->id,
            'email' => $email, // 重复的邮箱应该抛出异常
        ]);
    }

    /**
     * 测试学生的学号唯一性
     */
    public function test_student_number_must_be_unique(): void
    {
        $teacher = Teacher::factory()->create();
        $studentNumber = $this->faker->unique()->numerify('STU####');

        Student::factory()->create([
            'teacher_id' => $teacher->id,
            'student_number' => $studentNumber,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Student::factory()->create([
            'teacher_id' => $teacher->id,
            'student_number' => $studentNumber, // 重复的学号应该抛出异常
        ]);
    }
}
