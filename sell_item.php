<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 处理发布物品请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据并进行必要的清理
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);
    $item_condition = trim($_POST['item_condition']);

    // 初始化$image_url
    $image_url = 'no_image.png'; // 默认图片

    // 处理文件上传
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        // 创建上传目录（如果不存在）
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = basename($_FILES['image_file']['name']);
        $file_size = $_FILES['image_file']['size'];
        $file_type = mime_content_type($file_tmp);

        // 允许的文件类型
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        // 最大文件大小（例如 2MB）
        $max_size = 2 * 1024 * 1024;

        // 验证文件类型
        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('仅支持 JPG, PNG, GIF 格式的图片'); window.location.href='sell_item.php';</script>";
            exit();
        }

        // 验证文件大小
        if ($file_size > $max_size) {
            echo "<script>alert('图片大小不能超过 2MB'); window.location.href='sell_item.php';</script>";
            exit();
        }

        // 生成唯一文件名，防止文件名冲突
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('img_', true) . '.' . $file_ext;
        $target_file = 'uploads/' . $new_file_name;

        // 移动上传文件到目标目录
        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_url = $target_file;
        } else {
            echo "<script>alert('图片上传失败'); window.location.href='sell_item.php';</script>";
            exit();
        }
    }

    // 插入新物品数据
    $sql = "INSERT INTO items (user_id, title, description, price, category, item_condition, image_url, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'available')";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issdsss", $user_id, $title, $description, $price, $category, $item_condition, $image_url);

        if ($stmt->execute()) {
            echo "<script>alert('物品发布成功'); window.location.href='index.php';</script>";
        } else {
            // 删除已上传的图片，以防止孤立文件
            if ($image_url !== 'no_image.png' && file_exists($image_url)) {
                unlink($image_url);
            }
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
            min-height: 100vh; /* 确保页面高度适应内容 */
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="number"], textarea, select, input[type="file"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-button {
            background-color: #2196F3;
            margin-top: 10px;
        }
        .back-button:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>发布物品</h1>
        <form action="sell_item.php" method="post" enctype="multipart/form-data">
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
                <label for="image_file">上传图片：</label>
                <input type="file" id="image_file" name="image_file" accept="image/*">
            </div>
            <button type="submit">发布物品</button>
            <button type="button" onclick="window.location.href='index.php';" class="back-button">返回主页</button>
        </form>
    </div>
</body>
</html>
