# 安全指南

## 敏感信息管理

本项目遵循安全最佳实践，确保敏感信息不会被提交到 Git 仓库。

## 不应提交的文件

以下文件/信息**永远不要**提交到 Git 仓库：

### 1. 环境配置文件

- `.env` - 包含数据库密码、APP_KEY 等敏感信息
- `.env.local`
- `.env.production`
- `.env.backup`
- 任何 `.env.*` 文件

**状态**: ✅ 已在 `.gitignore` 中配置

### 2. 密钥和证书文件

- `*.pem` - PEM 格式证书
- `*.key` - 私钥文件
- `*.crt` - 证书文件
- `*.cert` - 证书文件
- `*.pfx` - PKCS#12 证书
- `*.p12` - PKCS#12 证书

**建议**: 如果项目需要证书文件，请使用环境变量或密钥管理服务（如 AWS Secrets Manager、HashiCorp Vault）

### 3. 代码中的敏感信息

不要在代码中硬编码以下信息：

- `APP_KEY` - Laravel 应用密钥
- 数据库密码
- API 密钥
- OAuth 客户端密钥
- 加密密钥
- JWT 密钥

## 正确的做法

### APP_KEY 管理

#### 本地开发

```bash
# 首次运行或克隆项目后，生成 APP_KEY
herd php artisan key:generate
```

这会自动在 `.env` 文件中生成 `APP_KEY`（`.env` 文件不会被提交）

#### Heroku 部署

```bash
# 在 Heroku 上动态生成并设置 APP_KEY
APP_KEY=$(php artisan key:generate --show)
heroku config:set APP_KEY="$APP_KEY" -a poper-ops-interview-04
```

### 环境变量管理

#### 本地开发

使用 `.env` 文件（不会被提交）：

```env
APP_KEY=base64:xxxxxxxxxxxxx
DB_PASSWORD=your_password
```

#### 生产环境（Heroku）

使用 Heroku 环境变量：

```bash
# 设置环境变量
heroku config:set KEY=value -a poper-ops-interview-04

# 查看环境变量
heroku config -a poper-ops-interview-04

# 删除环境变量
heroku config:unset KEY -a poper-ops-interview-04
```

### 证书管理

如果需要证书文件：

1. **使用环境变量**（推荐）
   - 将证书内容存储在 Heroku 环境变量中
   - 在应用启动时写入临时文件

2. **使用密钥管理服务**
   - AWS Secrets Manager
   - HashiCorp Vault
   - Azure Key Vault

3. **使用 Heroku Config Vars**
   - 将证书内容存储为环境变量
   - 在运行时读取并写入临时文件

## 检查清单

在提交代码前，请检查：

- [ ] `.env` 文件不在 Git 中
- [ ] 没有证书文件（`.pem`, `.key`, `.crt` 等）
- [ ] 代码中没有硬编码的密钥
- [ ] 代码中没有硬编码的密码
- [ ] 没有包含敏感信息的配置文件

## 检查命令

```bash
# 检查 .env 文件是否在 Git 中
git ls-files | grep -E "\.env$"

# 检查是否有证书文件
git ls-files | grep -E "\.(pem|key|crt|cert|pfx|p12)$"

# 检查代码中是否有硬编码的 APP_KEY
git diff HEAD | grep -i "APP_KEY" | grep -v ".env.example"

# 查看将被提交的文件
git status
```

## 如果误提交了敏感文件

### 1. 从 Git 索引中移除（保留本地文件）

```bash
git rm --cached .env
git commit -m "移除敏感文件"
git push
```

### 2. 从 Git 历史中完全删除（如果已推送）

**注意**: 这会重写 Git 历史，请谨慎操作！

```bash
# 使用 git-filter-repo（推荐）
git filter-repo --path .env --invert-paths

# 或使用 BFG Repo-Cleaner
bfg --delete-files .env
git reflog expire --expire=now --all
git gc --prune=now --aggressive
```

### 3. 立即更改已泄露的密钥

- 生成新的 `APP_KEY`
- 更改数据库密码
- 重置所有 API 密钥
- 重新生成所有证书

## .gitignore 配置

项目已正确配置 `.gitignore`，包含以下规则：

```
# 环境文件
.env
.env.backup
.env.production
.env.*.local

# 敏感文件
*.pem
*.key
*.crt
*.cert
*.pfx
*.p12

# Laravel 存储（可能包含敏感信息）
/storage/*.key
```

## 参考资源

- [Laravel 安全性文档](https://laravel.com/docs/security)
- [Heroku 配置变量文档](https://devcenter.heroku.com/articles/config-vars)
- [Git 敏感数据移除指南](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository)
