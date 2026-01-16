<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 为 admin_users 表添加 email 和 phone 字段
     */
    public function up(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $usersTable = config('admin.database.users_table', 'admin_users');

        // PostgreSQL 不支持 after()，直接添加字段
        if (!Schema::connection($connection)->hasColumn($usersTable, 'email')) {
            Schema::connection($connection)->table($usersTable, function (Blueprint $table) {
                $table->string('email')->nullable()->unique();
            });
        }
        
        if (!Schema::connection($connection)->hasColumn($usersTable, 'phone')) {
            Schema::connection($connection)->table($usersTable, function (Blueprint $table) {
                $table->string('phone')->nullable();
            });
        }

        // 添加字段注释（PostgreSQL）
        if (config('database.default') === 'pgsql') {
            try {
                DB::statement("COMMENT ON COLUMN {$usersTable}.email IS '邮箱'");
                DB::statement("COMMENT ON COLUMN {$usersTable}.phone IS '联系电话'");
            } catch (\Exception $e) {
                // 忽略注释错误（如果字段不存在或已存在注释）
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

        Schema::connection($connection)->table($usersTable, function (Blueprint $table) {
            $table->dropColumn(['email', 'phone']);
        });
    }
};
