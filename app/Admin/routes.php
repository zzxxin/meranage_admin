<?php

use App\Admin\Controllers\StudentController;
use App\Admin\Controllers\TeacherController;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    
    // 覆盖默认的用户管理路由，使用自定义的 UserController（只显示系统管理员）
    $router->resource('auth/users', \App\Admin\Controllers\UserController::class);
    
    // 教师管理路由（系统管理员）
    $router->resource('teachers', TeacherController::class);
    
    // 学生管理路由（教师和系统管理员）
    $router->resource('students', StudentController::class);

});
