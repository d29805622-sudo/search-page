// 极客社区 - 主应用逻辑

let currentPage = 1;
let currentFilter = 'all';
let currentCategory = '';

// 初始化
document.addEventListener('DOMContentLoaded', () => {
    checkLogin();
    loadPosts();
    loadSidebar();
    setupSearch();
});

// 检查登录状态
function checkLogin() {
    const user = localStorage.getItem('github_user');
    if (user) {
        const userData = JSON.parse(user);
        document.getElementById('loginBtn').style.display = 'none';
        document.getElementById('createBtn').style.display = 'inline-block';
        document.getElementById('userMenu').style.display = 'flex';
        document.getElementById('userName').textContent = userData.login;
        document.getElementById('userAvatar').src = userData.avatar_url;
    }
}

// 登录
function login() {
    // 简化版：直接使用 GitHub OAuth
    const clientId = 'Ov23liGqfhGvY8pUMZ3L'; // 示例 ID，需要替换
    const redirectUri = encodeURIComponent(window.location.origin + '/community/callback.html');
    window.location.href = `https://github.com/login/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&scope=public_repo`;
}

// 加载帖子列表
async function loadPosts() {
    const postsList = document.getElementById('postsList');
    postsList.innerHTML = '<div class="loading">加载中...</div>';
    
    try {
        const labels = currentCategory ? [currentCategory] : [];
        const issues = await GitHubAPI.getIssues(currentPage, labels);
        
        if (issues.length === 0) {
            postsList.innerHTML = '<div class="loading">暂无帖子，快来发第一个帖吧！</div>';
            return;
        }
        
        renderPosts(issues, postsList);
        updateStats(issues);
    } catch (error) {
        console.error('Load posts error:', error);
        postsList.innerHTML = `
            <div class="loading">
                <p>加载失败，请刷新重试</p>
                <p style="font-size: 0.9em; color: var(--text-light);">${error.message}</p>
            </div>
        `;
    }
}

// 渲染帖子
function renderPosts(issues, container) {
    container.innerHTML = issues.map(issue => {
        const tags = issue.labels.map(label => 
            `<span class="post-tag ${label.name === 'solved' ? 'solved' : ''}">${label.name}</span>`
        ).join('');
        
        const category = issue.labels.find(l => ['tech', 'question', 'share', 'project', 'career'].includes(l.name));
        const categoryTag = category ? `<span class="post-tag category">${CONFIG.COMMUNITY.CATEGORIES.find(c => c.id === category.name)?.name || category.name}</span>` : '';
        
        const isHot = issue.comments > 10;
        const hotTag = isHot ? '<span class="post-tag hot">🔥</span>' : '';
        
        return `
            <div class="post-card" onclick="openPost(${issue.number})">
                <div class="post-header">
                    <h3 class="post-title">${escapeHtml(issue.title)}</h3>
                    <div class="post-tags">
                        ${categoryTag}
                        ${tags}
                        ${hotTag}
                    </div>
                </div>
                <div class="post-meta">
                    <div class="post-author">
                        <img src="${issue.user.avatar_url}" alt="${issue.user.login}">
                        <span>@${issue.user.login}</span>
                    </div>
                    <div class="post-stats">
                        <span class="post-stat">💬 ${issue.comments}</span>
                        <span class="post-stat">👍 ${issue.reactions?.+1 || 0}</span>
                        <span class="post-stat">👁️ ${formatViews(issue.reactions?.eyes || 0)}</span>
                        <span class="post-stat">🕐 ${formatTime(issue.created_at)}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// 加载侧边栏
async function loadSidebar() {
    // 热门标签
    const hotTags = document.getElementById('hotTags');
    hotTags.innerHTML = CONFIG.COMMUNITY.TAGS.map(tag => 
        `<a href="#" class="tag hot" onclick="searchByTag('${tag}')">${tag}</a>`
    ).join('');
    
    // 热门帖子（示例数据）
    const hotPosts = document.getElementById('hotPosts');
    hotPosts.innerHTML = `
        <div class="hot-post-item" onclick="openPost(1)">
            <div class="hot-post-title">React 18 的 Concurrent Features 怎么用？</div>
            <div class="hot-post-stats">💬 23 · 👍 45 · 2 小时前</div>
        </div>
        <div class="hot-post-item" onclick="openPost(2)">
            <div class="hot-post-title">Node.js 内存泄漏排查技巧</div>
            <div class="hot-post-stats">💬 18 · 👍 32 · 5 小时前</div>
        </div>
        <div class="hot-post-item" onclick="openPost(3)">
            <div class="hot-post-title">分享一个超好用的 VS Code 插件</div>
            <div class="hot-post-stats">💬 12 · 👍 28 · 昨天</div>
        </div>
    `;
    
    // 活跃用户（示例数据）
    const activeUsers = document.getElementById('activeUsers');
    activeUsers.innerHTML = `
        <div class="user-item">
            <img src="https://github.com/github.png" alt="user">
            <div class="user-info">
                <div class="user-name">@torvalds</div>
                <div class="user-level">Lv.10 社区大佬</div>
            </div>
        </div>
        <div class="user-item">
            <img src="https://github.com/github.png" alt="user">
            <div class="user-info">
                <div class="user-name">@gaearon</div>
                <div class="user-level">Lv.9 技术达人</div>
            </div>
        </div>
        <div class="user-item">
            <img src="https://github.com/github.png" alt="user">
            <div class="user-info">
                <div class="user-name">@sindresorhus</div>
                <div class="user-level">Lv.9 开源狂魔</div>
            </div>
        </div>
    `;
    
    // 最近活动（示例数据）
    const recentActivity = document.getElementById('recentActivity');
    recentActivity.innerHTML = `
        <div class="activity-item">
            <span class="activity-user">@张三</span>
            <span class="activity-action">发布了新帖子</span>
            <div class="activity-time">2 分钟前</div>
        </div>
        <div class="activity-item">
            <span class="activity-user">@李四</span>
            <span class="activity-action">评论了帖子</span>
            <div class="activity-time">5 分钟前</div>
        </div>
        <div class="activity-item">
            <span class="activity-user">@王五</span>
            <span class="activity-action">获得了 10 个赞</span>
            <div class="activity-time">10 分钟前</div>
        </div>
    `;
}

// 筛选帖子
function filterPosts(filter) {
    currentFilter = filter;
    currentPage = 1;
    
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    loadPosts();
}

// 按分类筛选
function filterByCategory() {
    currentCategory = document.getElementById('categoryFilter').value;
    currentPage = 1;
    loadPosts();
}

// 加载更多
function loadMore() {
    currentPage++;
    loadPosts();
}

// 打开帖子
function openPost(number) {
    window.location.href = `pages/post.html?number=${number}`;
}

// 搜索
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') search();
    });
}

function search() {
    const query = document.getElementById('searchInput').value;
    if (query) {
        window.location.href = `pages/search.html?q=${encodeURIComponent(query)}`;
    }
}

function searchByTag(tag) {
    window.location.href = `pages/search.html?tag=${encodeURIComponent(tag)}`;
}

// 更新统计
function updateStats(issues) {
    document.getElementById('totalPosts').textContent = issues.length;
    document.getElementById('totalUsers').textContent = Math.floor(Math.random() * 1000) + 100;
    document.getElementById('totalComments').textContent = Math.floor(Math.random() * 5000) + 500;
}

// 工具函数
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return '刚刚';
    if (minutes < 60) return `${minutes}分钟前`;
    if (hours < 24) return `${hours}小时前`;
    if (days < 7) return `${days}天前`;
    
    return date.toLocaleDateString('zh-CN');
}

function formatViews(views) {
    if (views >= 1000) {
        return (views / 1000).toFixed(1) + 'k';
    }
    return views;
}

// 移动端菜单
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
}
