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
    <style>
        /* 样式调整 */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .navbar {
            background-color: #4CAF50;
            padding: 15px;
            color: white;
            text-align: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
        }
        .navbar a:hover {
            background-color: #45a049;
        }
        .items-section {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* 自动适应布局 */
            gap: 20px;
            padding: 20px;
        }
        .item-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background-color: #fff;
            text-align: center;
        }
        .item-card img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .item-info {
            margin-top: 10px;
        }
        .filter-section {
            margin-bottom: 20px;
            text-align: center;
        }
        .filter-section select, .filter-section button {
            padding: 10px;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">首页</a>
        <a href="my_items.php">我的物品</a>
        <a href="sell_item.php">发布物品</a>
        <a href="profile.php">个人中心</a>
    </div>

    <div class="container">
        <h1>欢迎你，同学！</h1>

        <!-- 商品筛选 -->
        <div class="filter-section">
            <form action="index.php" method="get">
                <label for="category">选择商品类别：</label>
                <select name="category" id="category">
                    <option value="">所有类别</option>
                    <option value="书籍" <?php if ($selected_category == '书籍') echo 'selected'; ?>>书籍</option>
                    <option value="电子产品" <?php if ($selected_category == '电子产品') echo 'selected'; ?>>电子产品</option>
                    <option value="家具" <?php if ($selected_category == '家具') echo 'selected'; ?>>家具</option>
                    <option value="运动用品" <?php if ($selected_category == '运动用品') echo 'selected'; ?>>运动用品</option>
                    <option value="生活用品" <?php if ($selected_category == '生活用品') echo 'selected'; ?>>生活用品</option>
                </select>
                <button type="submit">筛选</button>
            </form>
        </div>

        <h2>最新发布的物品</h2>
        <div class="items-section">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="item-card">
                        <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'uploads/no_image.png'; ?>" alt="No Image">
                        <div class="item-info">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p>价格：¥<?php echo htmlspecialchars($item['price']); ?></p>
                            <p>类别：<?php echo htmlspecialchars($item['category']); ?></p>
                            <a href="item_details.php?item_id=<?php echo htmlspecialchars($item['item_id']); ?>" class="btn">查看详情</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>暂时没有商品，试着发布一些物品吧！</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="navbar">
        <p>© 2024 小农二手交易系统</p>
    </footer>
</body>
</html>