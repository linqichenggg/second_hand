<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取用户信息
$user_id = $_SESSION['user_id'];

// 处理评价请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $rated_user_id = $_POST['rated_user_id'];

    // 调试输出，确认表单数据是否正确提交
    var_dump($order_id, $rating, $rated_user_id);

    if ($order_id && $rating && $rated_user_id) {
        // 插入评价记录
        $rate_sql = "INSERT INTO ratings (order_id, rated_user_id, rating) VALUES (?, ?, ?)";
        $rate_stmt = $conn->prepare($rate_sql);

        // 检查 SQL 准备是否成功
        if (!$rate_stmt) {
            // 输出 SQL 错误
            echo "SQL 错误: " . $conn->error;
        } else {
            // 如果 SQL 准备成功，继续执行
            $rate_stmt->bind_param("iii", $order_id, $rated_user_id, $rating);
            if ($rate_stmt->execute()) {
                // 更新用户总分
                $update_score_sql = "UPDATE users SET total_score = total_score + ? WHERE user_id = ?";
                $update_score_stmt = $conn->prepare($update_score_sql);

                // 检查是否成功准备了 SQL 语句
                if (!$update_score_stmt) {
                    // 输出 SQL 错误
                    echo "更新用户分数时 SQL 错误: " . $conn->error;
                } else {
                    // 正常执行绑定参数
                    $score_change = ($rating == 1) ? 1 : -1;
                    $update_score_stmt->bind_param("ii", $score_change, $rated_user_id);
                    if ($update_score_stmt->execute()) {
                        echo "<script>alert('评价成功'); window.location.href='orders.php';</script>";
                    } else {
                        echo "<script>alert('更新用户总分时出错: " . $update_score_stmt->error . "'); window.location.href='orders.php';</script>";
                    }
                }
            } else {
                echo "<script>alert('评价时出错: " . $rate_stmt->error . "'); window.location.href='orders.php';</script>";
            }
        }
    } else {
        echo "<script>alert('表单数据不完整，请检查'); window.location.href='orders.php';</script>";
    }
}

// 关闭数据库连接
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>评价页面 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>评价页面</h1>
        <form action="rate_order.php" method="post">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($_POST['order_id']); ?>">
            <input type="hidden" name="rated_user_id" value="<?php echo htmlspecialchars($_POST['rated_user_id']); ?>">
            <p>请对该订单进行评价：</p>
            <button type="submit" name="rating" value="1" class="rate-button">好评</button>
            <button type="submit" name="rating" value="-1" class="rate-button">差评</button>
        </form>
        <button onclick="window.location.href='orders.php';" style="margin-top: 20px;">返回订单页面</button>
    </div>
</body>
</html>