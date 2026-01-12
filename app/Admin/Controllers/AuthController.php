<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;

class AuthController extends BaseAuthController
{
    /**
     * 登录页面标题
     *
     * @return string|\Illuminate\Contracts\Translation\Translator|null
     */
    protected function loginTitle()
    {
        return '教育系统管理后台';
    }
}
