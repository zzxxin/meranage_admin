<?php

namespace Database\Seeders;

use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Database\Seeder;

/**
 * 角色和权限 Seeder
 * 创建系统管理员和教师角色，并分配相应的权限
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建权限
        $teacherManagePermission = Permission::firstOrCreate([
            'slug' => 'teacher.manage',
        ], [
            'name' => '教师管理',
            'slug' => 'teacher.manage',
            'http_method' => '',
            'http_path' => '/teachers*',
        ]);

        $studentManagePermission = Permission::firstOrCreate([
            'slug' => 'student.manage',
        ], [
            'name' => '学生管理',
            'slug' => 'student.manage',
            'http_method' => '',
            'http_path' => '/students*',
        ]);

        // 创建角色：系统管理员
        $administratorRole = Role::firstOrCreate([
            'slug' => 'administrator',
        ], [
            'name' => '系统管理员',
            'slug' => 'administrator',
        ]);

        // 创建角色：教师
        $teacherRole = Role::firstOrCreate([
            'slug' => 'teacher',
        ], [
            'name' => '教师',
            'slug' => 'teacher',
        ]);

        // 分配权限到角色
        // 系统管理员拥有教师管理权限
        if (!$administratorRole->permissions()->where('slug', 'teacher.manage')->exists()) {
            $administratorRole->permissions()->attach($teacherManagePermission->id);
        }

        // 教师拥有学生管理权限
        if (!$teacherRole->permissions()->where('slug', 'student.manage')->exists()) {
            $teacherRole->permissions()->attach($studentManagePermission->id);
        }

        // 系统管理员也拥有学生管理权限（可选，根据需求决定）
        // if (!$administratorRole->permissions()->where('slug', 'student.manage')->exists()) {
        //     $administratorRole->permissions()->attach($studentManagePermission->id);
        // }

        $this->command->info('角色和权限创建成功！');
        $this->command->info('- 系统管理员角色：拥有教师管理权限');
        $this->command->info('- 教师角色：拥有学生管理权限');
    }
}
