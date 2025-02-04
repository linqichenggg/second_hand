<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取用户信息
$user_id = $_SESSION['user_id'];

// 获取用户作为买家的订单
$buyer_orders_sql = "SELECT orders.order_id, orders.seller_id, orders.buyer_id, orders.order_date, orders.total_price, orders.status, items.title, sellers.username AS seller_username, shuttle_trips.route_id, shuttle_trips.departure_time
                    FROM orders
                    JOIN items ON orders.item_id = items.item_id
                    JOIN users AS sellers ON orders.seller_id = sellers.user_id
                    LEFT JOIN shuttle_trips ON orders.shuttle_trip_id = shuttle_trips.trip_id
                    WHERE orders.buyer_id = ?
                    ORDER BY orders.order_date DESC";

// 检查 SQL 是否准备成功
$buyer_stmt = $conn->prepare($buyer_orders_sql);
if (!$buyer_stmt) {
    echo "SQL Prepare Error: " . $conn->error;
    exit();  // 终止脚本执行
}

$buyer_stmt->bind_param("i", $user_id);
$buyer_stmt->execute();
$buyer_orders_result = $buyer_stmt->get_result();

// 获取用户作为卖家的订单
$seller_orders_sql = "SELECT orders.order_id, orders.seller_id, orders.buyer_id, orders.order_date, orders.total_price, orders.status, items.title, buyers.username AS buyer_username
                    FROM orders
                    JOIN items ON orders.item_id = items.item_id
                    JOIN users AS buyers ON orders.buyer_id = buyers.user_id
                    WHERE items.user_id = ?
                    ORDER BY orders.order_date DESC";

// 检查 SQL 是否准备成功
$seller_stmt = $conn->prepare($seller_orders_sql);
if (!$seller_stmt) {
    echo "SQL Prepare Error: " . $conn->error;
    exit();  // 终止脚本执行
}

$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_orders_result = $seller_stmt->get_result();

// 获取所有班车（无论是否有可用容量）
$shuttle_sql = "SELECT trip_id, route_id, departure_time, departure_campus, arrive_campus FROM shuttle_trips";
$shuttle_result = $conn->query($shuttle_sql);
$shuttles = [];
if ($shuttle_result->num_rows > 0) {
    while ($shuttle = $shuttle_result->fetch_assoc()) {
        $shuttles[] = $shuttle;
    }
}

// 处理删除订单请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order_id'])) {
    $delete_order_id = $_POST['delete_order_id'];
    $delete_order_sql = "DELETE FROM orders WHERE order_id = ? AND (seller_id = ? OR buyer_id = ?)";
    $delete_stmt = $conn->prepare($delete_order_sql);
    if ($delete_stmt) {
        $delete_stmt->bind_param("iii", $delete_order_id, $user_id, $user_id);
        if ($delete_stmt->execute()) {
            echo "<div class='notification success'>订单已删除，请刷新页面</div>";
        } else {
            echo "<script>alert('删除订单时出错: " . $delete_stmt->error . "'); window.location.href='orders.php';</script>";
        }
    }
}

// 处理班车分配请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_shuttle'])) {
    $order_id = $_POST['order_id'];
    $shuttle_trip_id = $_POST['shuttle_trip_id'];

    // 更新订单表，添加班车信息
    $assign_sql = "UPDATE orders SET shuttle_trip_id = ?, status = 'shipped' WHERE order_id = ?";
    $assign_stmt = $conn->prepare($assign_sql);
    if ($assign_stmt) {
        $assign_stmt->bind_param("ii", $shuttle_trip_id, $order_id);
        if ($assign_stmt->execute()) {
            echo "<script>alert('班车已成功分配给订单'); window.location.href='orders.php';</script>";
        } else {
            echo "<script>alert('分配班车时出错: " . $assign_stmt->error . "'); window.location.href='orders.php';</script>";
        }
    } else {
        echo "<script>alert('准备分配班车时出错: " . $conn->error . "'); window.location.href='orders.php';</script>";
    }
}

//确认收货
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delivery'])) {
    $order_id = $_POST['order_id'];
    $confirm_delivery_sql = "UPDATE orders SET status = 'delivered' WHERE order_id = ? AND buyer_id = ?";
    $confirm_delivery_stmt = $conn->prepare($confirm_delivery_sql);
    if ($confirm_delivery_stmt) {
        $confirm_delivery_stmt->bind_param("ii", $order_id, $user_id);
        if ($confirm_delivery_stmt->execute()) {
            echo "<script>alert('订单已确认收货'); window.location.href='orders.php';</script>";
        } else {
            echo "<script>alert('确认收货时出错: " . $confirm_delivery_stmt->error . "'); window.location.href='orders.php';</script>";
        }
    } else {
        echo "<script>alert('准备确认收货时出错: " . $conn->error . "'); window.location.href='orders.php';</script>";
    }
}

// 处理评价请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rate_order'])) {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $rated_user_id = $_POST['rated_user_id'];

    // 插入评价记录
    $rate_sql = "INSERT INTO ratings (order_id, rated_user_id, rating) VALUES (?, ?, ?)";
    $rate_stmt = $conn->prepare($rate_sql);
    if ($rate_stmt) {
        $rate_stmt->bind_param("iii", $order_id, $rated_user_id, $rating);
        if ($rate_stmt->execute()) {
            // 更新用户的总分
            $update_score_sql = "UPDATE users SET total_score = total_score + ? WHERE user_id = ?";
            $update_score_stmt = $conn->prepare($update_score_sql);
            $score_change = ($rating == 1) ? 1 : -1;
            $update_score_stmt->bind_param("ii", $score_change, $rated_user_id);
            if ($update_score_stmt->execute()) {
                echo "<script>alert('评价成功'); window.location.href='orders.php';</script>";
            } else {
                echo "<script>alert('更新评分时出错: " . $update_score_stmt->error . "'); window.location.href='orders.php';</script>";
            }
        } else {
            echo "<script>alert('评价时出错: " . $rate_stmt->error . "'); window.location.href='orders.php';</script>";
        }
    } else {
        echo "<script>alert('准备评价时出错: " . $conn->error . "'); window.location.href='orders.php';</script>";
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的订单 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 基础设置 */
        :root {
            --primary: #4CAF50;        /* 主要绿色 */
            --primary-dark: #388E3C;   /* 深绿色 */
            --secondary: #81C784;      /* 浅绿色 */
            --secondary-dark: #66BB6A; /* 中绿色 */
            --success: #43A047;        /* 成功绿色 */
            --danger: #e57373;         /* 柔和的红色 */
            --warning: #FFB74D;        /* 温和的橙色 */
            --info: #4DB6AC;          /* 青绿色 */
            --background: #F1F8E9;     /* 浅绿背景 */
            --surface: #ffffff;        /* 保持白色 */
            --text: #212529;
            --text-secondary: #558B2F; /* 深绿色文字 */
            --border: #dee2e6;
            --radius: 0.5rem;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* 全局重置 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h1 {
            text-align: center;
            color: var(--text);
        }

        .order {
            background-color: var(--surface);
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .order h3 {
            color: var(--text);
            font-size: 1.25rem;
        }

        .order p {
            color: var(--text-secondary);
            margin: 0.5rem 0;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .delete-button {
            background-color: var(--danger);
        }

        .delete-button:hover {
            background-color: #d32f2f;
        }

        .rate-button {
            background-color: var(--info);
        }

        .rate-button:hover {
            background-color: #00796b;
        }

        .message-button {
            background-color: var(--secondary);
        }

        .message-button:hover {
            background-color: #66bb6a;
        }

        .confirm-delivery-button {
            background-color: var(--success);
        }

        .confirm-delivery-button:hover {
            background-color: #388e3c;
        }

        .toggle-btn {
            background-color: var(--primary-dark);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
        }

        .toggle-btn:hover {
            background-color: var(--primary);
        }

        .shuttle-info {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>我的订单</h1>

        <div class="shuttle-info">
            <h3>可用班车信息：</h3>
            <button class="toggle-btn" onclick="toggleShuttles()">显示/隐藏班车信息</button>
            <div id="shuttle-list" style="display:none;">
                <?php if (!empty($shuttles)): ?>
                    <ul>
                        <?php foreach ($shuttles as $shuttle): ?>
                            <li><?php echo htmlspecialchars($shuttle['route_id']) . " - 出发时间: " . htmlspecialchars($shuttle['departure_time']) . " - 出发地: " . htmlspecialchars($shuttle['departure_campus']) . " - 到达地: " . htmlspecialchars($shuttle['arrive_campus']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>目前没有可用的班车。</p>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function toggleShuttles() {
                var shuttleList = document.getElementById('shuttle-list');
                shuttleList.style.display = (shuttleList.style.display === 'none' || shuttleList.style.display === '') ? 'block' : 'none';
            }
        </script>
        
        <h2>您购买的订单</h2>
        <?php if ($buyer_orders_result->num_rows > 0): ?>
            <?php while ($row = $buyer_orders_result->fetch_assoc()): ?>
                <div class="order">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p>卖家：<?php echo htmlspecialchars($row['seller_username']); ?></p>
                    <p>订单日期：<?php echo htmlspecialchars($row['order_date']); ?></p>
                    <p>总价：¥<?php echo htmlspecialchars($row['total_price']); ?></p>
                    <p>状态：<?php echo htmlspecialchars($row['status']); ?></p>
                    <?php if (!empty($row['route_id'])): ?>
                        <p>运输班车：<?php echo htmlspecialchars($row['route_id']) . " - 出发时间: " . htmlspecialchars($row['departure_time']); ?></p>
                    <?php endif; ?>
                    <form action="orders.php" method="post" style="display:inline;">
                        <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                        <button type="submit" name="delete_order" class="delete-button" onclick="return confirm('确定要删除此订单吗？');">删除订单</button>
                    </form>
                    <?php if ($row['status'] !== 'delivered'): ?>
                        <form action="orders.php" method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                            <button type="submit" name="confirm_delivery" class="confirm-delivery-button">确认收货</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($row['status'] === 'delivered'): ?>
                        <form action="rate_order.php" method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                            <input type="hidden" name="rated_user_id" value="<?php echo htmlspecialchars($row['seller_id']); ?>">
                            <button type="submit" class="rate-button">评价卖家</button>
                        </form>
                    <?php endif; ?>
                    <form action="send_message.php" method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                            <input type="hidden" name="receiver_id" value="<?php echo ($user_id === $row['seller_id']) ? $row['buyer_id'] : $row['seller_id']; ?>">
                            <button type="submit" class="message-button">留言</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>您还没有作为买家的订单。</p>
        <?php endif; ?>

        <h2>您卖出的订单</h2>
        <?php if ($seller_orders_result->num_rows > 0): ?>
            <?php while ($row = $seller_orders_result->fetch_assoc()): ?>
                <div class="order">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p>买家：<?php echo htmlspecialchars($row['buyer_username']); ?></p>
                    <p>订单日期：<?php echo htmlspecialchars($row['order_date']); ?></p>
                    <p>总价：¥<?php echo htmlspecialchars($row['total_price']); ?></p>
                    <p>状态：<?php echo htmlspecialchars($row['status']); ?></p>
                    <?php if ($row['status'] !== 'delivered'): ?>
                        <form action="orders.php" method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                            <label for="shuttle_trip_id">选择班车运输：</label>
                            <select id="shuttle_trip_id" name="shuttle_trip_id" required>
                                <?php foreach ($shuttles as $shuttle): ?>
                                    <option value="<?php echo htmlspecialchars($shuttle['trip_id']); ?>">
                                        <?php echo htmlspecialchars($shuttle['route_id']) . " - 出发时间: " . htmlspecialchars($shuttle['departure_time']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_shuttle">分配班车</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($row['status'] === 'delivered'): ?>
                        <form action="rate_order.php" method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                            <input type="hidden" name="rated_user_id" value="<?php echo htmlspecialchars($row['buyer_id']); ?>">
                            <button type="submit" class="rate-button">评价买家</button>
                        </form>
                    <?php endif; ?>
                    <form action="orders.php" method="post" style="display:inline;">
                        <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                        <button type="submit" name="delete_order" class="delete-button" onclick="return confirm('确定要删除此订单吗？');">删除订单</button>
                    </form>
                    <form action="send_message.php" method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                        <input type="hidden" name="receiver_id" value="<?php echo isset($row['seller_id']) ? ($user_id === $row['seller_id'] ? $row['buyer_id'] : $row['seller_id']) : ''; ?>">
                        <button type="submit" class="message-button">留言</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>您还没有作为卖家的订单。</p>
        <?php endif; ?>
        
        <button onclick="window.location.href='index.php';" style="margin: 20px auto; display: block;">返回主页</button>
    </div>
</body>
</html>