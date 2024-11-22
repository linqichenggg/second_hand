<?php
// login.php

// 启用错误报告用于调试（开发环境中启用，生产环境中应禁用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件和数据库连接
require_once 'auth.php';
require_once 'db_connect.php';


// 生成CSRF令牌（如果尚未生成）
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 管理员凭据（明文存储）
$admin_username = 'admin';
$admin_password = 'njau'; // 明文密码

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 验证CSRF令牌
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('无效的请求'); window.location.href='login.php';</script>";
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 检查是否为管理员登录
    if (isset($_POST['login_type']) && $_POST['login_type'] === 'admin') {
        // 管理员登录
        if ($username === $admin_username && $password === $admin_password) {
            // 登录成功，设置管理员会话
            session_regenerate_id(true); // 防止会话固定攻击
            $_SESSION['user_id'] = 0; // 使用0表示管理员（或其他标识）
            $_SESSION['username'] = $admin_username;
            $_SESSION['role'] = 'admin';

            echo "<script>alert('管理员登录成功'); window.location.href='admin_dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('管理员用户名或密码错误'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        // 普通用户登录逻辑
        // 查询用户
        $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $password === $user['password']) { // 假设普通用户密码也是明文存储
            // 登录成功，设置用户会话
            session_regenerate_id(true); // 防止会话固定攻击
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'user'; // 标识为普通用户

            echo "<script>alert('登录成功'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('用户名或密码错误'); window.location.href='login.php';</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 简单的样式调整 */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
        .admin-button {
            background-color: #f44336;
            margin-top: 10px;
        }
        .admin-button:hover {
            background-color: #d32f2f;
        }
        .back-button {
            background-color: #2196F3;
            margin-top: 10px;
        }
        .back-button:hover {
            background-color: #1976D2;
        }
        /* 默认隐藏管理员登录表单 */
        #admin-login-form {
            display: none;
        }
    </style>
    <script>
        function showAdminLogin() {
            document.getElementById('user-login-form').style.display = 'none';
            document.getElementById('admin-login-form').style.display = 'block';
        }

        function showUserLogin() {
            document.getElementById('admin-login-form').style.display = 'none';
            document.getElementById('user-login-form').style.display = 'block';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>登录</h1>
        <!-- 用户登录表单 -->
        <form id="user-login-form" action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">登录</button>
            <button type="button" onclick="window.location.href='register.php';" class="back-button">注册</button>
            <button type="button" onclick="showAdminLogin();" class="admin-button">管理员登录</button>
        </form>
        <!-- 管理员登录表单 -->
        <form id="admin-login-form" action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="login_type" value="admin">
            <div class="form-group">
                <label for="admin_username">管理员用户名：</label>
                <input type="text" id="admin_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="admin_password">密码：</label>
                <input type="password" id="admin_password" name="password" required>
            </div>
            <button type="submit">管理员登录</button>
            <button type="button" onclick="showUserLogin();" class="back-button">返回用户登录</button>
        </form>
    </div>
</body>
</html>
