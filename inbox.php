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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> 首页</a>
                <a href="my_items.php" class="nav-link"><i class="fas fa-box"></i> 我的物品</a>
                <a href="sell_item.php" class="nav-link"><i class="fas fa-plus"></i> 发布物品</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> 个人中心</a>
                <a href="inbox.php" class="nav-link"><i class="fas fa-envelope"></i> 收件箱</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> 退出</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 0;">收件箱</h2>
        </div>

        <?php if ($inbox_result->num_rows > 0): ?>
            <?php while ($message = $inbox_result->fetch_assoc()): ?>
                <div class="card" style="margin-bottom: 1rem;">
                    <div style="border-bottom: 1px solid var(--border); margin-bottom: 1rem; padding-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-user" style="color: var(--primary);"></i>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($message['sender_name']); ?></span>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                <i class="fas fa-clock"></i>
                                <?php echo htmlspecialchars($message['timestamp']); ?>
                            </div>
                        </div>
                        <h3 style="margin: 0.5rem 0; color: var(--text);">
                            <?php echo htmlspecialchars($message['subject']); ?>
                        </h3>
                    </div>
                    <div style="color: var(--text-secondary);">
                        <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 2rem;">
                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                <p style="color: var(--text-secondary);">目前没有新的留言</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>
</html>
