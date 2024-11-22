<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取用户信息
$user_id = $_SESSION['user_id'];

// 获取用户个人信息
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// 处理更新用户信息请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];

    $update_sql = "UPDATE users SET username = ?, phone_number = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt) {
        $update_stmt->bind_param("ssi", $username, $phone_number, $user_id);
        if ($update_stmt->execute()) {
            echo "<script>alert('个人信息更新成功'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('更新信息时出错: " . $update_stmt->error . "'); window.location.href='profile.php';</script>";
        }
        $update_stmt->close();
    }
}

// 处理更改密码请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 验证当前密码是否正确
    if ($current_password === $user['password']) {
        if ($new_password === $confirm_password) {
            $update_password_sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_password_stmt = $conn->prepare($update_password_sql);
            if ($update_password_stmt) {
                $update_password_stmt->bind_param("si", $new_password, $user_id);
                if ($update_password_stmt->execute()) {
                    echo "<script>alert('密码更新成功'); window.location.href='profile.php';</script>";
                } else {
                    echo "<script>alert('更新密码时出错: " . $update_password_stmt->error . "'); window.location.href='profile.php';</script>";
                }
                $update_password_stmt->close();
            }
        } else {
            echo "<script>alert('新密码和确认密码不匹配'); window.location.href='profile.php';</script>";
        }
    } else {
        echo "<script>alert('当前密码不正确'); window.location.href='profile.php';</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_campus'])) {
    $new_campus = $_POST['campus'];
    $update_campus_sql = "UPDATE users SET campus = ? WHERE user_id = ?";
    $update_campus_stmt = $conn->prepare($update_campus_sql);
    if ($update_campus_stmt) {
        $update_campus_stmt->bind_param("si", $new_campus, $user_id);
        if ($update_campus_stmt->execute()) {
            echo "<script>alert('校区更新成功'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('更新校区时出错: " . $update_campus_stmt->error . "'); window.location.href='profile.php';</script>";
        }
        $update_campus_stmt->close();
    } else {
        echo "<script>alert('准备更新数据时出错: " . $conn->error . "'); window.location.href='profile.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人中心 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>个人中心</h1>
        <form action="profile.php" method="post">
            <div class="form-group">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="student_id">学号：</label>
                <input type="text" id="student_id" value="<?php echo htmlspecialchars($user['student_id']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="phone_number">联系电话：</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
            </div>
            <button type="submit" name="update_profile">更新信息</button>
        </form>
        <form action="profile.php" method="post">
            <h2>修改校区</h2>
            <div class="form-group">
                <label for="campus">校区：</label>
                <select id="campus" name="campus" required>
                    <option value="卫岗" <?php echo ($user['campus'] == '卫岗') ? 'selected' : ''; ?>>卫岗校区</option>
                    <option value="滨江" <?php echo ($user['campus'] == '滨江') ? 'selected' : ''; ?>>滨江校区</option>
                    <option value="浦口" <?php echo ($user['campus'] == '浦口') ? 'selected' : ''; ?>>浦口校区</option>
                </select>
            </div>
            <button type="submit" name="update_campus">修改校区</button>
        </form>
        <hr>
        <form action="profile.php" method="post">
            <h2>更改密码</h2>
            <div class="form-group">
                <label for="current_password">当前密码：</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">新密码：</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">确认新密码：</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password">更改密码</button>
        </form>
        <a href="index.php" class="back-button">返回主页</a>
    </div>
</body>
</html>