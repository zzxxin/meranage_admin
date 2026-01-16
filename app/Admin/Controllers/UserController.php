<?php

namespace App\Admin\Controllers;

use App\Models\AdminUser;
use Encore\Admin\Controllers\UserController as BaseUserController;

/**
 * 用户管理控制器
 * 覆盖默认的用户管理，只显示系统管理员（user_type = 'administrator'），排除教师
 */
class UserController extends BaseUserController
{
    /**
     * 构建列表页面
     * 只显示系统管理员，不显示教师
     *
     * @return \Encore\Admin\Grid
     */
    public function grid()
    {
        $grid = parent::grid();
        
        // 只显示系统管理员（user_type = 'administrator'），排除教师
        $grid->model()->where('user_type', 'administrator');
        
        return $grid;
    }
}
