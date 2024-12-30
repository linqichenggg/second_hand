<?php
// delete_order.php

// 启用错误报告用于调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件和数据库连接
require_once 'auth.php';
require_once 'db_connect.php';

// 确保 session 已经开始，避免重复调用 session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 检查用户是否为管理员
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('您没有权限执行此操作'); window.location.href='login.php';</script>";
    exit();
}

// 获取 `order_id` 参数
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    // 开始事务
    $conn->begin_transaction();
    
    try {
        // 删除与订单相关的留言记录
        $stmt = $conn->prepare("DELETE FROM inbox_messages WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // 删除订单记录
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // 提交事务
        $conn->commit();
        
        echo "<script>alert('订单及相关留言已删除'); window.location.href='admin_dashboard.php';</script>";
    } catch (Exception $e) {
        // 出错时回滚事务
        $conn->rollback();
        echo "<script>alert('删除失败: " . $e->getMessage() . "'); window.location.href='admin_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('无效的请求'); window.location.href='admin_dashboard.php';</script>";
}

$conn->close();
?>