<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 只运行菜单 Seeder，不运行角色权限 Seeder
        // 角色和权限由用户在后台管理界面自行创建和管理
        $this->call([
            AdminMenuSeeder::class,
        ]);
    }
}
