<?php
// delete_order.php

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

// 获取 `order_id` 参数
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    // 检查订单是否存在
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        // 删除订单记录
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            echo "<script>alert('订单已删除'); window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('删除失败: " . $stmt->error . "'); window.location.href='admin_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('订单不存在'); window.location.href='admin_dashboard.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('无效的请求'); window.location.href='admin_dashboard.php';</script>";
}

$conn->close();
?>