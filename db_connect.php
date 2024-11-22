<?php
// db_connect.php

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "1";

// 创建数据库连接
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// 检查连接
if ($conn->connect_error) {
    // 在生产环境中，避免直接显示错误信息
    error_log("连接失败: " . $conn->connect_error);
    die("数据库连接失败，请稍后重试。");
}
?>
