<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nickname = trim($_POST['nickname']) ?: '小魔头粉丝';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password)) {
        $error = "用户名和密码不能为空";
    } elseif ($password !== $confirm_password) {
        $error = "两次密码输入不一致";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = "用户名长度需要在3-20个字符之间";
    } elseif (strlen($password) < 6) {
        $error = "密码长度至少6个字符";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $error = "用户名已存在";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO user (username, password, nickname) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $nickname]);

            $success = "注册成功！请登录";
            header("refresh:2;url=login.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - 小魔头Galgame论坛</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=ZCOOL+KuaiLe&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a1423 0%, #2c1b54 50%, #1a1423 100%);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-title {
            width: 100%;
            padding: 40px 20px;
            background: linear-gradient(135deg, #4b2e83, #2c1b54);
            text-align: center;
            border-bottom: 4px solid #c9a8ff;
        }
        .top-title h1 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 48px;
            letter-spacing: 4px;
            color: #ffe699;
            text-shadow: 3px 3px 8px #000;
        }
        .container {
            max-width: 450px;
            margin: 50px auto;
            padding: 20px;
        }
        .login-box {
            background: rgba(39, 28, 68, 0.95);
            border-radius: 16px;
            padding: 40px;
            border: 2px solid #6348a0;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .login-box h2 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 32px;
            color: #ffd878;
            text-align: center;
            margin-bottom: 30px;
            border-left: 5px solid #c9a8ff;
            padding-left: 15px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e0d4f5;
            font-size: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #3d2e66;
            border-radius: 8px;
            background: #1a1423;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #7c52d6;
            box-shadow: 0 0 20px rgba(124, 82, 214, 0.3);
        }
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #7c52d6, #5e3cad);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'ZCOOL KuaiLe', cursive;
        }
        .submit-btn:hover {
            background: linear-gradient(135deg, #946aff, #7c52d6);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(124, 82, 214, 0.4);
        }
        .links {
            text-align: center;
            margin-top: 25px;
            color: #9988bb;
        }
        .links a {
            color: #c9a8ff;
            text-decoration: none;
            transition: color 0.3s;
        }
        .links a:hover {
            color: #ffd878;
        }
        .error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff6b7a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #7dff9e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        footer {
            text-align: center;
            padding: 25px;
            color: #9988bb;
            font-size: 14px;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="top-title">
        <h1>小魔头Galgame</h1>
    </div>

    <div class="container">
        <div class="login-box">
            <h2>用户注册</h2>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo sanitize($error); ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo sanitize($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" placeholder="请输入用户名" required>
                </div>

                <div class="form-group">
                    <label>昵称</label>
                    <input type="text" name="nickname" placeholder="请输入昵称（选填）">
                </div>

                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" placeholder="请输入密码" required>
                </div>

                <div class="form-group">
                    <label>确认密码</label>
                    <input type="password" name="confirm_password" placeholder="请再次输入密码" required>
                </div>

                <button type="submit" class="submit-btn">立即注册</button>
            </form>

            <div class="links">
                已有账号？<a href="login.php">立即登录</a>
            </div>
        </div>
    </div>

    <footer>
        小魔头Galgame论坛 · 全站免费资源
    </footer>
</body>
</html>