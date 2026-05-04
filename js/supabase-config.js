/**
 * 幻想三国社区系统 - Supabase 配置文件
 * 
 * 配置说明:
 * - SUPABASE_URL: 你的 Supabase 项目 URL
 * - SUPABASE_ANON_KEY: 公开的匿名密钥（用于客户端）
 * 
 * 获取方式:
 * 1. 登录 Supabase Dashboard: https://supabase.com/dashboard
 * 2. 进入项目 -> Settings -> API
 * 3. 复制 Project URL 和 anon public key
 */

const SUPABASE_URL = 'https://btkupvzkzwnbbhpgkksc.supabase.co';

// ⚠️ 重要: 请从 Supabase Dashboard 获取你的 anon public key
// 位置: Settings -> API -> Project API keys -> anon public
const SUPABASE_ANON_KEY = 'YOUR_ANON_PUBLIC_KEY_HERE';

// 可选: 如果需要服务端功能，可以配置 service_role key
// ⚠️ 警告: 永远不要在前端代码中暴露 service_role key!
const SUPABASE_SERVICE_KEY = ''; // 仅在服务端使用

// Supabase 客户端配置
const SUPABASE_CONFIG = {
    url: SUPABASE_URL,
    anonKey: SUPABASE_ANON_KEY,
    
    // 客户端选项
    options: {
        // 自动刷新令牌
        autoRefreshToken: true,
        // 持久化会话
        persistSession: true,
        // 检测会话是否过期
        detectSessionInUrl: true,
        // 重试次数
        retries: 3,
        // 本地存储名称
        storageKey: '幻想三国-supabase-auth',
        // 全局头信息
        global: {
            headers: {
                'x-client-info': 'fantasy-three-kingdoms-community'
            }
        }
    }
};

// 导出配置供其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        SUPABASE_URL,
        SUPABASE_ANON_KEY,
        SUPABASE_SERVICE_KEY,
        SUPABASE_CONFIG
    };
}
