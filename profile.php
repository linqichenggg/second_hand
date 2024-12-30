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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> 首页</a>
                <a href="my_items.php" class="nav-link"><i class="fas fa-box"></i> 我的物品</a>
                <a href="sell_item.php" class="nav-link"><i class="fas fa-plus"></i> 发布物品</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> 个人中心</a>
                <a href="inbox.php" class="nav-link"><i class="fas fa-envelope"></i> 收件箱</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> 退出</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="margin-bottom: 2rem;">
            <h2>个人信息</h2>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> 用户名
                    </label>
                    <input type="text" name="username" class="form-input" 
                           value="<?php echo htmlspecialchars($user['username'] ?? '默认用户名'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-id-card"></i> 学号
                    </label>
                    <input type="text" value="<?php echo htmlspecialchars($user['student_id']); ?>" 
                           class="form-input" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-phone"></i> 联系电话
                    </label>
                    <input type="tel" name="phone_number" class="form-input" 
                           value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">
                    <i class="fas fa-save"></i> 更新信息
                </button>
            </form>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <h2>修改校区</h2>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-university"></i> 校区
                    </label>
                    <select name="campus" class="form-input" required>
                        <option value="卫岗" <?php echo ($user['campus'] == '卫岗') ? 'selected' : ''; ?>>卫岗校区</option>
                        <option value="滨江" <?php echo ($user['campus'] == '滨江') ? 'selected' : ''; ?>>滨江校区</option>
                        <option value="浦口" <?php echo ($user['campus'] == '浦口') ? 'selected' : ''; ?>>浦口校区</option>
                    </select>
                </div>
                <button type="submit" name="update_campus" class="btn btn-primary">
                    <i class="fas fa-save"></i> 修改校区
                </button>
            </form>
        </div>

        <div class="card">
            <h2>修改密码</h2>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> 当前密码
                    </label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-key"></i> 新密码
                    </label>
                    <input type="password" name="new_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-check"></i> 确认新密码
                    </label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">
                    <i class="fas fa-save"></i> 修改密码
                </button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>