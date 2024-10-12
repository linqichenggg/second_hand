<?php
session_start();

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

// 处理登录请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 查询用户是否存在
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // 验证密码
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo "<script>alert('登录成功'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('密码错误'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('用户不存在'); window.location.href='login.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] {
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
        .register-link {
            text-align: center;
            margin-top: 10px;
        }
        .register-link a {
            color: #007BFF;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 添加系统 Logo -->
        <img src="logo.jpg" alt="小农二手交易系统 Logo" class="logo">
        <h1>登录 - 小农</h1>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">登录</button>
        </form>
        <div class="register-link">
            <p>没有账号？<a href="register.php">立即注册</a></p>
        </div>
    </div>
</body>
</html>