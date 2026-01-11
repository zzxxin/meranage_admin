<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 创建教师表
     */
    public function up(): void
    {
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

        // 添加表注释（PostgreSQL）
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
