# 幻想三国社区系统 - 部署指南

## 📋 概述

本项目包含幻想三国官网的完整社区系统，包括玩家社区和攻略中心。

## 🗄️ Supabase 数据库配置

### 获取 API Keys

1. 登录 [Supabase Dashboard](https://supabase.com/dashboard)
2. 选择项目 `btkupvzkzwnbbhpgkksc`
3. 进入 **Settings** → **API**
4. 复制以下信息：
   - **Project URL**: `https://btkupvzkzwnbbhpgkksc.supabase.co`
   - **anon public key**: 在 `Project API keys` 下找到

### 初始化数据库

#### 方法一：通过 Supabase Dashboard（推荐）

1. 进入项目的 **SQL Editor**
2. 复制 `supabase-init.sql` 文件的全部内容
3. 粘贴到 SQL Editor 中
4. 点击 **Run** 执行

#### 方法二：通过 Supabase CLI

```bash
supabase db execute --project-id btkupvzkzwnbbhpgkksc -f supabase-init.sql
```

#### 方法三：通过 curl

```bash
# 首先需要在 Dashboard 中启用 pg_execute 扩展，或使用 Management API
```

### 数据库结构

执行 `supabase-init.sql` 后会创建以下表：

| 表名 | 说明 |
|------|------|
| `posts` | 帖子表 |
| `comments` | 评论表 |
| `user_points` | 用户积分表 |
| `likes` | 点赞记录表 |
| `view_history` | 浏览记录表 |
| `favorites` | 收藏表 |
| `announcements` | 公告表 |
| `sensitive_words` | 敏感词表 |

### 配置客户端

1. 打开 `js/supabase-config.js`
2. 将 `YOUR_ANON_PUBLIC_KEY_HERE` 替换为从 Dashboard 获取的 anon public key

```javascript
const SUPABASE_ANON_KEY = 'eyJhbGc...'; // 替换为你的 key
```

## 🌐 GitHub 仓库信息

- **仓库地址**: https://github.com/d29805622-sudo/search-page
- **目标分支**: main

### 部署说明

代码已配置好 GitHub Actions，当推送新代码时会自动构建和部署。

## 📁 文件结构

```
幻想三国官网/
├── index.html          # 首页
├── callback.html       # GitHub OAuth 回调页
├── game-bg.jpg         # 游戏背景图
├── apk/                # APK 下载目录
├── css/
│   ├── style.css       # 主样式文件
│   └── community.css   # 社区样式文件
├── js/
│   ├── config.js       # 配置文件
│   ├── github.js       # GitHub API 封装
│   ├── app.js          # 社区主逻辑
│   └── supabase-config.js  # Supabase 配置（需手动配置）
├── pages/
│   ├── community.html  # 玩家社区页
│   └── posts.html      # 攻略中心页
├── supabase-init.sql   # 数据库初始化脚本
└── README.md           # 本文档
```

## 🔐 安全说明

- **anon public key**: 可以安全地在前端代码中使用
- **service_role key**: 仅在服务端使用，**绝对不要**暴露在前端代码中

## ❓ 常见问题

### Q: 社区帖子存储在哪里？
A: 当前版本使用 GitHub Issues 作为帖子存储后端，Supabase 可用于增强功能如用户积分、点赞等。

### Q: 如何开启 Supabase 增强功能？
A: 1. 先初始化数据库（执行 supabase-init.sql）
   2. 配置 js/supabase-config.js 中的 anon key
   3. 在需要的地方引入 Supabase SDK

### Q: 部署后社区页面打不开？
A: 检查 GitHub Pages 是否正确配置，仓库 Settings → Pages → Source 应设置为 main 分支。

## 📞 支持

如有问题，请在 GitHub 仓库中提 Issue。
