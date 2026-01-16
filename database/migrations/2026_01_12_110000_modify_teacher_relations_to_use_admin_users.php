<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 删除 teachers 表，修改 students 和 courses 表的外键指向 admin_users
     */
    public function up(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $usersTable = config('admin.database.users_table', 'admin_users');

        // 步骤1：先删除外键约束，以便更新数据
        // 删除 students 表的外键约束
        if (Schema::hasTable('students')) {
            if (config('database.default') === 'pgsql') {
                // PostgreSQL 中，查找并删除外键约束
                $foreignKeys = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'students' 
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name LIKE '%teacher_id%'
                ");
                
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE students DROP CONSTRAINT IF EXISTS {$fk->constraint_name}");
                }
            } else {
                // MySQL/SQLite 等使用列名删除外键
                try {
                    Schema::table('students', function (Blueprint $table) {
                        $table->dropForeign(['teacher_id']);
                    });
                } catch (\Exception $e) {
                    // 忽略错误，外键可能不存在
                }
            }
        }

        // 删除 courses 表的外键约束
        if (Schema::hasTable('courses')) {
            if (config('database.default') === 'pgsql') {
                // PostgreSQL 中，查找并删除外键约束
                $foreignKeys = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'courses' 
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name LIKE '%teacher_id%'
                ");
                
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE courses DROP CONSTRAINT IF EXISTS {$fk->constraint_name}");
                }
            } else {
                // MySQL/SQLite 等使用列名删除外键
                try {
                    Schema::table('courses', function (Blueprint $table) {
                        $table->dropForeign(['teacher_id']);
                    });
                } catch (\Exception $e) {
                    // 忽略错误，外键可能不存在
                }
            }
        }

        // 步骤2：如果 teachers 表存在且有数据，先将数据迁移到 admin_users 表
        if (Schema::hasTable('teachers') && DB::table('teachers')->exists()) {
            $teachers = DB::table('teachers')->get();
            $teacherRole = DB::table('admin_roles')->where('slug', 'teacher')->first();
            $teacherIdMap = []; // 用于映射旧的 teacher_id 到新的 user_id
            
            foreach ($teachers as $teacher) {
                // 检查 admin_users 中是否已存在相同 email 或 username 的用户
                $existingUser = DB::table($usersTable)
                    ->where('email', $teacher->email)
                    ->orWhere('username', $teacher->email)
                    ->first();
                
                if (!$existingUser) {
                    // 创建新用户
                    $userId = DB::table($usersTable)->insertGetId([
                        'username' => $teacher->email,
                        'password' => $teacher->password,
                        'name' => $teacher->name,
                        'email' => $teacher->email,
                        'phone' => $teacher->phone,
                        'remember_token' => $teacher->remember_token,
                        'created_at' => $teacher->created_at,
                        'updated_at' => $teacher->updated_at,
                    ]);
                    
                    // 分配教师角色
                    if ($teacherRole && !DB::table('admin_role_users')->where('role_id', $teacherRole->id)->where('user_id', $userId)->exists()) {
                        DB::table('admin_role_users')->insert([
                            'role_id' => $teacherRole->id,
                            'user_id' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    
                    $teacherIdMap[$teacher->id] = $userId;
                } else {
                    $teacherIdMap[$teacher->id] = $existingUser->id;
                    
                    // 如果用户已存在但没有教师角色，分配角色
                    if ($teacherRole && !DB::table('admin_role_users')->where('role_id', $teacherRole->id)->where('user_id', $existingUser->id)->exists()) {
                        DB::table('admin_role_users')->insert([
                            'role_id' => $teacherRole->id,
                            'user_id' => $existingUser->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            // 步骤3：更新 students 和 courses 表中的 teacher_id
            foreach ($teacherIdMap as $oldTeacherId => $newUserId) {
                DB::table('students')->where('teacher_id', $oldTeacherId)->update(['teacher_id' => $newUserId]);
                DB::table('courses')->where('teacher_id', $oldTeacherId)->update(['teacher_id' => $newUserId]);
            }
        }

        // 步骤4：创建新的外键指向 admin_users
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) use ($usersTable) {
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) use ($usersTable) {
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('cascade');
            });
        }

        // 步骤5：删除 teachers 表（如果存在）
        if (Schema::hasTable('teachers')) {
            Schema::dropIfExists('teachers');
        }

        // 更新注释（PostgreSQL）
        if (config('database.default') === 'pgsql') {
            try {
                if (Schema::hasTable('students')) {
                    DB::statement("COMMENT ON COLUMN students.teacher_id IS '所属教师ID，外键关联admin_users表'");
                }
                if (Schema::hasTable('courses')) {
                    DB::statement("COMMENT ON COLUMN courses.teacher_id IS '创建课程的教师ID，外键关联admin_users表'");
                }
            } catch (\Exception $e) {
                // 忽略注释错误
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

        // 重新创建 teachers 表（如果需要回滚）
        // 注意：回滚时需要先恢复 teachers 表的数据，这里只创建表结构
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
            
            $table->index('email');
            $table->index('name');
        });

        // 恢复 students 表的外键指向 teachers
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['teacher_id']);
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('teachers')
                    ->onDelete('cascade');
            });
        }

        // 恢复 courses 表的外键指向 teachers
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['teacher_id']);
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('teachers')
                    ->onDelete('cascade');
            });
        }
    }
};
