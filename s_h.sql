/*
 Navicat Premium Dump SQL

 Source Server         : second_hand
 Source Server Type    : MySQL
 Source Server Version : 100428 (10.4.28-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : 1

 Target Server Type    : MySQL
 Target Server Version : 100428 (10.4.28-MariaDB)
 File Encoding         : 65001

 Date: 27/12/2024 20:08:03
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cart_items
-- ----------------------------
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_item_id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of cart_items
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for inbox_messages
-- ----------------------------
DROP TABLE IF EXISTS `inbox_messages`;
CREATE TABLE `inbox_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `inbox_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `inbox_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `inbox_messages_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of inbox_messages
-- ----------------------------
BEGIN;
INSERT INTO `inbox_messages` (`message_id`, `sender_id`, `receiver_id`, `order_id`, `subject`, `content`, `is_read`, `timestamp`) VALUES (1, 2, 1, 1, '快递已发出', '123', 0, '2024-12-24 15:13:59');
COMMIT;

-- ----------------------------
-- Table structure for items
-- ----------------------------
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `item_condition` enum('new','like_new','used','old') NOT NULL,
  `image_url` varchar(255) DEFAULT 'no_image.png',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `status` enum('available','sold','removed') DEFAULT 'available',
  `is_sold` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of items
-- ----------------------------
BEGIN;
INSERT INTO `items` (`item_id`, `user_id`, `title`, `description`, `price`, `category`, `item_condition`, `image_url`, `created_at`, `updated_at`, `status`, `is_sold`) VALUES (2, 1, '123', '123', 123.00, '书籍', 'new', 'no_image.png', '2024-12-27 19:46:39', NULL, 'available', 0);
COMMIT;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
  `shuttle_trip_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `orders_ibfk_3` (`seller_id`),
  KEY `orders_ibfk_2` (`item_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of orders
-- ----------------------------
BEGIN;
INSERT INTO `orders` (`order_id`, `buyer_id`, `seller_id`, `item_id`, `order_date`, `total_price`, `status`, `shuttle_trip_id`) VALUES (1, 2, 1, NULL, '2024-12-24 15:12:19', 123.00, 'delivered', NULL);
COMMIT;

-- ----------------------------
-- Table structure for ratings
-- ----------------------------
DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `rated_user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL COMMENT '好评为1，差评为-1',
  `rating_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`rating_id`),
  KEY `order_id` (`order_id`),
  KEY `rated_user_id` (`rated_user_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`rated_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of ratings
-- ----------------------------
BEGIN;
INSERT INTO `ratings` (`rating_id`, `order_id`, `rated_user_id`, `rating`, `rating_date`) VALUES (1, 1, 1, -1, '2024-12-24 15:13:45');
INSERT INTO `ratings` (`rating_id`, `order_id`, `rated_user_id`, `rating`, `rating_date`) VALUES (2, 1, 2, -1, '2024-12-24 15:14:18');
COMMIT;

-- ----------------------------
-- Table structure for routes
-- ----------------------------
DROP TABLE IF EXISTS `routes`;
CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name` varchar(100) NOT NULL,
  `departure_campus` enum('卫岗','滨江','浦口') NOT NULL,
  `arrival_campus` enum('卫岗','滨江','浦口') NOT NULL,
  PRIMARY KEY (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of routes
-- ----------------------------
BEGIN;
INSERT INTO `routes` (`route_id`, `route_name`, `departure_campus`, `arrival_campus`) VALUES (1, '卫岗->浦口', '卫岗', '浦口');
INSERT INTO `routes` (`route_id`, `route_name`, `departure_campus`, `arrival_campus`) VALUES (2, '浦口->卫岗', '浦口', '卫岗');
INSERT INTO `routes` (`route_id`, `route_name`, `departure_campus`, `arrival_campus`) VALUES (3, '浦口->滨江', '浦口', '滨江');
INSERT INTO `routes` (`route_id`, `route_name`, `departure_campus`, `arrival_campus`) VALUES (4, '滨江->浦口', '滨江', '浦口');
INSERT INTO `routes` (`route_id`, `route_name`, `departure_campus`, `arrival_campus`) VALUES (5, '卫岗->滨江', '卫岗', '滨江');
COMMIT;

-- ----------------------------
-- Table structure for shuttle_trips
-- ----------------------------
DROP TABLE IF EXISTS `shuttle_trips`;
CREATE TABLE `shuttle_trips` (
  `trip_id` int(11) NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL,
  `departure_campus` varchar(100) NOT NULL,
  `departure_time` time NOT NULL,
  `available_capacity` int(11) DEFAULT 50,
  `status` enum('待出发','已出发','已到达') DEFAULT '待出发',
  `arrive_campus` varchar(100) NOT NULL,
  PRIMARY KEY (`trip_id`),
  KEY `route_id` (`route_id`),
  CONSTRAINT `shuttle_trips_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of shuttle_trips
-- ----------------------------
BEGIN;
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (1, 1, '卫岗', '06:40:00', 50, '待出发', '浦口');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (2, 5, '卫岗', '13:00:00', 50, '待出发', '滨江');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (3, 2, '浦口', '07:40:00', 50, '待出发', '卫岗');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (4, 2, '浦口', '16:30:00', 50, '待出发', '卫岗');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (5, 3, '浦口', '07:00:00', 50, '待出发', '滨江');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (6, 3, '浦口', '13:40:00', 50, '待出发', '滨江');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (7, 3, '浦口', '20:40:00', 50, '待出发', '滨江');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (8, 3, '浦口', '22:40:00', 50, '待出发', '滨江');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (9, 4, '滨江', '12:30:00', 50, '待出发', '浦口');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (10, 4, '滨江', '17:10:00', 50, '待出发', '浦口');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (11, 4, '滨江', '20:00:00', 50, '待出发', '浦口');
INSERT INTO `shuttle_trips` (`trip_id`, `route_id`, `departure_campus`, `departure_time`, `available_capacity`, `status`, `arrive_campus`) VALUES (12, 4, '滨江', '22:00:00', 50, '待出发', '浦口');
COMMIT;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `campus` enum('卫岗','滨江','浦口') NOT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `total_score` int(11) DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
INSERT INTO `users` (`user_id`, `username`, `campus`, `password`, `student_id`, `phone_number`, `created_at`, `updated_at`, `total_score`) VALUES (1, '123', '卫岗', '123', '123', '123', '2024-12-24 14:36:20', '2024-12-24 15:13:45', -1);
INSERT INTO `users` (`user_id`, `username`, `campus`, `password`, `student_id`, `phone_number`, `created_at`, `updated_at`, `total_score`) VALUES (2, 'buyer', '滨江', '123', '1', '123', '2024-12-24 15:12:10', '2024-12-24 15:14:18', -1);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
