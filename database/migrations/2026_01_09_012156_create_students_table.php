<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 创建学生表
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id()->comment('学生ID');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade')->comment('所属教师ID，外键关联teachers表');
            $table->string('name')->comment('学生姓名');
            $table->string('email')->unique()->comment('学生邮箱');
            $table->string('phone')->nullable()->comment('联系电话');
            $table->string('student_number')->nullable()->unique()->comment('学号');
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱验证时间');
            $table->string('password')->comment('密码');
            $table->rememberToken()->comment('记住我令牌');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');
            
            // 添加索引
            $table->index('teacher_id');
            $table->index('email');
            $table->index('name');
            $table->index('student_number');
        });

        // 添加表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE students IS '学生表：存储学生基本信息，教师可以管理学生，一个教师可以管理多个学生（一对多关系）'");
        DB::statement("COMMENT ON COLUMN students.id IS '学生ID，主键'");
        DB::statement("COMMENT ON COLUMN students.teacher_id IS '所属教师ID，外键关联teachers表，级联删除'");
        DB::statement("COMMENT ON COLUMN students.name IS '学生姓名'");
        DB::statement("COMMENT ON COLUMN students.email IS '学生邮箱，唯一索引，用于登录'");
        DB::statement("COMMENT ON COLUMN students.phone IS '联系电话'");
        DB::statement("COMMENT ON COLUMN students.student_number IS '学号，唯一索引'");
        DB::statement("COMMENT ON COLUMN students.email_verified_at IS '邮箱验证时间'");
        DB::statement("COMMENT ON COLUMN students.password IS '加密后的密码'");
        DB::statement("COMMENT ON COLUMN students.remember_token IS '记住我功能令牌'");
        DB::statement("COMMENT ON COLUMN students.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN students.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN students.deleted_at IS '软删除时间'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
