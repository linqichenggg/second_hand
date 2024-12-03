<?php
// 引入认证文件
require_once 'auth.php';

// 引入数据库连接文件
require_once 'db_connect.php';

// 获取购物车中的物品
$sql = "SELECT cart_items.cart_item_id, items.item_id, items.title, items.price, items.image_url, items.user_id AS seller_id, cart_items.quantity FROM cart_items JOIN items ON cart_items.item_id = items.item_id WHERE cart_items.user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("SQL prepare error (删除已购买的物品): " . $conn->error . " | SQL: " . $sql . " | Cart Item ID: " . $purchase_cart_item_id);
    die("数据库错误，请稍后重试。");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 处理删除购物车物品请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_cart_item_id'])) {
    $delete_cart_item_id = $_POST['delete_cart_item_id'];
    $sql = "DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $delete_cart_item_id, $user_id);
        if ($stmt->execute()) {
            echo "<div class='notification success'>物品已从购物车中删除，请刷新</div>";
        } else {
            echo "<script>alert('删除购物车物品时出错: " . $stmt->error . "'); window.location.href='cart.php';</script>";
        }
    }
}

// 处理购买单个物品请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase_cart_item_id'])) {
    $purchase_cart_item_id = $_POST['purchase_cart_item_id'];
    $conn->begin_transaction();
    try {
        // 获取购物车物品信息
        $sql = "SELECT items.item_id, items.price, items.user_id AS seller_id, cart_items.quantity FROM cart_items JOIN items ON cart_items.item_id = items.item_id WHERE cart_items.cart_item_id = ? AND cart_items.user_id = ?";
        $stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("SQL prepare error (购买单个物品): " . $conn->error);
    die("出问题了！！！！");
}
$stmt->bind_param("ii", $purchase_cart_item_id, $user_id);
        if (!$stmt->execute()) {
            error_log("Execute error: " . $stmt->error);
            throw new Exception("数据库执行错误");
        }
        $item_result = $stmt->get_result();
        $item = $item_result->fetch_assoc();

        if ($item) {
            $total_price = $item['price'] * $item['quantity'];
            $seller_id = $item['seller_id'];
            // 插入订单数据
            $sql = "INSERT INTO orders (buyer_id, seller_id, item_id, total_price, order_date, status) VALUES (?, ?, ?, ?, NOW(), 'pending')";
            $stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("SQL prepare error (插入订单数据): " . $conn->error);
    die("插入订单数据出现问题");
}
$stmt->bind_param("iiid", $user_id, $seller_id, $item['item_id'], $total_price);
            $stmt->execute();

            // 删除购物车中已购买的物品
            $sql = "DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("SQL prepare error (删除已购买的物品): " . $conn->error);
    die("数据库错误，请稍后重试。");
}
$stmt->bind_param("ii", $purchase_cart_item_id, $user_id);
if (!$stmt->execute()) {
    error_log("Execute error (删除已购买的物品): " . $stmt->error);
    throw new Exception("数据库执行错误");
}

            // 更新 items 表，标记物品已售出
            $sql = "UPDATE items SET status = 'sold' WHERE item_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                error_log("SQL prepare error (更新物品状态): " . $conn->error);
                die("数据库错误，请稍后重试。");
            }
            $stmt->bind_param("i", $item['item_id']);
            if (!$stmt->execute()) {
                error_log("Execute error (更新物品状态): " . $stmt->error);
                throw new Exception("数据库执行错误");
            }

            $conn->commit();
            echo "<div class='notification success'>购买成功，页面即将跳转到订单页面...</div>";
            echo "<script>setTimeout(function() { window.location.href='orders.php'; }, 2000);</script>";
        } else {
            throw new Exception('未找到购物车物品');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('购买过程中出错: " . $e->getMessage() . "'); window.location.href='cart.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>购物车 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <script>
        function deleteCartItem(cartItemId) {
            const formData = new FormData();
            formData.append('delete_cart_item_id', cartItemId);

            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector('.container').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>购物车</h1>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="cart-item">
                    <?php 
                        echo !empty($row['image_url']) ? '<img src="' . htmlspecialchars($row['image_url']) . '" alt="Image" style="width: 150px; height: auto;">' : '<img src="uploads/no_image.png" alt="no_image" style="width: 150px; height: auto;">';
                    ?>
                    <div class="cart-item-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p>价格：<?php echo htmlspecialchars($row['price']); ?> 元</p>
                        <p>数量：<?php echo htmlspecialchars($row['quantity']); ?></p>
                    </div>
                    <button onclick="deleteCartItem(<?php echo $row['cart_item_id']; ?>)" class="delete-button">删除</button>
                    <form action="cart.php" method="post" style="display:inline-block;">
                        <input type="hidden" name="purchase_cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                        <button type="submit" name="purchase" class="purchase-button">购买</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>您的购物车中没有商品。</p>
        <?php endif; ?>
        <button onclick="window.location.href='index.php';" style="margin-top: 20px;">返回主页</button>
    </div>
</body>
</html>