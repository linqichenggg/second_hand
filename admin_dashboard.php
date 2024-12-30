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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;"><i class="fas fa-users"></i> 用户管理</h2>
                <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                    总用户数：<?php echo $user_result->num_rows; ?>
                </span>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--background); text-align: left;">
                            <th style="padding: 1rem;">用户ID</th>
                            <th style="padding: 1rem;">用户名</th>
                            <th style="padding: 1rem;">学号</th>
                            <th style="padding: 1rem;">校区</th>
                            <th style="padding: 1rem;">联系电话</th>
                            <th style="padding: 1rem;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $user_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($user['student_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($user['campus']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($user['phone_number']); ?></td>
                                <td style="padding: 1rem;">
                                    <a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>" 
                                       class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;"
                                       onclick="return confirm('确定要删除此用户吗？');">
                                        <i class="fas fa-trash"></i> 删除
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 商品管理部分 -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;"><i class="fas fa-shopping-bag"></i> 商品管理</h2>
                <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                    总商品数：<?php echo $item_result->num_rows; ?>
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--background); text-align: left;">
                            <th style="padding: 1rem;">商品ID</th>
                            <th style="padding: 1rem;">发布者ID</th>
                            <th style="padding: 1rem;">标题</th>
                            <th style="padding: 1rem;">价格</th>
                            <th style="padding: 1rem;">类别</th>
                            <th style="padding: 1rem;">状态</th>
                            <th style="padding: 1rem;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $item_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($item['item_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($item['user_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="padding: 1rem;">¥<?php echo htmlspecialchars($item['price']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($item['category']); ?></td>
                                <td style="padding: 1rem;">
                                    <span class="badge" style="background: <?php echo $item['status'] == 'available' ? 'var(--success)' : 'var(--secondary)'; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.8rem;">
                                        <?php echo $item['status'] == 'available' ? '在售' : '已售'; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <a href="delete_item.php?item_id=<?php echo $item['item_id']; ?>" 
                                       class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;"
                                       onclick="return confirm('确定要删除此商品吗？');">
                                        <i class="fas fa-trash"></i> 删除
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 订单管理部分 -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;"><i class="fas fa-shopping-cart"></i> 订单管理</h2>
                <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                    总订单数：<?php echo $order_result->num_rows; ?>
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--background); text-align: left;">
                            <th style="padding: 1rem;">订单ID</th>
                            <th style="padding: 1rem;">买家ID</th>
                            <th style="padding: 1rem;">商品ID</th>
                            <th style="padding: 1rem;">订单日期</th>
                            <th style="padding: 1rem;">状态</th>
                            <th style="padding: 1rem;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $order_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td><?php echo htmlspecialchars($user['user_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['student_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['phone_number'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['campus'] ?? ''); ?></td>
                                <td style="padding: 1rem;">
                                    <a href="delete_order.php?order_id=<?php echo $order['order_id']; ?>" 
                                       class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;"
                                       onclick="return confirm('确定要删除此订单吗？');">
                                        <i class="fas fa-trash"></i> 删除
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>