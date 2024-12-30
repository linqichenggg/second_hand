<?php
// index.php
session_start();
require_once 'db_connect.php';

// 获取商品类别筛选条件（如果有）
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// 获取所有商品，按照筛选条件筛选类别
$sql = "SELECT * FROM items";
if ($selected_category) {
    $sql .= " WHERE category = ?";
}
$stmt = $conn->prepare($sql);
if ($selected_category) {
    $stmt->bind_param("s", $selected_category);
}
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="index.php" class="nav-link">首页</a>
                <a href="my_items.php" class="nav-link">我的物品</a>
                <a href="sell_item.php" class="nav-link">发布物品</a>
                <a href="profile.php" class="nav-link">个人中心</a>
                <a href="inbox.php" class="nav-link">收件箱</a>
                <a href="return.php" class="nav-link">退出登录</a>
                <a href="cart.php" class="nav-link">购物车</a>
	<a href="orders.php" class="nav-link">我的订单</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="margin-bottom: 2rem;">
            <div class="filter-section">
                <form action="index.php" method="get" style="display: flex; gap: 1rem; align-items: center;">
                    <select name="category" class="form-input" style="min-width: 200px;">
                        <option value="">所有类别</option>
                        <option value="书籍" <?php if ($selected_category == '书籍') echo 'selected'; ?>>书籍</option>
                        <option value="电子产品" <?php if ($selected_category == '电子产品') echo 'selected'; ?>>电子产品</option>
                        <option value="家具" <?php if ($selected_category == '家具') echo 'selected'; ?>>家具</option>
                        <option value="运动用品" <?php if ($selected_category == '运动用品') echo 'selected'; ?>>运动用品</option>
                        <option value="生活用品" <?php if ($selected_category == '生活用品') echo 'selected'; ?>>生活用品</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> 筛选
                    </button>
                </form>
            </div>
        </div>

        <div class="grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="item-card">
                        <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'uploads/no_image.png'; ?>" 
                             alt="商品图片" class="item-image">
                        <div class="item-content">
                            <h3 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="item-price">¥<?php echo htmlspecialchars($item['price']); ?></p>
                            <div style="display: grid; gap: 0.5rem; margin-top: 1rem;">
                                <a href="item_details.php?item_id=<?php echo $item['item_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> 查看详情
                                </a>
                                <button onclick="addToCart(<?php echo $item['item_id']; ?>)" class="btn btn-secondary">
                                    <i class="fas fa-cart-plus"></i> 添加到购物车
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">暂时没有商品，试着发布一些物品吧！</p>
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