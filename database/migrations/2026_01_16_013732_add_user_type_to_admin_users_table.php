<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 为 admin_users 表添加 user_type 字段，用于区分系统管理员和教师
     */
    public function up(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $usersTable = config('admin.database.users_table', 'admin_users');

        // 添加 user_type 字段
        if (!Schema::connection($connection)->hasColumn($usersTable, 'user_type')) {
            if (config('database.default') === 'pgsql') {
                // PostgreSQL 使用枚举类型
                DB::statement("ALTER TABLE {$usersTable} ADD COLUMN user_type VARCHAR(20) DEFAULT 'administrator'");
                DB::statement("ALTER TABLE {$usersTable} ADD CONSTRAINT {$usersTable}_user_type_check CHECK (user_type IN ('administrator', 'teacher'))");
            } else {
                // MySQL 等其他数据库
                Schema::connection($connection)->table($usersTable, function (Blueprint $table) {
                    $table->enum('user_type', ['administrator', 'teacher'])->default('administrator')->after('name')->comment('用户类型：administrator系统管理员，teacher教师');
                });
            }

            // 添加索引
            Schema::connection($connection)->table($usersTable, function (Blueprint $table) {
                $table->index('user_type');
            });

            // 根据角色设置现有的用户类型
            // 如果用户有教师角色，设置为 teacher；否则设置为 administrator
            $teacherRole = DB::table('admin_roles')->where('slug', 'teacher')->first();
            if ($teacherRole) {
                // 获取所有有教师角色的用户ID
                $teacherUserIds = DB::table('admin_role_users')
                    ->where('role_id', $teacherRole->id)
                    ->pluck('user_id')
                    ->toArray();

                if (!empty($teacherUserIds)) {
                    // 设置教师类型
                    DB::table($usersTable)
                        ->whereIn('id', $teacherUserIds)
                        ->update(['user_type' => 'teacher']);
                }
            }

            // 添加字段注释（PostgreSQL）
            if (config('database.default') === 'pgsql') {
                try {
                    DB::statement("COMMENT ON COLUMN {$usersTable}.user_type IS '用户类型：administrator系统管理员，teacher教师'");
                } catch (\Exception $e) {
                    // 忽略注释错误
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $usersTable = config('admin.database.users_table', 'admin_users');

        if (Schema::connection($connection)->hasColumn($usersTable, 'user_type')) {
            Schema::connection($connection)->table($usersTable, function (Blueprint $table) {
                $table->dropColumn('user_type');
            });
        }
    }
};
