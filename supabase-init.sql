-- 小魔头Galgame资源论坛 - Supabase数据库初始化脚本
-- PostgreSQL格式

DROP TABLE IF EXISTS comment CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS category CASCADE;
DROP TABLE IF EXISTS "user" CASCADE;

CREATE TABLE "user" (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(100) NOT NULL,
    avatar VARCHAR(500),
    role INTEGER DEFAULT 0 CHECK (role IN (0, 1, 2)),
    integral INTEGER DEFAULT 0,
    create_time TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    update_time TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    status INTEGER DEFAULT 1 CHECK (status IN (0, 1))
);

CREATE TABLE category (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    "desc" VARCHAR(500),
    sort INTEGER DEFAULT 0,
    status INTEGER DEFAULT 1 CHECK (status IN (0, 1)),
    create_time TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE TABLE post (
    id SERIAL PRIMARY KEY,
    cate_id INTEGER REFERENCES category(id) ON DELETE SET NULL,
    user_id INTEGER REFERENCES "user"(id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    file_path VARCHAR(500),
    file_name VARCHAR(200),
    file_size BIGINT,
    download_count INTEGER DEFAULT 0,
    is_check INTEGER DEFAULT 0 CHECK (is_check IN (0, 1, 2)),
    create_time TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    update_time TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    status INTEGER DEFAULT 1 CHECK (status IN (0, 1))
);

CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    post_id INTEGER REFERENCES post(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES "user"(id) ON DELETE SET NULL,
    content TEXT NOT NULL,
    create_time TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_post_cate_id ON post(cate_id);
CREATE INDEX idx_post_user_id ON post(user_id);
CREATE INDEX idx_post_is_check ON post(is_check);
CREATE INDEX idx_post_status ON post(status);
CREATE INDEX idx_post_create_time ON post(create_time DESC);
CREATE INDEX idx_comment_post_id ON comment(post_id);
CREATE INDEX idx_user_username ON "user"(username);

INSERT INTO "user" (username, password, nickname, role, status) VALUES ('admin', '123456', '管理员', 2, 1);

INSERT INTO category (name, "desc", sort, status) VALUES
('小魔头暴露啦专区', '动漫剧情、角色、同人讨论', 1, 1),
('Galgame交流区', 'Galgame玩法、剧情、测评', 2, 1),
('游戏包上传下载', 'Galgame游戏包、补丁、MOD上传下载', 3, 1),
('同人创作区', '同人文、同人图、剪辑作品', 4, 1);
