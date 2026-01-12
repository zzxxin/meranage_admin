# Swagger API 文档

本项目已集成 Swagger/OpenAPI 文档，使用 `darkaonline/l5-swagger` 包。

## 访问文档

启动开发服务器后，访问以下地址查看 API 文档：

```
http://localhost:8000/api/documentation
```

## 配置

Swagger 配置文件位于 `config/l5-swagger.php`。

### 主要配置项

- **文档标题**: Laravel Admin API 文档
- **版本**: 1.0.0
- **访问路径**: `/api/documentation`
- **文档存储路径**: `storage/api-docs/`
- **扫描路径**: `app/` 目录

## 生成文档

当您修改了 API 控制器中的 Swagger 注释后，需要重新生成文档：

```bash
herd php artisan l5-swagger:generate
```

## 添加 API 文档注释

在 API 控制器中使用 OpenAPI 注释来定义 API 端点。示例：

```php
/**
 * @OA\Get(
 *     path="/api/user",
 *     summary="获取当前登录用户信息",
 *     tags={"用户"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="成功获取用户信息",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", example="john@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="未授权",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
public function index(Request $request): JsonResponse
{
    return response()->json($request->user());
}
```

## 安全认证

文档已配置 Bearer Token 认证方案。在 Swagger UI 中可以使用 "Authorize" 按钮输入 Token。

## 当前 API 端点

- `GET /api/user` - 获取当前登录用户信息（需要认证）

## 相关文件

- **配置文件**: `config/l5-swagger.php`
- **API 路由**: `routes/api.php`
- **API 控制器**: `app/Http/Controllers/Api/`
- **生成的文档**: `storage/api-docs/api-docs.json`

## 更多信息

- [L5-Swagger 文档](https://github.com/DarkaOnLine/L5-Swagger)
- [OpenAPI 规范](https://swagger.io/specification/)
- [Swagger PHP 注解文档](https://zircote.github.io/swagger-php/)
