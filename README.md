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

## 注意事项

1. 本地开发使用 Herd，所有 Artisan 命令前需要加 `herd` 前缀
2. 数据库使用 PostgreSQL，确保已正确配置
3. 所有表均支持软删除，删除操作不会真正删除数据
4. 权限管理通过 Laravel Admin 的 RBAC 系统实现
5. 默认账号密码为 `admin`，生产环境请务必修改

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
