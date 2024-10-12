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

// 处理删除物品请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_item_id'])) {
    $delete_item_id = $_POST['delete_item_id'];
    
    // 删除购物车中关联的物品
    $sql = "DELETE FROM cart_items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $delete_item_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // 删除用户发布的物品
    $sql = "DELETE FROM items WHERE item_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $delete_item_id, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('物品已删除'); window.location.href='my_items.php';</script>";
        } else {
            echo "<script>alert('删除物品时出错: " . $conn->error . "'); window.location.href='my_items.php';</script>";
        }
        $stmt->close();
    }
}

// 获取用户发布的物品
$sql = "SELECT * FROM items WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的物品 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        .item-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .item-info {
            flex-grow: 1;
            margin-left: 20px;
        }
        button {
            background-color: #f44336;
        }
        button:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>我的物品</h1>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="item-card">
                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                    <div class="item-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p>价格：¥<?php echo htmlspecialchars($row['price']); ?></p>
                        <p>状态：<?php echo htmlspecialchars($row['status']); ?></p>
                    </div>
                    <form action="my_items.php" method="post">
                        <input type="hidden" name="delete_item_id" value="<?php echo $row['item_id']; ?>">
                        <button type="submit">删除</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>您还没有发布任何物品。</p>
        <?php endif; ?>
        <a href="index.php" class="back-button">返回主页</a>
    </div>
</body>
</html>