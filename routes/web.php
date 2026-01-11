<?php

use Illuminate\Support\Facades\Route;

// 默认路由重定向到 Laravel Admin 登录页面
Route::get('/', function () {
    return redirect('/admin');
});

// Laravel Admin 路由
require __DIR__.'/../app/Admin/routes.php';