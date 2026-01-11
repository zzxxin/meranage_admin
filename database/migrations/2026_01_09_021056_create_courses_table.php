<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 创建课程表
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id()->comment('课程ID');
            $table->string('name')->comment('课程名');
            $table->string('year_month', 6)->comment('年月（格式：202310）');
            $table->decimal('fee', 10, 2)->comment('课程费用');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade')->comment('创建课程的教师ID，外键关联teachers表');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间');
            
            // 添加索引
            $table->index('teacher_id');
            $table->index('year_month');
            $table->index('name');
        });

        // 添加表注释（PostgreSQL）
        DB::statement("COMMENT ON TABLE courses IS '课程表：存储课程基本信息，包括课程名、年月、费用等，教师可以创建课程'");
        DB::statement("COMMENT ON COLUMN courses.id IS '课程ID，主键'");
        DB::statement("COMMENT ON COLUMN courses.name IS '课程名'");
        DB::statement("COMMENT ON COLUMN courses.year_month IS '年月，格式为YYYYMM，例如：202310'");
        DB::statement("COMMENT ON COLUMN courses.fee IS '课程费用，单位：元'");
        DB::statement("COMMENT ON COLUMN courses.teacher_id IS '创建课程的教师ID，外键关联teachers表，级联删除'");
        DB::statement("COMMENT ON COLUMN courses.created_at IS '创建时间'");
        DB::statement("COMMENT ON COLUMN courses.updated_at IS '更新时间'");
        DB::statement("COMMENT ON COLUMN courses.deleted_at IS '软删除时间'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
