<?php
require_once 'config.php';

$post_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ? AND status = 1 AND file_path != ''");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("文件不存在");
}

$file_path = $post['file_path'];
if (!file_exists($file_path)) {
    die("文件已被删除");
}

$pdo->prepare("UPDATE post SET download_count = download_count + 1 WHERE id = ?")->execute([$post_id]);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rawurlencode($post['file_name']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache');

readfile($file_path);
exit;
?>