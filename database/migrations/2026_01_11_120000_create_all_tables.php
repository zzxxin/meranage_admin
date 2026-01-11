<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 创建所有业务表（基于当前稳定的数据库结构）
     */
    public function up(): void
    {
        // 创建教师表
        Schema::create('teachers', function (Blueprint $table) {
            $table->id()->comment('教师ID');
            $table->string('name')->comment('教师姓名');
            $table->string('email')->unique()->comment('教师邮箱');
            $table->string('phone')->nullable()->comment('联系电话');
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱验证时间');
            $table->string('password')->comment('密码');
            $table->rememberToken()->comment('记住我令牌');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');
            
            // 添加索引
            $table->index('email');
            $table->index('name');
        });

        // 添加教师表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE teachers IS '教师表：存储教师基本信息，系统管理员可以管理教师'");
        DB::statement("COMMENT ON COLUMN teachers.id IS '教师ID，主键'");
        DB::statement("COMMENT ON COLUMN teachers.name IS '教师姓名'");
        DB::statement("COMMENT ON COLUMN teachers.email IS '教师邮箱，唯一索引，用于登录'");
        DB::statement("COMMENT ON COLUMN teachers.phone IS '联系电话'");
        DB::statement("COMMENT ON COLUMN teachers.email_verified_at IS '邮箱验证时间'");
        DB::statement("COMMENT ON COLUMN teachers.password IS '加密后的密码'");
        DB::statement("COMMENT ON COLUMN teachers.remember_token IS '记住我功能令牌'");
        DB::statement("COMMENT ON COLUMN teachers.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN teachers.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN teachers.deleted_at IS '软删除时间'");

        // 创建学生表
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

        // 添加学生表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE students IS '学生表：存储学生基本信息，教师可以管理学生'");
        DB::statement("COMMENT ON COLUMN students.id IS '学生ID，主键'");
        DB::statement("COMMENT ON COLUMN students.teacher_id IS '所属教师ID，外键关联teachers表'");
        DB::statement("COMMENT ON COLUMN students.name IS '学生姓名'");
        DB::statement("COMMENT ON COLUMN students.email IS '学生邮箱，唯一索引'");
        DB::statement("COMMENT ON COLUMN students.phone IS '联系电话'");
        DB::statement("COMMENT ON COLUMN students.student_number IS '学号，唯一索引'");
        DB::statement("COMMENT ON COLUMN students.email_verified_at IS '邮箱验证时间'");
        DB::statement("COMMENT ON COLUMN students.password IS '加密后的密码'");
        DB::statement("COMMENT ON COLUMN students.remember_token IS '记住我功能令牌'");
        DB::statement("COMMENT ON COLUMN students.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN students.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN students.deleted_at IS '软删除时间'");

        // 创建课程表
        Schema::create('courses', function (Blueprint $table) {
            $table->id()->comment('课程ID');
            $table->string('name')->comment('课程名');
            $table->string('year_month', 6)->comment('年月（格式：202310）');
            $table->decimal('fee', 10, 2)->comment('课程费用');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade')->comment('创建课程的教师ID');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');
            
            // 添加索引
            $table->index('teacher_id');
            $table->index('year_month');
            $table->index('name');
        });

        // 添加课程表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE courses IS '课程表：存储课程基本信息'");
        DB::statement("COMMENT ON COLUMN courses.id IS '课程ID，主键'");
        DB::statement("COMMENT ON COLUMN courses.name IS '课程名'");
        DB::statement("COMMENT ON COLUMN courses.year_month IS '课程所属年月，格式：YYYYMM'");
        DB::statement("COMMENT ON COLUMN courses.fee IS '课程费用'");
        DB::statement("COMMENT ON COLUMN courses.teacher_id IS '创建该课程的教师ID'");
        DB::statement("COMMENT ON COLUMN courses.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN courses.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN courses.deleted_at IS '软删除时间'");

        // 创建账单表
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->comment('账单ID');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade')->comment('关联的课程ID');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('关联的学生ID');
            $table->string('year_month', 6)->comment('年月（格式：202310）');
            $table->decimal('amount', 10, 2)->comment('账单金额');
            $table->string('status', 20)->default('pending')->comment('账单状态（pending待发送、sent已发送、paid已支付、cancelled已取消）');
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');
            
            // 添加索引
            $table->index('course_id');
            $table->index('student_id');
            $table->index('year_month');
            $table->index('status');
        });

        // 添加账单表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE invoices IS '账单表：针对指定年月的课程费用发起请款'");
        DB::statement("COMMENT ON COLUMN invoices.id IS '账单ID，主键'");
        DB::statement("COMMENT ON COLUMN invoices.course_id IS '关联的课程ID'");
        DB::statement("COMMENT ON COLUMN invoices.student_id IS '关联的学生ID'");
        DB::statement("COMMENT ON COLUMN invoices.year_month IS '账单所属年月，格式：YYYYMM'");
        DB::statement("COMMENT ON COLUMN invoices.amount IS '账单金额'");
        DB::statement("COMMENT ON COLUMN invoices.status IS '账单状态'");
        DB::statement("COMMENT ON COLUMN invoices.sent_at IS '账单发送时间'");
        DB::statement("COMMENT ON COLUMN invoices.paid_at IS '账单支付时间'");
        DB::statement("COMMENT ON COLUMN invoices.remark IS '备注信息'");
        DB::statement("COMMENT ON COLUMN invoices.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN invoices.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN invoices.deleted_at IS '软删除时间'");

        // 创建课程-学生中间表
        Schema::create('course_student', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade')->comment('课程ID');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('学生ID');
            $table->timestamps();

            // 确保一个学生不能重复添加同一个课程
            $table->unique(['course_id', 'student_id']);
        });

        // 添加课程-学生中间表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE course_student IS '课程-学生中间表：存储课程和学生的多对多关系'");
        DB::statement("COMMENT ON COLUMN course_student.id IS '主键ID'");
        DB::statement("COMMENT ON COLUMN course_student.course_id IS '课程ID'");
        DB::statement("COMMENT ON COLUMN course_student.student_id IS '学生ID'");
        DB::statement("COMMENT ON COLUMN course_student.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN course_student.updated_at IS '更新时间'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_student');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
    }
};
