# Heroku 部署指南

## 前置要求

1. ✅ 已创建 Heroku 应用
2. ✅ Git 仓库已初始化
3. 确保已登录 Heroku CLI: `heroku login`

## 部署步骤

### 1. 添加 Heroku 远程仓库

如果还没有添加 Heroku 远程仓库，请先获取您的 Heroku 应用名称，然后执行：

```bash
# 查看所有 Heroku 应用
heroku apps

# 添加 Heroku 远程仓库（替换 your-app-name 为您的实际应用名称）
git remote add heroku https://git.heroku.com/your-app-name.git
```

如果已经添加，可以跳过此步骤。

### 2. 添加 PostgreSQL 数据库

```bash
heroku addons:create heroku-postgresql:mini
```

### 3. 设置环境变量

```bash
# 获取应用名称（用于设置 APP_URL）
APP_NAME=$(heroku apps:info | grep "=== .*" | awk '{print $2}')

# 生成应用密钥并设置
APP_KEY=$(herd php artisan key:generate --show)
heroku config:set APP_KEY="$APP_KEY"

# 设置应用环境
heroku config:set APP_ENV=production

# 设置调试模式（生产环境建议关闭）
heroku config:set APP_DEBUG=false

# 设置应用 URL（替换 your-app-name 为实际应用名称）
heroku config:set APP_URL=https://your-app-name.herokuapp.com

# 设置日志级别
heroku config:set LOG_LEVEL=error
```

### 4. 提交代码更改（如果有未提交的更改）

```bash
# 查看状态
git status

# 如果有更改，提交它们
git add .
git commit -m "准备部署到 Heroku"
```

### 5. 部署代码到 Heroku

```bash
# 推送到 Heroku（根据您的分支名称选择）
git push heroku master
# 或者
# git push heroku main
```

### 6. 运行数据库迁移

```bash
heroku run php artisan migrate --force
```

### 7. 初始化 Laravel Admin

```bash
heroku run php artisan admin:install
```

### 8. 创建管理员用户

```bash
heroku run php artisan admin:create-user
```

按提示输入用户名、邮箱和密码。

### 9. 初始化菜单

```bash
heroku run php artisan db:seed --class=AdminMenuSeeder
```

### 10. 优化缓存

```bash
heroku run php artisan config:cache
heroku run php artisan route:cache
heroku run php artisan view:cache
```

### 11. 打开应用

```bash
heroku open
```

访问后台: `https://your-app-name.herokuapp.com/admin`

## 常用命令

### 查看应用信息

```bash
# 查看应用详情
heroku apps:info

# 查看环境变量
heroku config

# 查看日志
heroku logs --tail

# 查看最近 100 行日志
heroku logs -n 100
```

### 数据库管理

```bash
# 打开数据库命令行
heroku pg:psql

# 备份数据库
heroku pg:backups:capture

# 查看备份列表
heroku pg:backups:list

# 下载备份
heroku pg:backups:download
```

### 运行 Artisan 命令

```bash
# 运行任意 Artisan 命令
heroku run php artisan [command]

# 例如：清除缓存
heroku run php artisan cache:clear

# 例如：查看路由
heroku run php artisan route:list
```

### 环境变量管理

```bash
# 查看所有环境变量
heroku config

# 设置环境变量
heroku config:set KEY=value

# 删除环境变量
heroku config:unset KEY

# 查看单个环境变量
heroku config:get KEY
```

## 故障排查

### 部署失败

1. 查看部署日志:
   ```bash
   heroku logs --tail
   ```

2. 检查环境变量是否正确设置:
   ```bash
   heroku config
   ```

3. 检查 PHP 版本是否兼容:
   ```bash
   heroku run php -v
   ```

### 数据库连接问题

1. 检查数据库是否已添加:
   ```bash
   heroku addons
   ```

2. 检查 DATABASE_URL 环境变量:
   ```bash
   heroku config:get DATABASE_URL
   ```

3. 测试数据库连接:
   ```bash
   heroku pg:psql -c "SELECT version();"
   ```

### 应用无法访问

1. 检查应用是否在运行:
   ```bash
   heroku ps
   ```

2. 查看应用日志:
   ```bash
   heroku logs --tail
   ```

3. 重启应用:
   ```bash
   heroku restart
   ```

## 注意事项

1. **Procfile**: 项目已包含 `Procfile`，Heroku 会自动使用它启动应用
2. **.env 文件**: 不要将 `.env` 文件提交到 Git，使用 `heroku config:set` 设置环境变量
3. **数据库迁移**: 每次部署新代码后，记得运行 `heroku run php artisan migrate --force`
4. **缓存**: 生产环境建议启用配置、路由和视图缓存
5. **日志**: Heroku 会自动收集日志，使用 `heroku logs --tail` 查看实时日志
6. **文件存储**: Heroku 文件系统是只读的（除了 `tmp` 和 `log` 目录），如需文件上传，建议使用 AWS S3 等云存储服务
