# Laravel Admin 后台管理系统

基于 Laravel 10 和 Laravel Admin 的后台管理系统，提供教师管理和学生管理功能。

## 技术栈

- **PHP**: 8.2+
- **框架**: Laravel 10.x
- **后台管理**: Laravel Admin 1.8
- **数据库**: PostgreSQL
- **环境**: Herd (本地开发)

## 功能特性

### 1. 教师管理
- 教师信息的增删改查
- 教师列表展示（支持搜索、排序）
- 教师详情查看
- 软删除支持

### 2. 学生管理
- 学生信息的增删改查
- 学生列表展示（支持搜索、排序、筛选）
- 学生详情查看
- 学生与教师的关联管理
- 软删除支持

### 3. 权限管理
- 基于角色的权限控制（RBAC）
- 教师管理权限（`teacher.manage`）
- 学生管理权限（`student.manage`）
- Dashboard 权限（`dashboard`）

### 4. 数据库设计
- **teachers**: 教师表
- **students**: 学生表
- **courses**: 课程表
- **invoices**: 账单表
- **course_student**: 课程-学生关联表

所有表均支持软删除（`deleted_at`）。

## 环境要求

- PHP >= 8.2
- Composer
- PostgreSQL
- Herd (推荐，用于本地开发)

## 安装步骤

### 1. 克隆项目

```bash
git clone git@github.com:zzxxin/meranage_admin.git
cd meranage_admin
```

### 2. 安装依赖

```bash
composer install
```

### 3. 配置环境

复制 `.env.example` 为 `.env` 并配置数据库连接：

```bash
cp .env.example .env
```

编辑 `.env` 文件，配置 PostgreSQL 数据库：

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. 生成应用密钥

```bash
herd php artisan key:generate
```

### 5. 运行数据库迁移

```bash
herd php artisan migrate
```

### 6. 初始化 Laravel Admin

```bash
herd php artisan admin:install
```

### 7. 创建管理员用户

```bash
herd php artisan admin:create-user
```

### 8. 初始化菜单和权限

```bash
herd php artisan db:seed --class=AdminMenuSeeder
```

手动创建权限（可选）：
- 教师管理权限（`teacher.manage`）
- 学生管理权限（`student.manage`）

## 默认账号

- **用户名**: `admin`
- **密码**: `admin`（首次登录后请修改）
- **权限**: 全部权限（`*`）

## 开发命令

### 启动开发服务器

```bash
herd php artisan serve --host=0.0.0.0 --port=8000
```

访问: `http://localhost:8000/admin`

### 运行测试

```bash
herd php artisan test
```

### 清除缓存

```bash
herd php artisan cache:clear
herd php artisan config:clear
herd php artisan route:clear
herd php artisan view:clear
```

## 权限说明

系统使用 Laravel Admin 的 RBAC（基于角色的权限控制）系统：

1. **All permission (`*`)**: 全部权限，分配给 Administrator 角色
2. **Dashboard (`dashboard`)**: Dashboard 访问权限
3. **教师管理 (`teacher.manage`)**: 教师管理权限
4. **学生管理 (`student.manage`)**: 学生管理权限

默认 `admin` 账号拥有 `Administrator` 角色，具有全部权限。

## 目录结构

```
app/
├── Admin/
│   ├── Controllers/
│   │   ├── HomeController.php      # 首页控制器
│   │   ├── TeacherController.php   # 教师管理控制器
│   │   └── StudentController.php   # 学生管理控制器
│   └── routes.php                  # 路由配置
├── Models/
│   ├── Teacher.php                 # 教师模型
│   ├── Student.php                 # 学生模型
│   ├── Course.php                  # 课程模型
│   └── Invoice.php                 # 账单模型
database/
├── migrations/                     # 数据库迁移文件
└── seeders/
    └── AdminMenuSeeder.php         # 菜单 Seeder
```

## Heroku 部署

### 前置要求

1. 安装 [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli)
2. 注册 Heroku 账号
3. 登录 Heroku: `heroku login`

### 部署步骤

#### 1. 创建 Heroku 应用

```bash
heroku create your-app-name
```

#### 2. 添加 PostgreSQL 插件

```bash
heroku addons:create heroku-postgresql:mini
```

#### 3. 设置环境变量

```bash
# 设置应用密钥（Heroku 会自动设置，但可以手动设置）
heroku config:set APP_KEY=$(php artisan key:generate --show)

# 设置应用环境
heroku config:set APP_ENV=production

# 设置调试模式（生产环境建议关闭）
heroku config:set APP_DEBUG=false

# 设置应用 URL
heroku config:set APP_URL=https://your-app-name.herokuapp.com

# 设置日志级别
heroku config:set LOG_LEVEL=error
```

#### 4. 配置数据库

PostgreSQL 数据库连接会自动配置，Heroku 会自动设置 `DATABASE_URL` 环境变量。

如果需要手动配置，可以查看数据库连接信息：

```bash
heroku config:get DATABASE_URL
```

#### 5. 部署代码

```bash
# 添加 Heroku 远程仓库（如果还没有）
git remote add heroku https://git.heroku.com/your-app-name.git

# 推送到 Heroku
git push heroku main
# 或者使用 master 分支
# git push heroku master
```

#### 6. 运行数据库迁移

```bash
heroku run php artisan migrate --force
```

#### 7. 初始化 Laravel Admin

```bash
heroku run php artisan admin:install
```

#### 8. 创建管理员用户

```bash
heroku run php artisan admin:create-user
```

#### 9. 初始化菜单

```bash
heroku run php artisan db:seed --class=AdminMenuSeeder
```

#### 10. 清除缓存

```bash
heroku run php artisan config:cache
heroku run php artisan route:cache
heroku run php artisan view:cache
```

#### 11. 打开应用

```bash
heroku open
```

访问: `https://your-app-name.herokuapp.com/admin`

### Heroku 常用命令

```bash
# 查看应用信息
heroku info

# 查看日志
heroku logs --tail

# 运行 Artisan 命令
heroku run php artisan [command]

# 查看环境变量
heroku config

# 设置环境变量
heroku config:set KEY=value

# 删除环境变量
heroku config:unset KEY

# 打开数据库命令行
heroku pg:psql

# 备份数据库
heroku pg:backups:capture

# 查看数据库备份
heroku pg:backups:list
```

### 注意事项

1. **Procfile**: 项目已包含 `Procfile`，指定了 Web 服务器启动命令
2. **PHP 版本**: 确保 `composer.json` 中指定的 PHP 版本符合 Heroku 的要求
3. **存储**: Heroku 文件系统是只读的（除了 `tmp` 和 `log` 目录），如需文件上传，建议使用 AWS S3 等云存储服务
4. **日志**: Heroku 会自动收集日志，使用 `heroku logs --tail` 查看
5. **数据库迁移**: 每次部署后需要运行 `heroku run php artisan migrate --force`
6. **缓存**: 生产环境建议启用配置缓存和路由缓存
7. **HTTPS**: Heroku 默认提供 HTTPS，确保 `APP_URL` 设置为 HTTPS 地址

### 环境变量配置

在 Heroku 上，以下环境变量需要配置：

- `APP_ENV`: 设置为 `production`
- `APP_DEBUG`: 设置为 `false`（生产环境）
- `APP_URL`: 应用的 URL（HTTPS）
- `APP_KEY`: 应用密钥（自动生成）
- `DATABASE_URL`: PostgreSQL 数据库连接（自动设置）
- `LOG_LEVEL`: 日志级别（建议 `error`）

### 数据库迁移策略

在 Heroku 上运行迁移时，建议：

1. 首次部署时运行完整迁移
2. 后续部署时只运行新的迁移
3. 使用 `--force` 标志（生产环境）
4. 迁移前备份数据库：`heroku pg:backups:capture`

### 故障排查

如果部署后遇到问题：

1. 查看日志: `heroku logs --tail`
2. 检查环境变量: `heroku config`
3. 测试数据库连接: `heroku run php artisan tinker`
4. 清除缓存: `heroku run php artisan config:clear`

## 注意事项

1. 本地开发使用 Herd，所有 Artisan 命令前需要加 `herd` 前缀
2. 数据库使用 PostgreSQL，确保已正确配置
3. 所有表均支持软删除，删除操作不会真正删除数据
4. 权限管理通过 Laravel Admin 的 RBAC 系统实现
5. 默认账号密码为 `admin`，生产环境请务必修改
6. Heroku 部署时注意配置环境变量和运行迁移

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
