<?php
// delete_item.php

// 启用错误报告用于调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件和数据库连接
require_once 'auth.php';
require_once 'db_connect.php';

session_start();

// 检查用户是否为管理员
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('您没有权限执行此操作'); window.location.href='login.php';</script>";
    exit();
}

// 获取 `item_id` 参数
if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    // 获取商品的 `image_url` 以便删除图片文件
    $stmt = $conn->prepare("SELECT image_url FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if ($item) {
        $image_url = $item['image_url'];

        // 删除商品记录
        $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);

        if ($stmt->execute()) {
            // 删除图片文件（如果不是默认图片）
            if ($image_url !== 'no_image.png' && file_exists($image_url)) {
                unlink($image_url);
            }
            echo "<script>alert('商品已删除'); window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('删除失败: " . $stmt->error . "'); window.location.href='admin_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('商品不存在'); window.location.href='admin_dashboard.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('无效的请求'); window.location.href='admin_dashboard.php';</script>";
}

$conn->close();
?>
