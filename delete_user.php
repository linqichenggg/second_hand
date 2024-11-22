<?php
// delete_user.php

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

// 获取 `user_id` 参数
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // 防止管理员删除自己
    if ($user_id == $_SESSION['user_id']) {
        echo "<script>alert('您不能删除自己的账户'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }

    // 删除用户
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('用户已删除'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('删除失败: " . $stmt->error . "'); window.location.href='admin_dashboard.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('无效的请求'); window.location.href='admin_dashboard.php';</script>";
}

$conn->close();
?>
