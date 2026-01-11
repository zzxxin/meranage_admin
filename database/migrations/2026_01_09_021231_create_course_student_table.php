<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 创建课程和学生多对多关系中间表
     */
    public function up(): void
    {
        Schema::create('course_student', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade')->comment('课程ID，外键关联courses表');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('学生ID，外键关联students表');
            $table->timestamps();
            
            // 添加唯一约束：一个学生不能重复添加同一个课程
            $table->unique(['course_id', 'student_id'], 'course_student_unique');
            
            // 添加索引
            $table->index('course_id');
            $table->index('student_id');
        });

        // 添加表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE course_student IS '课程学生中间表：存储课程和学生的多对多关系，一个课程可以有多个学生，一个学生可以参加多个课程'");
        DB::statement("COMMENT ON COLUMN course_student.id IS '主键ID'");
        DB::statement("COMMENT ON COLUMN course_student.course_id IS '课程ID，外键关联courses表，级联删除'");
        DB::statement("COMMENT ON COLUMN course_student.student_id IS '学生ID，外键关联students表，级联删除'");
        DB::statement("COMMENT ON COLUMN course_student.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN course_student.updated_at IS '更新时间'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_student');
    }
};
