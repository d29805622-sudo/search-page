# 数据库设置指南

## ⚠️ 重要：需要手动初始化数据库

由于 Supabase API 限制，无法自动执行 SQL 脚本，需要手动在 Supabase Dashboard 中操作。

## 初始化步骤

### 1. 登录 Supabase Dashboard
访问：https://supabase.com/dashboard

### 2. 进入 SQL Editor
1. 选择你的项目
2. 点击左侧菜单 **SQL Editor**

### 3. 执行初始化脚本
1. 点击 **New Query** 按钮
2. 复制 `supabase-init.sql` 文件中的所有内容
3. 粘贴到编辑器中
4. 点击 **Run** 按钮执行

### 4. 验证是否成功
执行以下查询确认表已创建：

```sql
SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';
```

应该看到以下表：
- user
- category
- post
- comment

## 初始管理员账号

- **用户名**：admin
- **密码**：123456
- **角色**：管理员（role = 2）

## 已预置的板块

| ID | 名称 | 描述 |
|----|------|------|
| 1 | 小魔头暴露啦专区 | 动漫剧情、角色、同人讨论 |
| 2 | Galgame交流区 | Galgame玩法、剧情、测评 |
| 3 | 游戏包上传下载 | Galgame游戏包、补丁、MOD上传下载 |
| 4 | 同人创作区 | 同人文、同人图、剪辑作品 |
