<?php
// auth.php

// 启动会话
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 启用错误报告（开发环境下启用，生产环境应禁用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 会话超时设置（例如 30 分钟）
$timeout_duration = 1800;

// 检查会话最后活动时间
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('会话已过期，请重新登录'); window.location.href='login.php';</script>";
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// 防止会话固定攻击
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // 从会话中获取用户ID
} else {
    // 如果没有登录，跳转到登录页或其他页面
    echo "<script>alert('请先登录'); window.location.href='login.php';</script>";
    exit();
}

// 可选：进一步检查用户权限或角色
/*
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('您没有权限访问此页面'); window.location.href='index.php';</script>";
    exit();
}
*/
?>
