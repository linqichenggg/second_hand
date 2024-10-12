<?php
session_start();

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 获取用户信息
$user_id = $_SESSION['user_id'];

// 数据库连接信息
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "1";

// 创建数据库连接
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 获取用户的留言
$inbox_sql = "SELECT inbox_messages.*, users.username AS sender_name FROM inbox_messages 
              JOIN users ON inbox_messages.sender_id = users.user_id
              WHERE inbox_messages.receiver_id = ?
              ORDER BY inbox_messages.timestamp DESC";
$inbox_stmt = $conn->prepare($inbox_sql);
$inbox_stmt->bind_param("i", $user_id);
$inbox_stmt->execute();
$inbox_result = $inbox_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>收件箱 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>收件箱</h1>
        <?php if ($inbox_result->num_rows > 0): ?>
            <?php while ($message = $inbox_result->fetch_assoc()): ?>
                <div class="message">
                    <h3>主题：<?php echo htmlspecialchars($message['subject']); ?></h3>
                    <p>来自：<?php echo htmlspecialchars($message['sender_name']); ?></p>
                    <p>时间：<?php echo htmlspecialchars($message['timestamp']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>目前没有新的留言。</p>
        <?php endif; ?>
        <button onclick="window.location.href='index.php';" style="margin-top: 20px;">返回主页</button>
    </div>
</body>
</html>
