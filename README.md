安装指南 - 小农二手交易系统

**1. 项目概述**

小农二手交易系统是一个面向校园的二手交易平台，用户可以发布、购买商品并管理个人信息。系统支持商品的类别筛选、图片上传、订单管理、评论与留言功能。

**2. 系统要求**

• 操作系统：Linux / macOS / Windows（推荐使用 Linux 或 macOS 作为开发环境）

• Web 服务器：Apache / Nginx

• 数据库：MySQL 5.6 或更高版本

• PHP 版本：7.4 或更高版本

• 前端：HTML, CSS, JavaScript (Vue.js 或类似框架)

• 扩展：PHP 扩展 mysqli, gd（用于图片上传）

3. **环境搭建**

3.1 安装 PHP 和 MySQL

·在 Linux（Ubuntu/Debian）系统上：

sudo apt update

sudo apt install apache2 php php-mysqli php-gd mysql-server

·在 macOS 上，推荐使用 Homebrew 来安装：

brew install php

brew install mysql

3.2 安装 Composer（PHP 包管理器）

如果系统中尚未安装 Composer，可以执行以下命令：

curl -sS https://getcomposer.org/installer | php

将 Composer 移动到系统路径：

sudo mv composer.phar /usr/local/bin/composer

3. **获取项目文件**

将项目的所有文件从 

GitHub仓库：<https://github.com/linqichenggg/second_hand_njau.git>

下载到本地

3. **配置数据库**

5.1 创建数据库

登录到 MySQL 并创建一个数据库：

mysql -u root -p

CREATE DATABASE second\_hand\_njau;

5.2 导入数据库结构

在项目根目录下，你应该有一个名为 s\_h.sql 的文件，里面包含所有表的创建语句。使用以下命令导入数据库结构：

mysql -u root -p second\_hand\_njau < s\_h.sql

5.3 配置数据库连接

在 db\_connect.php 文件中，修改数据库连接的配置：

$servername = "localhost";  // 数据库主机

$username = "root";         // 数据库用户名

$password = "";             // 数据库密码

$dbname = "second\_hand\_njau"; // 数据库名

// 创建连接

$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接

if ($conn->connect\_error) {

`    `die("连接失败: " . $conn->connect\_error);

}

3. **配置 Web 服务器**

编辑 Apache 配置文件以支持你的项目。

假设项目存放在 /var/www/html/second\_hand\_njau 目录下：

sudo nano /etc/apache2/sites-available/second\_hand\_njau.conf

在文件中添加以下内容：

<VirtualHost \*:80>

`    `ServerAdmin webmaster@localhost

`    `DocumentRoot /var/www/html/second\_hand\_njau

`    `ServerName localhost

`    `<Directory /var/www/html/second\_hand\_njau>

`        `AllowOverride All

`        `Require all granted

`    `</Directory>

`    `ErrorLog ${APACHE\_LOG\_DIR}/error.log

`    `CustomLog ${APACHE\_LOG\_DIR}/access.log combined

</VirtualHost>

启用配置并重启 Apache：

sudo a2ensite second\_hand\_njau.conf

sudo systemctl restart apache2

3. **配置文件权限**

确保 uploads/ 目录具有适当的写入权限，以便文件上传可以正常工作：

chmod -R 777 uploads/

3. **测试和调试**

\1. 打开浏览器并访问项目。默认情况下，应该在 localhost 上可以访问。

\2. 测试注册、登录、商品发布等功能。

\3. 如果出现任何错误，请查看 Apache 或 Nginx 错误日志：

• Apache：/var/log/apache2/error.log

