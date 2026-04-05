// GitHub API 调用模块

const GitHubAPI = {
    // 获取 Issues（帖子）
    async getIssues(page = 1, labels = []) {
        const url = new URL(`https://api.github.com/repos/${CONFIG.GITHUB.OWNER}/${CONFIG.GITHUB.REPO}/issues`);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', CONFIG.PAGINATION.POSTS_PER_PAGE);
        url.searchParams.set('state', 'open');
        if (labels.length > 0) {
            url.searchParams.set('labels', labels.join(','));
        }
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch issues');
        return await response.json();
    },
    
    // 获取单个 Issue（帖子详情）
    async getIssue(number) {
        const response = await fetch(`https://api.github.com/repos/${CONFIG.GITHUB.OWNER}/${CONFIG.GITHUB.REPO}/issues/${number}`);
        if (!response.ok) throw new Error('Failed to fetch issue');
        return await response.json();
    },
    
    // 获取评论
    async getComments(issueNumber, page = 1) {
        const response = await fetch(`https://api.github.com/repos/${CONFIG.GITHUB.OWNER}/${CONFIG.GITHUB.REPO}/issues/${issueNumber}/comments?page=${page}&per_page=50`);
        if (!response.ok) throw new Error('Failed to fetch comments');
        return await response.json();
    },
    
    // 创建 Issue（发帖）
    async createIssue(title, body, labels = []) {
        const response = await fetch(`https://api.github.com/repos/${CONFIG.GITHUB.OWNER}/${CONFIG.GITHUB.REPO}/issues`, {
            method: 'POST',
            headers: {
                'Authorization': `token ${CONFIG.GITHUB.TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title,
                body,
                labels
            })
        });
        if (!response.ok) throw new Error('Failed to create issue');
        return await response.json();
    },
    
    // 创建评论
    async createComment(issueNumber, body) {
        const response = await fetch(`https://api.github.com/repos/${CONFIG.GITHUB.OWNER}/${CONFIG.GITHUB.REPO}/issues/${issueNumber}/comments`, {
            method: 'POST',
            headers: {
                'Authorization': `token ${CONFIG.GITHUB.TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ body })
        });
        if (!response.ok) throw new Error('Failed to create comment');
        return await response.json();
    },
    
    // 获取用户信息
    async getUser(username) {
        const response = await fetch(`https://api.github.com/users/${username}`);
        if (!response.ok) throw new Error('Failed to fetch user');
        return await response.json();
    },
    
    // GitHub OAuth 登录
    login() {
        const clientId = 'YOUR_GITHUB_CLIENT_ID'; // 需要配置
        const redirectUri = window.location.origin + '/community/callback.html';
        const scope = 'public_repo,user';
        window.location.href = `https://github.com/login/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&scope=${scope}`;
    },
    
    // 处理回调
    async handleCallback(code) {
        // 这里需要后端配合交换 token
        // 简化版：直接存储 code
        localStorage.setItem('github_code', code);
        return code;
    }
};
