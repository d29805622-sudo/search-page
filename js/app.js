// 小魔头Galgame论坛 - Supabase应用

const SUPABASE_URL = 'https://btkupvzkzwnbbhpgkksc.supabase.co';
const SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJ0a3VwdnpreXduYmJocGdrazNjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MTIzMjAyMTYsImV4cCI6MjAyNzg5NjIxNn0.r3G3LQ-GOWqLpRvC9e7lRhH3a3P6t8XqOi3lGdQ8W8U';

let supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

let currentUser = null;
let currentPost = null;
let currentCategory = null;

document.addEventListener('DOMContentLoaded', async () => {
    await initApp();
    setupEventListeners();
});

async function initApp() {
    const savedUser = localStorage.getItem('user');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        updateUserUI();
    }
    await loadCategories();
    await loadHomePosts();
}

function setupEventListeners() {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', handleNavClick);
    });
    document.querySelectorAll('.auth-tab').forEach(tab => {
        tab.addEventListener('click', handleAuthTabClick);
    });
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.addEventListener('click', handleAdminTabClick);
    });
}

function handleNavClick(e) {
    e.preventDefault();
    const page = e.target.dataset.page;
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    e.target.classList.add('active');
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));

    switch(page) {
        case 'home':
            document.getElementById('page-home').classList.add('active');
            loadHomePosts();
            break;
        case 'xiaomogui':
            navigateToCategory(1);
            break;
        case 'ggame':
            navigateToCategory(2);
            break;
        case 'upload':
            if (!currentUser) {
                showToast('请先登录', 'warning');
                navigateTo('login');
            } else {
                document.getElementById('page-editor').classList.add('active');
                loadCategoriesToEditor();
            }
            break;
        case 'login':
            navigateTo('login');
            break;
    }
}

function navigateTo(page) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(`page-${page}`).classList.add('active');
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.toggle('active', item.dataset.page === page);
    });
}

async function navigateToCategory(cateId) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-category').classList.add('active');
    currentCategory = cateId;
    await loadCategoryInfo(cateId);
    await loadCategoryPosts(cateId);
}

async function loadCategories() {
    const container = document.getElementById('categories');
    try {
        const { data, error } = await supabase
            .from('category')
            .select('*')
            .eq('status', 1)
            .order('sort', { ascending: true });
        
        if (error) throw error;
        
        const icons = ['👻', '🎮', '📦', '🎨'];
        container.innerHTML = data.map((cat, i) => `
            <div class="category-card" onclick="navigateToCategory(${cat.id})">
                <div class="category-icon">${icons[i] || '📁'}</div>
                <div class="category-name">${cat.name}</div>
                <div class="category-desc">${cat.desc || ''}</div>
                <div class="category-stats"><span>查看详情 →</span></div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = `
            <div class="category-card" onclick="navigateToCategory(1)"><div class="category-icon">👻</div><div class="category-name">小魔头暴露啦专区</div><div class="category-desc">动漫剧情、角色、同人讨论</div><div class="category-stats"><span>查看详情 →</span></div></div>
            <div class="category-card" onclick="navigateToCategory(2)"><div class="category-icon">🎮</div><div class="category-name">Galgame交流区</div><div class="category-desc">Galgame玩法、剧情、测评</div><div class="category-stats"><span>查看详情 →</span></div></div>
            <div class="category-card" onclick="navigateToCategory(3)"><div class="category-icon">📦</div><div class="category-name">游戏包上传下载</div><div class="category-desc">Galgame游戏包、补丁、MOD上传下载</div><div class="category-stats"><span>查看详情 →</span></div></div>
            <div class="category-card" onclick="navigateToCategory(4)"><div class="category-icon">🎨</div><div class="category-name">同人创作区</div><div class="category-desc">同人文、同人图、剪辑作品</div><div class="category-stats"><span>查看详情 →</span></div></div>
        `;
    }
}

async function loadCategoryInfo(cateId) {
    try {
        const { data } = await supabase.from('category').select('*').eq('id', cateId).single();
        if (data) document.getElementById('categoryTitle').textContent = data.name;
    } catch (error) {}
    document.getElementById('newPostBtn').style.display = currentUser ? 'block' : 'none';
}

async function loadCategoryPosts(cateId) {
    const container = document.getElementById('categoryPosts');
    container.innerHTML = '<div class="loading">加载中</div>';
    
    try {
        const { data, error } = await supabase
            .from('post')
            .select('*, category (name), user (nickname)')
            .eq('cate_id', cateId)
            .eq('status', 1)
            .eq('is_check', 1)
            .order('create_time', { ascending: false });
        
        if (error) throw error;
        
        if (!data || data.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📭</div><div class="empty-state-text">暂无帖子，快来发布第一篇吧！</div></div>';
            return;
        }
        
        container.innerHTML = data.map(post => `
            <div class="post-list-item" onclick="viewPost(${post.id})">
                <div class="post-list-content">
                    <div class="post-title">${post.title}</div>
                    <div class="post-meta"><span>👤 ${post.user?.nickname || '匿名'}</span><span>📅 ${formatDate(post.create_time)}</span><span>⬇️ ${post.download_count || 0}</span></div>
                </div>
                ${post.file_path ? '<span class="post-status status-approved">📦 有资源</span>' : ''}
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<div class="empty-state"><div class="empty-state-text">加载失败，请稍后重试</div></div>';
    }
}

async function loadHomePosts() {
    const container = document.getElementById('homePosts');
    container.innerHTML = '<div class="loading">加载中</div>';
    
    try {
        const { data, error } = await supabase
            .from('post')
            .select('*, category (name), user (nickname)')
            .eq('status', 1)
            .eq('is_check', 1)
            .order('create_time', { ascending: false })
            .limit(12);
        
        if (error) throw error;
        
        if (!data || data.length === 0) {
            container.innerHTML = '<div class="empty-state" style="grid-column: 1/-1;"><div class="empty-state-icon">🎮</div><div class="empty-state-text">暂无游戏包，精彩即将上线！</div></div>';
            return;
        }
        
        container.innerHTML = data.map(post => `
            <div class="post-card" onclick="viewPost(${post.id})">
                <div class="post-card-header">
                    <span class="post-cate-badge">${post.category?.name || '未分类'}</span>
                    <div class="post-title">${post.title}</div>
                    <div class="post-meta"><span>👤 ${post.user?.nickname || '匿名'}</span><span>📅 ${formatDate(post.create_time)}</span></div>
                </div>
                <div class="post-card-body">
                    <div class="post-excerpt">${post.content || '暂无描述'}</div>
                    <div class="post-footer">
                        <div class="post-stats"><span>💬 0</span><span>⬇️ ${post.download_count || 0}</span></div>
                        ${post.file_path ? '<span class="post-status status-approved">📦 可下载</span>' : ''}
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<div class="empty-state" style="grid-column: 1/-1;"><div class="empty-state-icon">⚠️</div><div class="empty-state-text">数据库未初始化，请先运行SQL脚本</div></div>';
    }
}

async function viewPost(postId) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-post').classList.add('active');
    
    const container = document.getElementById('postDetail');
    container.innerHTML = '<div class="loading">加载中</div>';
    
    try {
        const { data, error } = await supabase
            .from('post')
            .select('*, category (name), user (nickname)')
            .eq('id', postId)
            .single();
        
        if (error) throw error;
        
        currentPost = data;
        
        if (data.file_path) {
            await supabase.from('post').update({ download_count: (data.download_count || 0) + 1 }).eq('id', postId);
        }
        
        container.innerHTML = `
            <div class="post-detail-header">
                <span class="post-cate-badge">${data.category?.name || '未分类'}</span>
                <h1 class="post-detail-title">${data.title}</h1>
                <div class="post-detail-meta"><span>👤 ${data.user?.nickname || '匿名'}</span><span>📅 ${formatDate(data.create_time)}</span><span>⬇️ ${(data.download_count || 0) + (data.file_path ? 1 : 0)}</span></div>
            </div>
            <div class="post-detail-content">${data.content || '暂无内容'}</div>
            ${data.file_path ? `
                <div class="post-download-section">
                    <h4>📦 游戏包下载</h4>
                    <p style="color: var(--text-secondary); margin: 10px 0;">文件名：${data.file_name || '游戏包'} ${data.file_size ? '| 大小：' + formatFileSize(data.file_size) : ''}</p>
                    <a href="${data.file_path}" class="download-btn" download="${data.file_name}">⬇️ 点击下载游戏包</a>
                </div>
            ` : ''}
        `;
        
        document.getElementById('commentForm').style.display = currentUser ? 'block' : 'none';
        await loadComments(postId);
    } catch (error) {
        container.innerHTML = '<div class="empty-state"><div class="empty-state-text">加载失败</div></div>';
    }
}

async function loadComments(postId) {
    const container = document.getElementById('commentsList');
    try {
        const { data, error } = await supabase
            .from('comment')
            .select('*, user (nickname)')
            .eq('post_id', postId)
            .order('create_time', { ascending: true });
        
        if (error) throw error;
        
        if (!data || data.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-state-text">暂无评论</div></div>';
            return;
        }
        
        container.innerHTML = data.map(comment => `
            <div class="comment-item">
                <div class="comment-header"><span class="comment-user">👤 ${comment.user?.nickname || '匿名'}</span><span class="comment-time">${formatDate(comment.create_time)}</span></div>
                <div class="comment-content">${comment.content}</div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<div class="empty-state"><div class="empty-state-text">加载评论失败</div></div>';
    }
}

async function submitComment() {
    if (!currentUser) { showToast('请先登录', 'warning'); return; }
    const content = document.getElementById('commentContent').value.trim();
    if (!content) { showToast('请输入评论内容', 'warning'); return; }
    
    try {
        const { error } = await supabase.from('comment').insert({
            post_id: currentPost.id,
            user_id: currentUser.id,
            content: content
        });
        if (error) throw error;
        document.getElementById('commentContent').value = '';
        showToast('评论成功', 'success');
        await loadComments(currentPost.id);
    } catch (error) {
        showToast('评论失败，请稍后重试', 'error');
    }
}

function handleAuthTabClick(e) {
    const tab = e.target.dataset.tab;
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    e.target.classList.add('active');
    document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
    document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
}

async function login() {
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    if (!username || !password) { showToast('请输入用户名和密码', 'warning'); return; }
    
    try {
        const { data, error } = await supabase.from('user').select('*').eq('username', username).single();
        if (error) throw error;
        if (data.password !== password) { showToast('密码错误', 'error'); return; }
        currentUser = data;
        localStorage.setItem('user', JSON.stringify(data));
        updateUserUI();
        showToast('登录成功', 'success');
        navigateTo('home');
    } catch (error) {
        showToast('用户名或密码错误', 'error');
    }
}

async function register() {
    const username = document.getElementById('regUsername').value.trim();
    const password = document.getElementById('regPassword').value;
    const nickname = document.getElementById('regNickname').value.trim();
    if (!username || !password || !nickname) { showToast('请填写所有字段', 'warning'); return; }
    
    try {
        const { data: existing } = await supabase.from('user').select('id').eq('username', username).single();
        if (existing) { showToast('用户名已存在', 'error'); return; }
        
        const { data, error } = await supabase.from('user').insert({
            username: username,
            password: password,
            nickname: nickname,
            role: 0,
            integral: 0,
            status: 1
        }).select().single();
        
        if (error) throw error;
        currentUser = data;
        localStorage.setItem('user', JSON.stringify(data));
        updateUserUI();
        showToast('注册成功', 'success');
        navigateTo('home');
    } catch (error) {
        showToast('注册失败，请稍后重试', 'error');
    }
}

function logout() {
    currentUser = null;
    localStorage.removeItem('user');
    updateUserUI();
    showToast('已退出登录', 'success');
    navigateTo('home');
}

function updateUserUI() {
    const loginBtn = document.getElementById('loginBtn');
    const userInfo = document.getElementById('userInfo');
    const adminPanel = document.getElementById('adminPanel');
    
    if (currentUser) {
        loginBtn.style.display = 'none';
        userInfo.style.display = 'flex';
        document.getElementById('usernameDisplay').textContent = currentUser.nickname;
        const roleNames = ['普通用户', '版主', '管理员'];
        document.getElementById('userRole').textContent = roleNames[currentUser.role] || '普通用户';
        adminPanel.style.display = currentUser.role === 2 ? 'block' : 'none';
        if (currentUser.role === 2) loadAdminPanel();
    } else {
        loginBtn.style.display = 'block';
        userInfo.style.display = 'none';
        adminPanel.style.display = 'none';
    }
}

function showPostEditor() {
    if (!currentUser) { showToast('请先登录', 'warning'); return; }
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-editor').classList.add('active');
    loadCategoriesToEditor();
}

async function loadCategoriesToEditor() {
    const select = document.getElementById('postCate');
    try {
        const { data, error } = await supabase.from('category').select('*').eq('status', 1).order('sort', { ascending: true });
        if (error) throw error;
        select.innerHTML = data.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
        if (currentCategory) select.value = currentCategory;
    } catch (error) {
        select.innerHTML = '<option value="1">小魔头暴露啦专区</option><option value="2">Galgame交流区</option><option value="3">游戏包上传下载</option><option value="4">同人创作区</option>';
    }
}

async function submitPost() {
    if (!currentUser) { showToast('请先登录', 'warning'); return; }
    const cateId = document.getElementById('postCate').value;
    const title = document.getElementById('postTitle').value.trim();
    const content = document.getElementById('postContent').value.trim();
    if (!title || !content) { showToast('请填写标题和内容', 'warning'); return; }
    
    try {
        let filePath = null, fileName = null, fileSize = null;
        const fileInput = document.getElementById('postFile');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName = file.name;
            fileSize = file.size;
            filePath = 'https://example.com/downloads/' + Date.now() + '-' + file.name;
        }
        const isCheck = (filePath || cateId === '3') ? 0 : 1;
        
        const { error } = await supabase.from('post').insert({
            cate_id: cateId,
            user_id: currentUser.id,
            title: title,
            content: content,
            file_path: filePath,
            file_name: fileName,
            file_size: fileSize,
            is_check: isCheck,
            status: 1
        });
        if (error) throw error;
        showToast(isCheck ? '帖子已提交，等待审核' : '发布成功', 'success');
        cancelPost();
        navigateTo('home');
        loadHomePosts();
    } catch (error) {
        showToast('发布失败，请稍后重试', 'error');
    }
}

function cancelPost() {
    document.getElementById('postTitle').value = '';
    document.getElementById('postContent').value = '';
    document.getElementById('postFile').value = '';
    navigateTo('home');
}

async function loadAdminPanel() {
    const content = document.getElementById('adminContent');
    content.innerHTML = '<div class="loading">加载中</div>';
    
    try {
        const { data, error } = await supabase
            .from('post')
            .select('*, category (name), user (nickname)')
            .eq('is_check', 0)
            .order('create_time', { ascending: false });
        
        if (error) throw error;
        
        if (!data || data.length === 0) {
            content.innerHTML = '<div class="empty-state"><div class="empty-state-text">暂无待审核帖子</div></div>';
            return;
        }
        
        content.innerHTML = data.map(post => `
            <div class="admin-item">
                <div class="admin-item-header"><strong>${post.title}</strong><span>${post.category?.name}</span></div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 10px;">作者: ${post.user?.nickname} | ${formatDate(post.create_time)}</div>
                <div class="admin-item-actions">
                    <button onclick="approvePost(${post.id})" class="btn-primary btn-small btn-success">✅ 通过</button>
                    <button onclick="rejectPost(${post.id})" class="btn-primary btn-small btn-danger">❌ 拒绝</button>
                    <button onclick="deletePost(${post.id})" class="btn-secondary btn-small">🗑️ 删除</button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        content.innerHTML = '<div class="empty-state"><div class="empty-state-text">加载失败</div></div>';
    }
}

async function approvePost(postId) {
    try {
        await supabase.from('post').update({ is_check: 1 }).eq('id', postId);
        showToast('已通过审核', 'success');
        loadAdminPanel();
    } catch (error) { showToast('操作失败', 'error'); }
}

async function rejectPost(postId) {
    try {
        await supabase.from('post').update({ is_check: 2 }).eq('id', postId);
        showToast('已拒绝', 'success');
        loadAdminPanel();
    } catch (error) { showToast('操作失败', 'error'); }
}

async function deletePost(postId) {
    if (!confirm('确定要删除这篇帖子吗？')) return;
    try {
        await supabase.from('post').delete().eq('id', postId);
        showToast('已删除', 'success');
        loadAdminPanel();
    } catch (error) { showToast('删除失败', 'error'); }
}

function handleAdminTabClick(e) {
    const tab = e.target.dataset.tab;
    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
    e.target.classList.add('active');
    if (tab === 'pending') loadAdminPanel();
    else if (tab === 'users') loadUserManagement();
}

async function loadUserManagement() {
    const content = document.getElementById('adminContent');
    content.innerHTML = '<div class="loading">加载中</div>';
    
    try {
        const { data, error } = await supabase.from('user').select('*').order('create_time', { ascending: false });
        if (error) throw error;
        
        if (!data || data.length === 0) {
            content.innerHTML = '<div class="empty-state"><div class="empty-state-text">暂无用户</div></div>';
            return;
        }
        
        const roleNames = ['普通用户', '版主', '管理员'];
        content.innerHTML = data.map(user => `
            <div class="admin-item">
                <div class="admin-item-header"><strong>${user.nickname}</strong><span>${roleNames[user.role] || '普通用户'}</span></div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 10px;">用户名: ${user.username} | 积分: ${user.integral || 0}</div>
                <div class="admin-item-actions">
                    ${user.role < 2 ? '<button onclick="setUserRole(' + user.id + ', 2)" class="btn-primary btn-small">设为管理员</button>' : ''}
                    <button onclick="toggleUserStatus(${user.id}, ${user.status === 1 ? 0 : 1})" class="btn-secondary btn-small">${user.status === 1 ? '禁用' : '启用'}</button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        content.innerHTML = '<div class="empty-state"><div class="empty-state-text">加载失败</div></div>';
    }
}

async function setUserRole(userId, role) {
    try {
        await supabase.from('user').update({ role: role }).eq('id', userId);
        showToast('设置成功', 'success');
        loadUserManagement();
    } catch (error) { showToast('操作失败', 'error'); }
}

async function toggleUserStatus(userId, status) {
    try {
        await supabase.from('user').update({ status: status }).eq('id', userId);
        showToast(status ? '已启用' : '已禁用', 'success');
        loadUserManagement();
    } catch (error) { showToast('操作失败', 'error'); }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast ' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 3000);
}
