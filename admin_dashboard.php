<?php
// admin_dashboard.php

// 启用错误报告用于调试（开发环境中启用，生产环境中应禁用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件和数据库连接
require_once 'auth.php';
require_once 'db_connect.php';

// 检查用户是否为管理员
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('您没有权限访问此页面'); window.location.href='login.php';</script>";
    exit();
}

// 查询所有用户
$user_result = $conn->query("SELECT * FROM users");

// 查询所有商品
$item_result = $conn->query("SELECT * FROM items");

// 查询所有订单
$order_result = $conn->query("SELECT * FROM orders");

// 查询所有收件箱消息
$inbox_sql = "SELECT inbox_messages.*, users.username AS sender_name FROM inbox_messages 
              JOIN users ON inbox_messages.sender_id = users.user_id
              ORDER BY inbox_messages.timestamp DESC";
$inbox_result = $conn->query($inbox_sql);  // 这里是直接使用 query 来获取结果

// 如果查询失败，初始化一个空的结果集
if (!$inbox_result) {
    $inbox_result = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员面板 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> 控制台</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> 退出</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- 用户管理部分 -->
        <div class="card" style="margin-bottom: 2rem;">
            <h2><i class="fas fa-users"></i> 用户管理</h2>
            <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                总用户数：<?php echo $user_result->num_rows; ?>
            </span>
            <table>
                <thead>
                    <tr>
                        <th>用户ID</th>
                        <th>用户名</th>
                        <th>学号</th>
                        <th>校区</th>
                        <th>联系电话</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $user_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['campus']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                            <td><a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-danger" onclick="return confirm('确定要删除此用户吗？');">删除</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- 商品管理部分 -->
        <div class="card">
            <h2><i class="fas fa-shopping-bag"></i> 商品管理</h2>
            <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                总商品数：<?php echo $item_result->num_rows; ?>
            </span>
            <table>
                <thead>
                    <tr>
                        <th>商品ID</th>
                        <th>发布者ID</th>
                        <th>标题</th>
                        <th>价格</th>
                        <th>类别</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $item_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td>¥<?php echo htmlspecialchars($item['price']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo $item['status'] == 'available' ? '在售' : '已售'; ?></td>
                            <td><a href="delete_item.php?item_id=<?php echo $item['item_id']; ?>" class="btn btn-danger" onclick="return confirm('确定要删除此商品吗？');">删除</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- 收件箱管理部分 -->
        <div class="card">
            <h2><i class="fas fa-envelope"></i> 收件箱管理</h2>
            <?php if ($inbox_result && $inbox_result->num_rows > 0): ?>
                <?php while ($message = $inbox_result->fetch_assoc()): ?>
                    <div class="message" style="border-bottom: 1px solid var(--border); padding: 1rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <strong><?php echo htmlspecialchars($message['sender_name']); ?></strong> <br>
                                <span><?php echo htmlspecialchars($message['subject']); ?></span>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($message['timestamp']); ?>
                            </div>
                        </div>
                        <div style="color: var(--text-secondary); margin-top: 1rem;">
                            <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary);"></i>
                    <p>没有新的消息。</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 订单管理部分 -->
        <div class="card">
            <h2><i class="fas fa-shopping-cart"></i> 订单管理</h2>
            <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                总订单数：<?php echo $order_result->num_rows; ?>
            </span>
            <table>
                <thead>
                    <tr>
                        <th>订单ID</th>
                        <th>买家ID</th>
                        <th>商品ID</th>
                        <th>订单日期</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $order_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['buyer_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><a href="delete_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-danger" onclick="return confirm('确定要删除此订单吗？');">删除</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>