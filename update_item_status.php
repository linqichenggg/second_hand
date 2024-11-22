<?php
// update_item_status.php

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

// 获取 `item_id` 和 `status` 参数
if (isset($_GET['item_id']) && isset($_GET['status'])) {
    $item_id = intval($_GET['item_id']);
    $status = $_GET['status'];

    // 验证 `status` 的值
    $allowed_status = ['available', 'sold'];
    if (!in_array($status, $allowed_status)) {
        echo "<script>alert('无效的状态'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }

    // 更新商品状态
    $stmt = $conn->prepare("UPDATE items SET status = ? WHERE item_id = ?");
    $stmt->bind_param("si", $status, $item_id);

    if ($stmt->execute()) {
        echo "<script>alert('商品状态已更新为 " . htmlspecialchars($status) . "'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('更新失败: " . $stmt->error . "'); window.location.href='admin_dashboard.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('无效的请求'); window.location.href='admin_dashboard.php';</script>";
}

$conn->close();
?>
