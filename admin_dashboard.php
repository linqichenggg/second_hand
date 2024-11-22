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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员面板 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 简单的样式调整 */
        body {
            font-family: Arial, sans-serif;
            background-color: #eef;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
            padding: 14px 20px;
        }
        .navbar a {
            float: left;
            color: #f2f2f2;
            text-align: center;
            padding: 0 16px;
            text-decoration: none;
            font-size: 17px;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            padding: 20px;
        }
        .section {
            margin-bottom: 40px;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .action-buttons button {
            margin-right: 5px;
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .action-buttons button:hover {
            background-color: #d32f2f;
        }
        .status-button {
            background-color: #4CAF50;
        }
        .status-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin_dashboard.php">管理员面板</a>
        <a href="logout.php">登出</a>
    </div>
    <div class="container">
        <h1>欢迎, 管理员!</h1>
    <!-- <?php echo htmlspecialchars($_SESSION['username']); ?> -->
        
        <!-- 用户管理 -->
        <div class="section">
            <h2>用户管理</h2>
            <?php
            // 查询所有用户
            $stmt = $conn->prepare("SELECT user_id, username, student_id, phone_number, campus FROM users");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table>
                <tr>
                    <th>用户ID</th>
                    <th>用户名</th>
                    <th>学号</th>
                    <th>电话</th>
                    <th>校区</th>
                    <th>操作</th>
                </tr>
                <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($user['campus']); ?></td>
                    <td class="action-buttons">
                        <!-- 这里可以添加更多管理功能，例如编辑用户信息 -->
                        <a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>"><button>删除用户</button></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
        <!-- 商品管理 -->
        <div class="section">
            <h2>商品管理</h2>
            <?php
            // 查询所有商品
            $stmt = $conn->prepare("SELECT item_id, user_id, title, price, category, item_condition, status FROM items");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table>
                <tr>
                    <th>物品ID</th>
                    <th>用户ID</th>
                    <th>标题</th>
                    <th>价格</th>
                    <th>类别</th>
                    <th>成色</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                <?php while ($item = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['price']); ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td><?php echo htmlspecialchars($item['item_condition']); ?></td>
                    <td><?php echo htmlspecialchars($item['status']); ?></td>
                    <td class="action-buttons">
                        <?php if ($item['status'] === 'available'): ?>
                            <a href="update_item_status.php?item_id=<?php echo $item['item_id']; ?>&status=sold"><button class="status-button">标记为已售出</button></a>
                        <?php elseif ($item['status'] === 'sold'): ?>
                            <a href="update_item_status.php?item_id=<?php echo $item['item_id']; ?>&status=available"><button class="status-button">标记为可用</button></a>
                        <?php endif; ?>
                        <a href="delete_item.php?item_id=<?php echo $item['item_id']; ?>"><button>删除商品</button></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
        <!-- 其他管理功能可以在此添加 -->
    </div>
</body>
</html>
