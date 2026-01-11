<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 管理员菜单 Seeder
 * 用于添加教师管理和学生管理菜单项
 */
class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清空现有菜单（可选，根据需求决定）
        // DB::table('admin_menu')->truncate();

        // 获取最大的 order 值
        $maxOrder = DB::table('admin_menu')->max('order') ?? 0;

        // 添加教师管理菜单（仅系统管理员可见）
        $teacherMenuId = DB::table('admin_menu')->insertGetId([
            'parent_id' => 0,
            'order' => $maxOrder + 1,
            'title' => '教师管理',
            'icon' => 'fa-users',
            'uri' => 'teachers',
            'permission' => 'teacher.manage', // 只有拥有教师管理权限的角色可见
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 添加学生管理菜单（教师和系统管理员可见）
        DB::table('admin_menu')->insert([
            'parent_id' => 0,
            'order' => $maxOrder + 2,
            'title' => '学生管理',
            'icon' => 'fa-graduation-cap',
            'uri' => 'students',
            'permission' => 'student.manage', // 只有拥有学生管理权限的角色可见
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('管理员菜单添加成功！');
    }
}
