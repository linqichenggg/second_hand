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

// 获取订单和接收者ID
$order_id = $_POST['order_id'];
$receiver_id = $_POST['receiver_id'];

// 处理发送留言请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
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

    // 获取留言内容
    $subject = $_POST['subject'];
    $content = $_POST['content'];

    // 插入留言到数据库
    $send_message_sql = "INSERT INTO inbox_messages (sender_id, receiver_id, order_id, subject, content) VALUES (?, ?, ?, ?, ?)";
    $send_message_stmt = $conn->prepare($send_message_sql);
    if ($send_message_stmt) {
        $send_message_stmt->bind_param("iiiss", $user_id, $receiver_id, $order_id, $subject, $content);
        if ($send_message_stmt->execute()) {
            echo "<script>alert('留言已发送'); window.location.href='orders.php';</script>";
        } else {
            echo "<script>alert('发送留言时出错: " . $send_message_stmt->error . "'); window.location.href='orders.php';</script>";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发送留言 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>发送留言</h1>
        <form action="send_message.php" method="post">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($receiver_id); ?>">
            <div class="form-group">
                <label for="subject">主题：</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="content">内容：</label>
                <textarea id="content" name="content" rows="5" required></textarea>
            </div>
            <button type="submit" name="send_message">发送留言</button>
            <button onclick="window.location.href='orders.php';" style="margin-top: 20px;">返回订单页面</button>
        </form>
    </div>
</body>
</html>
