# 🧛‍♂️ 小魔头Galgame资源论坛 🧩

一个基于 Supabase 的 Galgame 资源论坛，支持游戏包上传下载、帖子发布、评论互动等功能。

## 功能特性

- 📋 **板块分类**：小魔头暴露啦专区、Galgame交流区、游戏包上传下载、同人创作区
- 👤 **用户系统**：注册/登录、积分系统、角色权限（普通用户/版主/管理员）
- 📝 **帖子系统**：发布帖子、评论互动、游戏包下载
- 📦 **文件管理**：游戏包上传（需审核）、直接下载（无需登录）
- 🔧 **管理后台**：帖子审核、用户管理

## 技术栈

- **前端**：原生 HTML/CSS/JavaScript
- **后端**：Supabase (PostgreSQL + Realtime)
- **样式**：自定义暗色二次元风格 CSS

## 数据库初始化

⚠️ **重要**：请参考 `DATABASE_SETUP.md` 手动在 Supabase Dashboard 中执行 SQL 脚本。

## 初始账号

- 用户名：`admin`
- 密码：`123456`
- 角色：管理员

## 部署

本项目为纯静态网页，可直接部署到 GitHub Pages、Vercel、Netlify 等平台。

## 目录结构

```
├── index.html          # 主页面
├── css/
│   └── style.css       # 样式文件
├── js/
│   └── app.js          # 应用逻辑
├── supabase-init.sql   # 数据库初始化脚本
├── DATABASE_SETUP.md   # 数据库设置指南
└── README.md           # 项目说明
```

## 规则说明

- ⬆️ 上传游戏包需管理员审核
- ⬇️ 下载游戏包无需登录
- 👑 管理员初始账号：admin / 123456

---

Made with 💜 for Galgame lovers
