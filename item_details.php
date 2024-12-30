<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取用户信息
$user_id = $_SESSION['user_id'];

// 获取物品信息
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $sql = "SELECT * FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    if (!$item) {
        echo "<script>alert('物品不存在'); window.location.href='index.php';</script>";
        exit();
    }

    // 获取卖家的评分
    $seller_id = $item['user_id'];
    $score_sql = "SELECT total_score FROM users WHERE user_id = ?";
    $score_stmt = $conn->prepare($score_sql);
    $score_stmt->bind_param("i", $seller_id);
    $score_stmt->execute();
    $score_result = $score_stmt->get_result();
    $seller_score = $score_result->fetch_assoc()['total_score'] ?? 0; // 如果没有评分，默认为0

} else {
    echo "<script>alert('无效的物品ID'); window.location.href='index.php';</script>";
    exit();
}



// 处理添加到购物车请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    // 检查是否是物品发布者自己
    if ($item['user_id'] == $user_id) {
        echo "<script>alert('无法将自己的物品添加到购物车'); window.location.href='item_details.php?item_id=$item_id';</script>";
    } else {
        $sql = "INSERT INTO cart_items (user_id, item_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $item_id);
            if ($stmt->execute()) {
                echo "<script>alert('已添加到购物车'); window.location.href='cart.php';</script>";
            } else {
                echo "<script>alert('添加到购物车时出错: " . $stmt->error . "'); window.location.href='item_details.php?item_id=$item_id';</script>";
            }
        } else {
            echo "<script>alert('准备插入数据时出错: " . $conn->error . "'); window.location.href='item_details.php?item_id=$item_id';</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品详情 - 小农二手交易系统</title>
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
        <div class="card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'uploads/no_image.png'; ?>" 
                         alt="商品图片" style="width: 100%; border-radius: var(--radius); margin-bottom: 1rem;">
                    <div style="background: var(--success); color: white; padding: 0.5rem; border-radius: var(--radius); text-align: center;">
                        卖家评分：<?php echo htmlspecialchars($seller_score); ?>
                    </div>
                </div>
                
                <div>
                    <h2 style="font-size: 1.5rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($item['title']); ?></h2>
                    <p class="item-price" style="font-size: 1.8rem; margin-bottom: 1.5rem;">
                        ¥<?php echo htmlspecialchars($item['price']); ?>
                    </p>
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">商品描述：</h3>
                        <p style="color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">商品成色：</h3>
                        <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($item['item_condition']); ?></p>
                    </div>
                    
                    <div style="display: grid; gap: 1rem;">
                        <button onclick="addToCart(<?php echo $item['item_id']; ?>)" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> 添加到购物车
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回主页
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>