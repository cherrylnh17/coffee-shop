SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `image` varchar(150) DEFAULT NULL,
  `category` tinyint unsigned NOT NULL COMMENT '1: Makanan, 2: Minuman',
  `price` int unsigned NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `menu` VALUES (1,'Nasi Goreng Special','https://placehold.co/400x300?text=Nasi+Goreng',1,25000,'Nasi goreng dengan telur mata sapi, ayam suwir, dan kerupuk.','2026-04-16 11:12:15',NULL),(2,'Mie Goreng Jawa','https://placehold.co/400x300?text=Mie+Goreng',1,22000,'Mie goreng bumbu tradisional dengan sayuran segar.','2026-04-16 11:12:15',NULL),(3,'Ayam Geprek Sambal Bawang','https://placehold.co/400x300?text=Ayam+Geprek',1,18000,'Ayam krispi pedas dengan sambal bawang asli.','2026-04-16 11:12:15',NULL),(4,'Sandwich Gandum','https://placehold.co/400x300?text=Sandwich',1,20000,'Roti gandum isi daging asap, keju, dan selada.','2026-04-16 11:12:15',NULL),(5,'Pasta Carbonara','https://placehold.co/400x300?text=Pasta',1,35000,'Pasta creamy dengan saus keju dan topping beef bacon.','2026-04-16 11:12:15',NULL),(6,'Kentang Goreng (Fries)','https://placehold.co/400x300?text=Fries',1,15000,'Kentang goreng renyah dengan bumbu tabur original.','2026-04-16 11:12:15',NULL),(7,'Club Sandwich','https://placehold.co/400x300?text=Club+Sandwich',1,28000,'Roti lapis tiga tingkat dengan isi lengkap.','2026-04-16 11:12:15',NULL),(8,'Sate Ayam Madura','https://placehold.co/400x300?text=Sate+Ayam',1,30000,'10 tusuk sate ayam dengan bumbu kacang kental.','2026-04-16 11:12:15',NULL),(9,'Es Kopi Susu Gula Aren','https://placehold.co/400x300?text=Kopi+Susu',2,18000,'Espresso dicampur susu segar dan gula aren asli.','2026-04-16 11:12:15',NULL),(10,'Iced Cafe Latte','https://placehold.co/400x300?text=Latte',2,22000,'Kopi espresso dengan susu uap yang lembut.','2026-04-16 11:12:15',NULL),(11,'Green Tea Latte','https://placehold.co/400x300?text=Matcha',2,20000,'Bubuk matcha premium dengan susu segar cold/hot.','2026-04-16 11:12:15',NULL),(12,'Lemon Tea Ice','https://placehold.co/400x300?text=Lemon+Tea',2,12000,'Teh segar dengan perasan jeruk lemon asli.','2026-04-16 11:12:15',NULL),(13,'Chocolate Shake','https://placehold.co/400x300?text=Chocolate',2,25000,'Minuman cokelat blend dengan topping whipped cream.','2026-04-16 11:12:15',NULL),(14,'Americano Ice','https://placehold.co/400x300?text=Americano',2,15000,'Double shot espresso dengan air mineral dingin.','2026-04-16 11:12:15',NULL),(15,'Avocado Juice','https://placehold.co/400x300?text=Avocado',2,18000,'Jus alpukat mentega dengan topping cokelat kental.','2026-04-16 11:12:15',NULL),(16,'sd','assets/image/menu/menu_69ed402847a53.jpeg',1,1,'11','2026-04-26 05:28:56',NULL),(17,'sfsdfsfd','assets/image/menu/menu_69f0211355c56.jpg',2,2444,'aaaa','2026-04-28 09:53:07',NULL);
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `user_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `table_id` int unsigned NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `customer_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `qty` int unsigned NOT NULL COMMENT 'Total barang',
  `subtotal` int unsigned NOT NULL,
  `tax` int unsigned NOT NULL,
  `paid` int unsigned DEFAULT NULL,
  `change` int unsigned DEFAULT NULL,
  `total` int unsigned NOT NULL,
  `payment` tinyint NOT NULL COMMENT '1: Kasir, 2: Online',
  `detail` text,
  `status` tinyint unsigned NOT NULL DEFAULT '2' COMMENT '1: success, 2: pending, 3: expired',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `order` VALUES (67,'ORD-tsvdt9Rt',NULL,NULL,2,'7','122','',1,18000,2160,0,0,20160,1,'1 Avocado Juice',1,'2026-05-20 11:15:53',NULL,'2026-05-20 11:25:53'),(72,'ORD-wJhBPDAv',1,'q',1,'1','qal','',4,34888,4187,40000,925,39075,1,'1 Lemon Tea Ice, 1 Es Kopi Susu Gula Aren, 2 sfsdfsfd',1,'2026-05-21 10:57:25',NULL,'2026-05-21 11:07:25'),(73,'ORD-1wCeCCTn',1,'q',1,'1','a','',1,12000,1440,15000,1560,13440,1,'1 Lemon Tea Ice',1,'2026-05-21 12:44:40',NULL,'2026-05-21 12:54:40'),(74,'ORD-nVREaVZD',1,'q',1,'1','aa','',1,12000,1440,1000000000,999986560,13440,1,'1 Lemon Tea Ice',1,'2026-05-21 13:37:19',NULL,'2026-05-21 13:47:19'),(75,'ORD-cgEFKT8f',1,'q',1,'1','sss','',2,40000,4800,50000,5200,44800,1,'1 Mie Goreng Jawa, 1 Avocado Juice',1,'2026-05-22 10:14:04',NULL,'2026-05-22 10:24:04');
DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `menu_id` int unsigned NOT NULL,
  `menu_name` varchar(50) NOT NULL,
  `qty` int unsigned NOT NULL COMMENT 'Total per item',
  `subtotal` int unsigned NOT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order` (`order_id`),
  CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `order_item` VALUES (99,67,15,'Avocado Juice',1,18000,'','2026-05-20 11:15:53',NULL),(109,72,12,'Lemon Tea Ice',1,12000,'','2026-05-21 10:57:25',NULL),(110,72,9,'Es Kopi Susu Gula Aren',1,18000,'','2026-05-21 10:57:25',NULL),(111,72,17,'sfsdfsfd',2,4888,'','2026-05-21 10:57:25',NULL),(112,73,12,'Lemon Tea Ice',1,12000,'cihuy','2026-05-21 12:44:40',NULL),(113,74,12,'Lemon Tea Ice',1,12000,'','2026-05-21 13:37:19',NULL),(114,75,2,'Mie Goreng Jawa',1,22000,'','2026-05-22 10:14:04',NULL),(115,75,15,'Avocado Juice',1,18000,'','2026-05-22 10:14:04',NULL);
DROP TABLE IF EXISTS `printer`;
CREATE TABLE `printer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` tinyint unsigned NOT NULL COMMENT '1=Bluetooth,2=Network,3=USB',
  `bt_mac` varchar(50) DEFAULT NULL,
  `bt_channel` int unsigned DEFAULT NULL,
  `rfcomm_dev` varchar(50) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `port` int unsigned DEFAULT NULL,
  `usb_device` varchar(100) DEFAULT NULL,
  `timeout` int DEFAULT '5',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `printer` VALUES (1,'Kasir',1,'DC:0D:51:78:D7:83',1,'/dev/rfcomm0',NULL,NULL,NULL,5,1,NULL,NULL);
DROP TABLE IF EXISTS `table`;
CREATE TABLE `table` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `table` VALUES (1,'1','2026-04-21 10:20:20',NULL),(2,'7','2026-04-28 09:52:47',NULL),(3,'A1','2026-05-21 08:42:42',NULL),(4,'2','2026-05-21 10:37:11',NULL);
DROP TABLE IF EXISTS `tax`;
CREATE TABLE `tax` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `amount` decimal(10,0) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `tax` VALUES (1,'pajak ppn',12,'2026-05-20 06:02:30'),(4,'asuransi',8,'2026-05-22 10:34:01');
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` tinyint unsigned NOT NULL COMMENT '1: Kasir, 2: Admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user` VALUES (1,'q','q','q',1,'2026-04-15 12:17:34',NULL),(2,'admin','admin','admin',2,'2026-04-16 12:13:38',NULL),(3,'a','a','1',1,'2026-05-21 10:37:06',NULL);


SET foreign_key_checks = 1;
