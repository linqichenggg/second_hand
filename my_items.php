<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取用户信息
$user_id = $_SESSION['user_id'];

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
            <h2 style="margin-bottom: 0;">我的物品</h2>
        </div>

        <div class="grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="item-card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                             alt="商品图片" class="item-image">
                        <div class="item-content">
                            <h3 class="item-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="item-price">¥<?php echo htmlspecialchars($row['price']); ?></p>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <form action="my_items.php" method="post" style="flex: 1;">
                                    <input type="hidden" name="delete_item_id" value="<?php echo $row['item_id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                                        <i class="fas fa-trash"></i> 删除
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">您还没有发布任何物品</p>
                    <a href="sell_item.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 发布物品
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>