<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Laravel Admin API 文档",
 *     description="基于 Laravel 10 和 Laravel Admin 的后台管理系统 API 文档",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="API 服务器地址"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="使用 Bearer Token 进行身份验证"
 * )
 */
class BaseController extends Controller
{
    //
}
