# Heroku 部署操作文档

## 目标应用

- **应用名称**: `poper-ops-interview-04`
- **组织**: `poper-dev@herokumanager.com`
- **部署方式**: GitHub 自动部署（已授权连接）

## 部署方式说明

✅ **此应用已连接到 GitHub 仓库**，可以直接通过推送代码到 GitHub 进行自动部署。

有两种部署方式：

1. **GitHub 自动部署（推荐）**：推送代码到 GitHub，Heroku 会自动检测并部署
2. **Git 直接部署**：使用 `git push heroku` 直接推送到 Heroku（如果需要在本地直接部署）

## 部署步骤

### 方式一：GitHub 自动部署（推荐）

#### 1. 确保代码已推送到 GitHub

```bash
# 查看当前状态
git status

# 添加更改
git add .

# 提交更改
git commit -m "部署到 Heroku"

# 推送到 GitHub（确保推送到正确的分支）
git push origin main
# 或
# git push origin master
```

**注意**: 确保推送到 GitHub 的分支与 Heroku 应用配置的分支一致。

#### 2. 检查 Heroku 部署状态

```bash
# 查看最近的部署日志
heroku releases -a poper-ops-interview-04

# 查看实时日志
heroku logs --tail -a poper-ops-interview-04
```

Heroku 会自动检测到 GitHub 的推送并开始部署。

### 方式二：Git 直接部署（可选）

如果需要在本地直接部署（不通过 GitHub），可以添加 Heroku Git 远程仓库：

```bash
# 添加 Heroku 远程仓库（可选，仅用于直接部署）
git remote add heroku https://git.heroku.com/poper-ops-interview-04.git

# 直接推送到 Heroku
git push heroku main
```

**注意**: 如果使用 GitHub 自动部署，通常不需要此步骤。

### 2. 检查应用状态

```bash
# 查看应用信息
heroku apps:info -a poper-ops-interview-04

# 查看当前环境变量
heroku config -a poper-ops-interview-04
```

### 3. 添加 PostgreSQL 数据库

检查是否已有 PostgreSQL 数据库：

```bash
heroku addons -a poper-ops-interview-04
```

如果没有 PostgreSQL 数据库，添加一个：

```bash
heroku addons:create heroku-postgresql:mini -a poper-ops-interview-04
```

### 4. 设置环境变量

```bash
# 设置应用密钥（如果还没有）
APP_KEY=$(herd php artisan key:generate --show)
heroku config:set APP_KEY="$APP_KEY" -a poper-ops-interview-04

# 设置应用环境
heroku config:set APP_ENV=production -a poper-ops-interview-04

# 设置调试模式（生产环境建议关闭）
heroku config:set APP_DEBUG=false -a poper-ops-interview-04

# 设置应用 URL
heroku config:set APP_URL=https://poper-ops-interview-04.herokuapp.com -a poper-ops-interview-04

# 设置日志级别
heroku config:set LOG_LEVEL=error -a poper-ops-interview-04
```

**注意**: PostgreSQL 数据库连接信息会自动通过 `DATABASE_URL` 环境变量配置，无需手动设置。

### 3. 提交并推送代码到 GitHub（首次部署前）

如果这是首次部署，需要先配置数据库和环境变量（见下方步骤）。

如果已经配置好，只需推送代码：

```bash
# 查看状态
git status

# 添加更改
git add .

# 提交更改
git commit -m "部署更新"

# 推送到 GitHub（Heroku 会自动检测并部署）
git push origin main
# 或
# git push origin master
```

**注意**: 
- 推送后，Heroku 会自动检测并开始部署
- 可以在 Heroku Dashboard 或使用 `heroku logs --tail -a poper-ops-interview-04` 查看部署进度

### 4. 运行数据库迁移（首次部署）

```bash
heroku run php artisan migrate --force -a poper-ops-interview-04
```

### 8. 初始化 Laravel Admin

```bash
heroku run php artisan admin:install -a poper-ops-interview-04
```

### 9. 创建管理员用户

```bash
heroku run php artisan admin:create-user -a poper-ops-interview-04
```

按提示输入：
- 用户名（例如：`admin`）
- 密码
- 邮箱

### 10. 初始化菜单

```bash
heroku run php artisan db:seed --class=AdminMenuSeeder -a poper-ops-interview-04
```

### 11. 优化缓存

```bash
heroku run php artisan config:cache -a poper-ops-interview-04
heroku run php artisan route:cache -a poper-ops-interview-04
heroku run php artisan view:cache -a poper-ops-interview-04
```

### 12. 打开应用

```bash
heroku open -a poper-ops-interview-04
```

访问后台: `https://poper-ops-interview-04.herokuapp.com/admin`

## 一键部署脚本

如果您想快速部署，可以将以下命令保存为脚本：

```bash
#!/bin/bash

# Heroku 应用名称
APP_NAME="poper-ops-interview-04"

# 添加远程仓库（如果还没有）
if ! git remote | grep -q "heroku"; then
    echo "添加 Heroku 远程仓库..."
    git remote add heroku https://git.heroku.com/${APP_NAME}.git
fi

# 检查并添加 PostgreSQL
echo "检查 PostgreSQL 数据库..."
if ! heroku addons -a ${APP_NAME} | grep -q "postgresql"; then
    echo "添加 PostgreSQL 数据库..."
    heroku addons:create heroku-postgresql:mini -a ${APP_NAME}
fi

# 设置环境变量
echo "设置环境变量..."
if [ -z "$(heroku config:get APP_KEY -a ${APP_NAME})" ]; then
    APP_KEY=$(herd php artisan key:generate --show)
    heroku config:set APP_KEY="$APP_KEY" -a ${APP_NAME}
fi

heroku config:set APP_ENV=production -a ${APP_NAME}
heroku config:set APP_DEBUG=false -a ${APP_NAME}
heroku config:set APP_URL=https://${APP_NAME}.herokuapp.com -a ${APP_NAME}
heroku config:set LOG_LEVEL=error -a ${APP_NAME}

# 部署代码
echo "部署代码到 Heroku..."
git push heroku master

# 运行迁移
echo "运行数据库迁移..."
heroku run php artisan migrate --force -a ${APP_NAME}

# 初始化 Laravel Admin
echo "初始化 Laravel Admin..."
heroku run php artisan admin:install -a ${APP_NAME}

# 初始化菜单
echo "初始化菜单..."
heroku run php artisan db:seed --class=AdminMenuSeeder -a ${APP_NAME}

# 优化缓存
echo "优化缓存..."
heroku run php artisan config:cache -a ${APP_NAME}
heroku run php artisan route:cache -a ${APP_NAME}
heroku run php artisan view:cache -a ${APP_NAME}

echo "部署完成！"
echo "访问地址: https://${APP_NAME}.herokuapp.com/admin"
echo "注意：请运行 'heroku run php artisan admin:create-user -a ${APP_NAME}' 创建管理员用户"
```

## 部署流程总结

### 首次部署流程

1. ✅ 配置 PostgreSQL 数据库（步骤 3）
2. ✅ 设置环境变量（步骤 4）
3. ✅ 推送代码到 GitHub
4. ✅ 运行数据库迁移和初始化（步骤 4）
5. ✅ 创建管理员用户
6. ✅ 访问应用

### 日常更新流程

1. 修改代码
2. 提交并推送到 GitHub：`git push origin main`
3. Heroku 自动部署
4. 如有新迁移，运行：`heroku run php artisan migrate --force -a poper-ops-interview-04`

### 在 Heroku Dashboard 中管理部署

您也可以在 Heroku Dashboard 中：
- 查看部署历史
- 手动触发重新部署
- 配置自动部署的分支
- 启用/禁用自动部署

## 常用命令

### 查看应用信息

```bash
# 查看应用详情
heroku apps:info -a poper-ops-interview-04

# 查看环境变量
heroku config -a poper-ops-interview-04

# 查看日志
heroku logs --tail -a poper-ops-interview-04

# 查看最近 100 行日志
heroku logs -n 100 -a poper-ops-interview-04
```

### 数据库管理

```bash
# 打开数据库命令行
heroku pg:psql -a poper-ops-interview-04

# 备份数据库
heroku pg:backups:capture -a poper-ops-interview-04

# 查看备份列表
heroku pg:backups:list -a poper-ops-interview-04

# 下载备份
heroku pg:backups:download -a poper-ops-interview-04
```

### 运行 Artisan 命令

```bash
# 运行任意 Artisan 命令
heroku run php artisan [command] -a poper-ops-interview-04

# 例如：清除缓存
heroku run php artisan cache:clear -a poper-ops-interview-04

# 例如：查看路由
heroku run php artisan route:list -a poper-ops-interview-04
```

### 环境变量管理

```bash
# 查看所有环境变量
heroku config -a poper-ops-interview-04

# 设置环境变量
heroku config:set KEY=value -a poper-ops-interview-04

# 删除环境变量
heroku config:unset KEY -a poper-ops-interview-04

# 查看单个环境变量
heroku config:get KEY -a poper-ops-interview-04
```

## 故障排查

### 部署失败

1. 查看部署日志:
   ```bash
   heroku logs --tail -a poper-ops-interview-04
   ```

2. 检查环境变量是否正确设置:
   ```bash
   heroku config -a poper-ops-interview-04
   ```

3. 检查 PHP 版本是否兼容:
   ```bash
   heroku run php -v -a poper-ops-interview-04
   ```

### 数据库连接问题

1. 检查数据库是否已添加:
   ```bash
   heroku addons -a poper-ops-interview-04
   ```

2. 检查 DATABASE_URL 环境变量:
   ```bash
   heroku config:get DATABASE_URL -a poper-ops-interview-04
   ```

3. 测试数据库连接:
   ```bash
   heroku pg:psql -a poper-ops-interview-04 -c "SELECT version();"
   ```

### 应用无法访问

1. 检查应用是否在运行:
   ```bash
   heroku ps -a poper-ops-interview-04
   ```

2. 查看应用日志:
   ```bash
   heroku logs --tail -a poper-ops-interview-04
   ```

3. 重启应用:
   ```bash
   heroku restart -a poper-ops-interview-04
   ```

## 重要安全提示

### ⚠️ 敏感信息不要提交到 Git

**请务必确保以下文件/信息不会被提交到 Git 仓库**：

1. **.env 文件** - 包含数据库密码、APP_KEY 等敏感信息
   - `.gitignore` 已配置，`.env` 文件不会被提交
   - 如果之前误提交了 `.env` 文件，需要从 Git 历史中删除：
     ```bash
     git rm --cached .env
     git commit -m "移除 .env 文件"
     ```

2. **APP_KEY 和其他密钥** - 必须动态生成
   - 本地开发：运行 `herd php artisan key:generate` 自动生成
   - Heroku 部署：使用 `heroku config:set APP_KEY="..."` 设置
   - **不要**将 APP_KEY 硬编码到代码中

3. **证书文件** - 不要提交任何证书文件
   - `.pem`, `.key`, `.crt`, `.cert` 等文件
   - 确保 `.gitignore` 中包含这些文件类型

4. **其他敏感信息**
   - 数据库密码
   - API 密钥
   - OAuth 密钥
   - 私钥文件

### 检查是否误提交了敏感文件

```bash
# 检查 .env 文件是否在 Git 中
git ls-files | grep -E "\.env$"

# 检查是否有证书文件
git ls-files | grep -E "\.(pem|key|crt|cert)$"

# 检查是否有包含密钥的文件
git ls-files | xargs grep -l "APP_KEY\|SECRET\|PRIVATE" 2>/dev/null | grep -v ".gitignore" | grep -v "composer.json"
```

### 如果误提交了敏感文件

1. 从 Git 索引中移除（但保留本地文件）：
   ```bash
   git rm --cached .env
   git commit -m "移除敏感文件"
   ```

2. 如果已推送到远程，需要从历史中删除（使用 git-filter-repo 或 BFG Repo-Cleaner）

3. 立即更改所有已泄露的密钥和密码

## 注意事项

1. **Procfile**: 项目已包含 `Procfile`，Heroku 会自动使用它启动应用
2. **.env 文件**: 不要将 `.env` 文件提交到 Git，使用 `heroku config:set` 设置环境变量
3. **APP_KEY**: 必须在部署时动态生成，不要硬编码到代码中
4. **证书文件**: 不要提交任何证书文件到 Git 仓库
5. **数据库迁移**: 每次部署新代码后，记得运行 `heroku run php artisan migrate --force -a poper-ops-interview-04`
6. **缓存**: 生产环境建议启用配置、路由和视图缓存
7. **日志**: Heroku 会自动收集日志，使用 `heroku logs --tail -a poper-ops-interview-04` 查看实时日志
8. **文件存储**: Heroku 文件系统是只读的（除了 `tmp` 和 `log` 目录），如需文件上传，建议使用 AWS S3 等云存储服务
9. **应用名称**: 所有命令都使用 `-a poper-ops-interview-04` 参数来指定应用

## 快速参考

```bash
# 部署流程（简化版）
git remote add heroku https://git.heroku.com/poper-ops-interview-04.git  # 首次部署
heroku addons:create heroku-postgresql:mini -a poper-ops-interview-04     # 首次部署
heroku config:set APP_KEY="$(herd php artisan key:generate --show)" -a poper-ops-interview-04
heroku config:set APP_ENV=production APP_DEBUG=false APP_URL=https://poper-ops-interview-04.herokuapp.com -a poper-ops-interview-04
git push heroku master
heroku run php artisan migrate --force -a poper-ops-interview-04
heroku run php artisan admin:install -a poper-ops-interview-04
heroku run php artisan admin:create-user -a poper-ops-interview-04
heroku run php artisan db:seed --class=AdminMenuSeeder -a poper-ops-interview-04
heroku run php artisan config:cache route:cache view:cache -a poper-ops-interview-04
heroku open -a poper-ops-interview-04
```
