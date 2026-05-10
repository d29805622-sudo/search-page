<?php
require_once 'config.php';
requireLogin();

$cate_id = $_GET['cate_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM category WHERE id = ? AND status = 1");
$stmt->execute([$cate_id]);
$category = $stmt->fetch();

if (!$category) {
    die("板块不存在");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $file = $_FILES['file'] ?? null;

    if (empty($title) || empty($content)) {
        $error = "标题和内容不能为空";
    } else {
        $file_path = '';
        $file_name = '';
        $file_size = '';

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $new_filename;
            $file_name = $file['name'];
            $file_size = formatFileSize($file['size']);
            
            move_uploaded_file($file['tmp_name'], $file_path);
        }

        $is_check = isAdmin() ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO post (cate_id, user_id, title, content, file_path, file_name, file_size, is_check) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cate_id, $_SESSION['user_id'], $title, $content, $file_path, $file_name, $file_size, $is_check]);
        
        $post_id = $pdo->lastInsertId();
        
        $success_msg = isAdmin() ? "发布成功！" : "发布成功！等待管理员审核。";
        header("Location: post.php?id=$post_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发布帖子 - 小魔头Galgame论坛</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=ZCOOL+KuaiLe&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a1423 0%, #2c1b54 50%, #1a1423 100%);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .top-title {
            width: 100%;
            padding: 30px 20px;
            background: linear-gradient(135deg, #4b2e83, #2c1b54);
            text-align: center;
            border-bottom: 4px solid #c9a8ff;
        }
        .top-title h1 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 42px;
            letter-spacing: 4px;
            color: #ffe699;
            text-shadow: 3px 3px 8px #000;
        }
        .main {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .form-box {
            background: #271c44;
            border-radius: 12px;
            border: 1px solid #6348a0;
            padding: 30px;
        }
        .form-box h2 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 28px;
            color: #ffd878;
            margin-bottom: 25px;
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
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #3d2e66;
            border-radius: 8px;
            background: #1a1423;
            color: #fff;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s;
        }
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #7c52d6;
            box-shadow: 0 0 20px rgba(124, 82, 214, 0.3);
        }
        .file-upload {
            border: 2px dashed #6348a0;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload:hover {
            border-color: #7c52d6;
            background: rgba(124, 82, 214, 0.1);
        }
        .file-upload input[type="file"] {
            display: none;
        }
        .file-upload-text {
            color: #9988bb;
            font-size: 14px;
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
        .error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff6b7a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #c9a8ff;
            text-decoration: none;
        }
        .back-link:hover { color: #ffd878; }
        footer {
            text-align: center;
            padding: 25px;
            color: #9988bb;
            font-size: 14px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="top-title">
        <h1>小魔头Galgame</h1>
    </div>

    <div class="main">
        <a href="category.php?id=<?php echo $cate_id; ?>" class="back-link">← 返回板块</a>

        <div class="form-box">
            <h2>发布帖子 - <?php echo sanitize($category['name']); ?></h2>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo sanitize($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>帖子标题</label>
                    <input type="text" name="title" placeholder="请输入帖子标题" required>
                </div>

                <div class="form-group">
                    <label>帖子内容</label>
                    <textarea name="content" placeholder="请输入帖子内容..." required></textarea>
                </div>

                <div class="form-group">
                    <label>附件上传（可选）</label>
                    <div class="file-upload" onclick="document.getElementById('file').click()">
                        <input type="file" id="file" name="file">
                        <div class="file-upload-text">
                            📎 点击选择文件 | 支持任意格式文件
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">发布帖子</button>
            </form>
        </div>
    </div>

    <footer>
        小魔头Galgame论坛 · 全站免费资源
    </footer>

    <script>
        document.getElementById('file').addEventListener('change', function() {
            var fileName = this.files[0] ? this.files[0].name : '未选择文件';
            document.querySelector('.file-upload-text').textContent = '📎 已选择: ' + fileName;
        });
    </script>
</body>
</html>