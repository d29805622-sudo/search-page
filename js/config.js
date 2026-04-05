// 极客社区 - 配置文件

const CONFIG = {
    // GitHub 配置（请替换成你的）
    GITHUB: {
        OWNER: 'd29805622-sudo',
        REPO: 'community-discussions',
        TOKEN: '' // 可选，公开访问不需要
    },
    
    // 社区配置
    COMMUNITY: {
        NAME: '极客社区',
        DESCRIPTION: '开发者的技术交流平台',
        CATEGORIES: [
            { id: 'tech', name: '技术讨论', icon: '💻' },
            { id: 'question', name: '问答求助', icon: '❓' },
            { id: 'share', name: '资源分享', icon: '📦' },
            { id: 'project', name: '项目展示', icon: '🚀' },
            { id: 'career', name: '职业发展', icon: '💼' }
        ],
        TAGS: [
            'JavaScript', 'Python', 'Java', 'React', 'Vue', 
            'Node.js', '前端', '后端', 'AI', '数据库',
            'Linux', 'Docker', 'Git', '面试', '创业'
        ]
    },
    
    // 分页配置
    PAGINATION: {
        POSTS_PER_PAGE: 20
    },
    
    // 积分配置
    POINTS: {
        POST: 10,      // 发帖
        COMMENT: 5,    // 评论
        LIKE: 2,       // 被点赞
        SOLVED: 50     // 问题被采纳
    }
};
