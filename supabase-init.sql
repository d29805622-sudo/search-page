-- 幻想三国社区系统 - Supabase 数据库初始化脚本
-- 创建日期: 2024

-- 1. 创建帖子表
CREATE TABLE IF NOT EXISTS posts (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content TEXT NOT NULL,
    author_id VARCHAR(255) NOT NULL,
    author_name VARCHAR(255) NOT NULL,
    author_avatar TEXT,
    category VARCHAR(50) NOT NULL,
    tags TEXT[] DEFAULT '{}',
    views INTEGER DEFAULT 0,
    likes INTEGER DEFAULT 0,
    comments_count INTEGER DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. 创建评论表
CREATE TABLE IF NOT EXISTS comments (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    parent_id UUID REFERENCES comments(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    author_id VARCHAR(255) NOT NULL,
    author_name VARCHAR(255) NOT NULL,
    author_avatar TEXT,
    likes INTEGER DEFAULT 0,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. 创建用户积分表
CREATE TABLE IF NOT EXISTS user_points (
    user_id VARCHAR(255) PRIMARY KEY,
    points INTEGER DEFAULT 0,
    total_posts INTEGER DEFAULT 0,
    total_comments INTEGER DEFAULT 0,
    total_likes_received INTEGER DEFAULT 0,
    level VARCHAR(50) DEFAULT '新手',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. 创建点赞记录表
CREATE TABLE IF NOT EXISTS likes (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    target_type VARCHAR(20) NOT NULL, -- 'post' 或 'comment'
    target_id UUID NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user_id, target_type, target_id)
);

-- 5. 创建浏览记录表
CREATE TABLE IF NOT EXISTS view_history (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    viewed_at TIMESTAMPTZ DEFAULT NOW()
);

-- 6. 创建收藏表
CREATE TABLE IF NOT EXISTS favorites (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user_id, post_id)
);

-- 7. 创建公告表
CREATE TABLE IF NOT EXISTS announcements (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content TEXT NOT NULL,
    priority INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 8. 创建敏感词表
CREATE TABLE IF NOT EXISTS sensitive_words (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    word VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 9. 创建索引以提高查询性能
CREATE INDEX IF NOT EXISTS idx_posts_author ON posts(author_id);
CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category);
CREATE INDEX IF NOT EXISTS idx_posts_created ON posts(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_posts_views ON posts(views DESC);
CREATE INDEX IF NOT EXISTS idx_posts_likes ON posts(likes DESC);

CREATE INDEX IF NOT EXISTS idx_comments_post ON comments(post_id);
CREATE INDEX IF NOT EXISTS idx_comments_author ON comments(author_id);

CREATE INDEX IF NOT EXISTS idx_likes_target ON likes(target_type, target_id);
CREATE INDEX IF NOT EXISTS idx_view_history_user ON view_history(user_id);

-- 10. 启用 Row Level Security (RLS)
ALTER TABLE posts ENABLE ROW LEVEL SECURITY;
ALTER TABLE comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_points ENABLE ROW LEVEL SECURITY;
ALTER TABLE likes ENABLE ROW LEVEL SECURITY;
ALTER TABLE favorites ENABLE ROW LEVEL SECURITY;
ALTER TABLE announcements ENABLE ROW LEVEL SECURITY;

-- 11. 创建 RLS 策略

-- posts 表策略
CREATE POLICY "Allow public read posts" ON posts FOR SELECT USING (is_deleted = FALSE);
CREATE POLICY "Allow authenticated insert posts" ON posts FOR INSERT WITH CHECK (auth.role() = 'authenticated');
CREATE POLICY "Allow author update posts" ON posts FOR UPDATE USING (auth.uid()::text = author_id);
CREATE POLICY "Allow author delete posts" ON posts FOR DELETE USING (auth.uid()::text = author_id);

-- comments 表策略
CREATE POLICY "Allow public read comments" ON comments FOR SELECT USING (is_deleted = FALSE);
CREATE POLICY "Allow authenticated insert comments" ON comments FOR INSERT WITH CHECK (auth.role() = 'authenticated');
CREATE POLICY "Allow author update comments" ON comments FOR UPDATE USING (auth.uid()::text = author_id);
CREATE POLICY "Allow author delete comments" ON comments FOR DELETE USING (auth.uid()::text = author_id);

-- user_points 表策略
CREATE POLICY "Allow public read points" ON user_points FOR SELECT USING (TRUE);
CREATE POLICY "Allow authenticated upsert points" ON user_points FOR INSERT WITH CHECK (auth.role() = 'authenticated');
CREATE POLICY "Allow owner update points" ON user_points FOR UPDATE USING (auth.uid()::text = user_id);

-- likes 表策略
CREATE POLICY "Allow public read likes" ON likes FOR SELECT USING (TRUE);
CREATE POLICY "Allow authenticated manage likes" ON likes FOR INSERT WITH CHECK (auth.role() = 'authenticated');
CREATE POLICY "Allow owner delete likes" ON likes FOR DELETE USING (auth.uid()::text = user_id);

-- favorites 表策略
CREATE POLICY "Allow public read favorites" ON favorites FOR SELECT USING (TRUE);
CREATE POLICY "Allow authenticated manage favorites" ON favorites FOR INSERT WITH CHECK (auth.role() = 'authenticated');
CREATE POLICY "Allow owner delete favorites" ON favorites FOR DELETE USING (auth.uid()::text = user_id);

-- announcements 表策略
CREATE POLICY "Allow public read announcements" ON announcements FOR SELECT USING (is_active = TRUE);

-- 12. 插入示例数据
INSERT INTO announcements (title, content, priority) VALUES
('欢迎来到幻想三国社区！', '欢迎所有玩家加入我们的社区，分享游戏心得，讨论攻略技巧。', 100),
('新版本来袭', '敬请期待最新游戏版本更新内容！', 50);

-- 13. 插入默认敏感词
INSERT INTO sensitive_words (word) VALUES
('作弊'), ('外挂'), ('骗子'), ('广告');

-- 14. 创建更新 updated_at 的触发器函数
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- 为相关表创建触发器
DROP TRIGGER IF EXISTS update_posts_updated_at ON posts;
CREATE TRIGGER update_posts_updated_at BEFORE UPDATE ON posts FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_comments_updated_at ON comments;
CREATE TRIGGER update_comments_updated_at BEFORE UPDATE ON comments FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_user_points_updated_at ON user_points;
CREATE TRIGGER update_user_points_updated_at BEFORE UPDATE ON user_points FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_announcements_updated_at ON announcements;
CREATE TRIGGER update_announcements_updated_at BEFORE UPDATE ON announcements FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- 15. 创建统计视图
CREATE OR REPLACE VIEW community_stats AS
SELECT 
    (SELECT COUNT(*) FROM posts WHERE is_deleted = FALSE) as total_posts,
    (SELECT COUNT(*) FROM comments WHERE is_deleted = FALSE) as total_comments,
    (SELECT COUNT(*) FROM user_points) as total_users,
    (SELECT COUNT(*) FROM posts WHERE created_at > NOW() - INTERVAL '24 hours') as posts_24h,
    (SELECT COUNT(*) FROM comments WHERE created_at > NOW() - INTERVAL '24 hours') as comments_24h;
