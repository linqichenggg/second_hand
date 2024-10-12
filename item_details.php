<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('请先登录'); window.location.href='login.php';</script>";
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

// 获取物品信息
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $sql = "SELECT * FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    if (!$item) {
        echo "<script>alert('物品不存在'); window.location.href='index.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('无效的物品ID'); window.location.href='index.php';</script>";
    exit();
}

// 处理添加到购物车请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    // 检查是否是物品发布者自己
    if ($item['user_id'] == $user_id) {
        echo "<script>alert('无法将自己的物品添加到购物车'); window.location.href='item_details.php?item_id=$item_id';</script>";
    } else {
        $sql = "INSERT INTO cart_items (user_id, item_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $item_id);
            if ($stmt->execute()) {
                echo "<script>alert('已添加到购物车'); window.location.href='cart.php';</script>";
            } else {
                echo "<script>alert('添加到购物车时出错: " . $stmt->error . "'); window.location.href='item_details.php?item_id=$item_id';</script>";
            }
        } else {
            echo "<script>alert('准备插入数据时出错: " . $conn->error . "'); window.location.href='item_details.php?item_id=$item_id';</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>物品详情 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>物品详情</h1>
        <div class="item-details">
            <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'no_image.png'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
            <div class="item-info">
                <h2><?php echo htmlspecialchars($item['title']); ?></h2>
                <p>价格：¥<?php echo htmlspecialchars($item['price']); ?></p>
                <p>描述：<?php echo htmlspecialchars($item['description']); ?></p>
                <p>成色：<?php echo htmlspecialchars($item['item_condition']); ?></p>
            </div>
            <?php if ($item['user_id'] != $user_id): ?>
                <form action="item_details.php?item_id=<?php echo $item_id; ?>" method="post">
                    <button type="submit" name="add_to_cart">添加到购物车</button>
                </form>
            <?php endif; ?>
            <button onclick="window.location.href='index.php';" style="margin-top: 10px;">返回主页</button>
        </div>
    </div>
</body>
</html>