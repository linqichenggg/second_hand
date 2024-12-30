<?php
// login.php

// 启用错误报告用于调试（开发环境中启用，生产环境中应禁用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件和数据库连接
require_once 'db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 启用错误报告（开发环境下启用，生产环境应禁用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 会话超时设置（例如 30 分钟）
$timeout_duration = 1800;

// 检查会话最后活动时间
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('会话已过期，请重新登录'); window.location.href='login.php';</script>";
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// 防止会话固定攻击
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:">
    <title>登录 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 2rem auto;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">欢迎回来</h1>
                <p style="color: var(--text-secondary);">登录您的小农账户</p>
            </div>

            <!-- 用户登录表单 -->
            <form id="user-login-form" action="login.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                <div style="display: grid; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        登录
                    </button>
                    <button type="button" onclick="window.location.href='register.php';" class="btn btn-secondary">
                        注册
                    </button>
                    <button type="button" onclick="showAdminLogin();" class="btn" style="background: var(--warning);">
                        管理员登录
                    </button>
                </div>
            </form>

            <!-- 管理员登录表单 -->
            <form id="admin-login-form" action="login.php" method="post" style="display: none;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="login_type" value="admin">
                <div class="form-group">
                    <label class="form-label">
                        管理员用户名
                    </label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        密码
                    </label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <div style="display: grid; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        管理员登录
                    </button>
                    <button type="button" onclick="showUserLogin();" class="btn btn-secondary">
                        返回用户登录
                    </button>
                </div>
            </form>
        </div>
    </div>

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
</body>
</html>
