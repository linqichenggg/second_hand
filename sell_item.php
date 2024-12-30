<?php
// 启用错误报告用于调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入认证文件和数据库连接
require_once 'auth.php';
require_once 'db_connect.php';

// 处理发布物品请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查是否有文件上传
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // 检查文件是否为图片
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // 尝试上传文件
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                echo "<script>alert('抱歉，上传文件时出错。'); window.location.href='sell_item.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('文件不是图片。'); window.location.href='sell_item.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('请选择要上传的图片。'); window.location.href='sell_item.php';</script>";
        exit();
    }

    // 获取表单数据并进行必要的清理
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);  // 获取用户选择的类别
    $item_condition = trim($_POST['item_condition']);

    // 插入新物品数据
    $sql = "INSERT INTO items (user_id, title, description, price, category, item_condition, image_url, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'available')";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issdsss", $user_id, $title, $description, $price, $category, $item_condition, $image_url);

        if ($stmt->execute()) {
            echo "<script>alert('物品发布成功'); window.location.href='index.php';</script>";
        } else {
            // 删除已上传的图片，以防止孤立文件
            if ($image_url !== 'no_image.png' && file_exists($image_url)) {
                unlink($image_url);
            }
            echo "<script>alert('物品发布失败，请重试: " . $stmt->error . "'); window.location.href='sell_item.php';</script>";
        }
    } else {
        echo "<script>alert('准备插入数据时出错: " . $conn->error . "'); window.location.href='sell_item.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发布物品 - 小农二手交易系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="index.php" class="nav-link">首页</a>
                <a href="my_items.php" class="nav-link">我的物品</a>
                <a href="sell_item.php" class="nav-link">发布物品</a>
                <a href="profile.php" class="nav-link">个人中心</a>
                <a href="inbox.php" class="nav-link">收件箱</a>
                <a href="logout.php" class="nav-link">退出</a>
                <a href="cart.php" class="nav-link">购物车</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 2rem;">发布物品</h2>
            <form action="sell_item.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()" id="sellForm">
                <div class="form-group">
                    <label class="form-label">上传图片</label>
                    <div style="border: 2px dashed var(--border); border-radius: var(--radius); padding: 2rem; text-align: center; cursor: pointer;" 
                         onclick="document.getElementById('image_file').click();">
                        <p style="color: var(--text-secondary);">点击或拖拽图片到此处上传</p>
                        <img id="image-preview" src="#" alt="预览图" style="display: none; max-width: 100%; margin-top: 1rem; border-radius: var(--radius);">
                        <input type="file" id="image_file" name="image" accept="image/*" style="display: none;" onchange="previewImage(this);">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">商品名称</label>
                    <input type="text" name="title" class="form-input" placeholder="请输入商品名称" required>
                </div>

                <div class="form-group">
                    <label class="form-label">商品描述</label>
                    <textarea name="description" class="form-input" rows="4" style="resize: vertical;" 
                              placeholder="请详细描述商品的具体情况，例如：使用时长、新旧程度、有无损坏等" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">价格</label>
                    <input type="number" name="price" step="0.01" class="form-input" placeholder="请输入价格" required>
                </div>

                <div class="form-group">
                    <label class="form-label">类别</label>
                    <select name="category" class="form-input" required>
                        <option value="">请选择类别</option>
                        <option value="书籍">书籍</option>
                        <option value="电子产品">电子产品</option>
                        <option value="家具">家具</option>
                        <option value="运动用品">运动用品</option>
                        <option value="生活用品">生活用品</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">商品成色</label>
                    <select name="item_condition" class="form-input" required>
                        <option value="">请选择成色</option>
                        <option value="new">崭新出场</option>
                        <option value="like_new">略有磨损</option>
                        <option value="used">久经沙场</option>
                        <option value="old">破损不堪</option>
                    </select>
                </div>

                <div style="display: grid; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">确认发布</button>
                    <a href="index.php" class="btn btn-secondary">返回主页</a>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function validateForm() {
            var fileInput = document.getElementById('image_file');
            var title = document.querySelector('input[name="title"]').value;
            var price = document.querySelector('input[name="price"]').value;
            var category = document.querySelector('select[name="category"]').value;
            var condition = document.querySelector('select[name="item_condition"]').value;

            if (!fileInput.files[0]) {
                alert('请选择商品图片');
                return false;
            }
            if (!title.trim()) {
                alert('请输入商品名称');
                return false;
            }
            if (!price || price <= 0) {
                alert('请输入有效的价格');
                return false;
            }
            if (!category) {
                alert('请选择商品类别');
                return false;
            }
            if (!condition) {
                alert('请选择商品成色');
                return false;
            }
            return true;
        }

        // 拖拽上传
        var dropZone = document.querySelector('[onclick="document.getElementById(\'image_file\').click()"]');
        
        if (dropZone) {  // 添加检查
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--primary');
            });
            
            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--border');
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--border');
                var file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    document.getElementById('image_file').files = e.dataTransfer.files;
                    previewImage(document.getElementById('image_file'));
                }
            });
        }

        // 确保validateForm在全局作用域可用
        window.validateForm = validateForm;
    });
    </script>
</body>
</html>