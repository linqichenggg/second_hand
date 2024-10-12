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

// 处理发布物品请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $item_condition = $_POST['item_condition'];
    $image_url = $_POST['image_url'];

    // 插入新物品数据
    $sql = "INSERT INTO items (user_id, title, description, price, category, item_condition, image_url, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'available')";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issdsss", $user_id, $title, $description, $price, $category, $item_condition, $image_url);

        if ($stmt->execute()) {
            echo "<script>alert('物品发布成功'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('物品发布失败，请重试: " . $stmt->error . "'); window.location.href='sell_item.php';</script>";
        }
    } else {
        echo "<script>alert('准备插入数据时出错: " . $conn->error . "'); window.location.href='sell_item.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发布物品 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 400px;
        }
        .form-group {
            text-align: left;
        }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>发布物品</h1>
        <form action="sell_item.php" method="post">
            <div class="form-group">
                <label for="title">物品标题：</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">物品描述：</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="price">价格 (元)：</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="category">类别：</label>
                <input type="text" id="category" name="category">
            </div>
            <div class="form-group">
                <label for="item_condition">物品成色：</label>
                <select id="item_condition" name="item_condition" required>
                    <option value="new">全新</option>
                    <option value="like_new">几乎全新</option>
                    <option value="used">二手</option>
                    <option value="old">较旧</option>
                </select>
            </div>
            <div class="form-group">
                <label for="image_url">图片链接：</label>
                <input type="text" id="image_url" name="image_url">
            </div>
            <button type="submit">发布物品</button>
            <button onclick="window.location.href='index.php';" style="margin-top: 10px;">返回主页</button>
        </form>
    </div>
</body>
</html>

