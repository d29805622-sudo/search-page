# 小魔头Galgame论坛 - 部署指南

## 项目结构

```
xiaomotou-forum/
├── config.php          # 数据库配置文件
├── index.php           # 首页
├── login.php           # 登录页
├── register.php        # 注册页
├── logout.php          # 退出登录
├── category.php        # 板块帖子列表
├── post.php            # 帖子详情页
├── new_post.php        # 发布帖子
├── download.php        # 文件下载处理
├── database.sql        # 数据库脚本
├── admin/
│   └── index.php       # 管理后台
└── uploads/            # 上传文件目录（自动创建）
```

## 部署步骤

### 方法一：本地开发测试

1. **安装环境**
   - 下载并安装 XAMPP/WAMP/MAMP（包含 PHP + MySQL）
   - 启动 Apache 和 MySQL 服务

2. **导入数据库**
   - 打开 phpMyAdmin（通常 http://localhost/phpmyadmin）
   - 创建新数据库，导入 `database.sql` 文件

3. **配置数据库**
   - 编辑 `config.php`，修改数据库连接信息：
   ```php
   $config = [
       'host' => 'localhost',
       'dbname' => 'xiaomotou_gal_forum',
       'username' => 'root',      // 你的数据库用户名
       'password' => '',          // 你的数据库密码
       'charset' => 'utf8mb4'
   ];
   ```

4. **访问网站**
   - 将项目文件放入网站根目录
   - 访问 http://localhost/xiaomotou-forum/

### 方法二：免费PHP主机部署

推荐使用以下免费PHP+MySQL主机：

1. **000webhost** (https://www.000webhost.com/)
   - 注册账号
   - 创建新网站
   - 上传所有文件
   - 导入数据库

2. **InfinityFree** (https://www.infinityfree.com/)
   - 免费提供PHP + MySQL
   - 支持SSH文件管理

### 方法三：云服务器部署

1. **购买云服务器**（如阿里云、腾讯云）
2. **安装宝塔面板**进行可视化管理
3. **创建网站**，上传文件
4. **创建数据库**，导入SQL
5. **配置域名解析**

## 管理员登录

- **用户名**: admin
- **密码**: 123456

⚠️ 首次登录后请立即修改密码！

## 功能说明

### 用户功能
- ✅ 用户注册/登录
- ✅ 浏览板块和帖子
- ✅ 发布帖子（带附件）
- ✅ 评论互动
- ✅ 下载附件

### 管理功能
- ✅ 数据统计面板
- ✅ 帖子审核管理
- ✅ 用户权限管理
- ✅ 删除帖子/用户

## 注意事项

1. **文件上传目录**：确保 `uploads/` 目录有写入权限
2. **安全建议**：修改管理员默认密码
3. **定期备份**：定期备份数据库和上传文件
4. **PHP版本**：建议使用 PHP 7.4 或更高版本

## 技术支持

如有问题，请检查：
1. PHP版本是否 >= 7.4
2. MySQL版本是否 >= 5.7
3. 是否正确导入数据库
4. config.php 数据库配置是否正确