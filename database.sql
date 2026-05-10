-- 小魔头Galgame论坛数据库
-- 请在MySQL中执行此脚本

-- 创建数据库
CREATE DATABASE IF NOT EXISTS `xiaomotou_gal_forum` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `xiaomotou_gal_forum`;

-- 用户表
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码（加密）',
  `nickname` varchar(50) DEFAULT '小魔头粉丝' COMMENT '昵称',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `role` tinyint(1) DEFAULT 0 COMMENT '角色 0普通用户 1版主 2管理员',
  `integral` int(11) DEFAULT 0 COMMENT '积分',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT 1 COMMENT '状态 1正常 0禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 板块表
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '板块名称',
  `desc` varchar(255) DEFAULT '' COMMENT '板块描述',
  `sort` int(11) DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='板块表';

-- 帖子表
DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL COMMENT '板块ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `title` varchar(255) NOT NULL COMMENT '帖子标题',
  `content` longtext NOT NULL COMMENT '帖子内容',
  `file_path` varchar(255) DEFAULT '' COMMENT '文件路径',
  `file_name` varchar(255) DEFAULT '' COMMENT '文件名',
  `file_size` varchar(50) DEFAULT '' COMMENT '文件大小',
  `download_count` int(11) DEFAULT 0 COMMENT '下载次数',
  `is_check` tinyint(1) DEFAULT 0 COMMENT '审核状态 0待审核 1通过 2拒绝',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `cate_id` (`cate_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='帖子表';

-- 评论表
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- 初始化板块
INSERT INTO `category` (`name`, `desc`) VALUES 
('小魔头暴露啦专区', '动漫剧情、角色、同人讨论'),
('Galgame交流区', 'Galgame玩法、剧情、测评'),
('游戏包上传下载', 'Galgame游戏包、补丁、MOD上传下载'),
('同人创作区', '同人文、同人图、剪辑作品');

-- 管理员账号: admin / 123456
INSERT INTO `user` (`username`, `password`, `nickname`, `role`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '论坛管理员', 2);