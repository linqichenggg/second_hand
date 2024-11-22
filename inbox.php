<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取用户信息
$user_id = $_SESSION['user_id'];

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
