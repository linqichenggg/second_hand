<?php
session_start();

// 启用所有错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// 处理注册请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];  // 直接存储用户输入的密码
    $student_id = $_POST['student_id'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'];
    $campus = $_POST['campus'];

    // 检查是否存在相同的学号
    $sql = "SELECT * FROM users WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('学号已存在，请直接登录'); window.location.href='login.php';</script>";
        } else {
            // 插入新用户数据
            $sql = "INSERT INTO users (username, password, student_id, phone_number, campus) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssss", $username, $password, $student_id, $phone_number, $campus);

                if ($stmt->execute()) {
                    echo "<script>alert('注册成功'); window.location.href='login.php';</script>";
                } else {
                    echo "<script>alert('注册失败，请重试: " . $stmt->error . "'); window.location.href='register.php';</script>";
                }
            } else {
                echo "<script>alert('准备插入数据时出错: " . $conn->error . "'); window.location.href='register.php';</script>";
            }
        }

        $stmt->close();
    } else {
        echo "<script>alert('准备查询数据时出错: " . $conn->error . "'); window.location.href='register.php';</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - 小农二手交易系统</title>
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
            width: 350px;
            text-align: center;
        }
        .logo {
            width: 100px;
            height: auto;
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
        .login-link {
            text-align: center;
            margin-top: 10px;
        }
        .login-link a {
            color: #007BFF;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 添加系统 Logo -->
        <img src="logo.jpg" alt="小农二手交易系统 Logo" class="logo">
        <h1>注册 - 小农</h1>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="student_id">学号：</label>
                <input type="text" id="student_id" name="student_id" required>
            </div>
            <div class="form-group">
                <label for="phone_number">联系电话：</label>
                <input type="text" id="phone_number" name="phone_number">
            </div>
            <div class="form-group">
                <label for="campus">校区：</label>
                <select id="campus" name="campus" required>
                    <option value="卫岗">卫岗校区</option>
                    <option value="滨江">滨江校区</option>
                    <option value="浦口">浦口校区</option>
                </select>
            </div>
            <button type="submit">注册</button>
        </form>
        <div class="login-link">
            <p>已有账号？<a href="login.php">直接登录</a></p>
        </div>
    </div>
</body>
</html>