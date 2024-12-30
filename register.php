<?php
session_start();
require_once 'db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 处理注册请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $student_id = $_POST['student_id'];
    $phone_number = $_POST['phone_number'];
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
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 2rem auto;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">创建账户</h1>
                <p style="color: var(--text-secondary);">加入小农二手交易平台</p>
            </div>
            <form action="register.php" method="post" id="registerForm" novalidate>
                <div class="form-group">
                    <label class="form-label">
                        用户名
                    </label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        密码
                    </label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        学号
                    </label>
                    <input type="text" name="student_id" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        联系电话
                    </label>
                    <input type="tel" name="phone_number" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        校区
                    </label>
                    <select name="campus" class="form-input" required>
                        <option value="">请选择校区</option>
                        <option value="卫岗">卫岗校区</option>
                        <option value="滨江">滨江校区</option>
                        <option value="浦口">浦口校区</option>
                    </select>
                </div>
                <div style="display: grid; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        注册
                    </button>
                    <a href="login.php" class="btn btn-secondary">
                        返回登录
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="register.js"></script>
</body>
</html>