-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 22, 2026 at 08:14 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(150) DEFAULT NULL,
  `category` tinyint UNSIGNED NOT NULL COMMENT '1: Makanan, 2: Minuman',
  `price` int UNSIGNED NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `image`, `category`, `price`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Nasi Goreng Special', 'https://placehold.co/400x300?text=Nasi+Goreng', 1, 25000, 'Nasi goreng dengan telur mata sapi, ayam suwir, dan kerupuk.', '2026-04-16 11:12:15', NULL),
(2, 'Mie Goreng Jawa', 'https://placehold.co/400x300?text=Mie+Goreng', 1, 22000, 'Mie goreng bumbu tradisional dengan sayuran segar.', '2026-04-16 11:12:15', NULL),
(3, 'Ayam Geprek Sambal Bawang', 'https://placehold.co/400x300?text=Ayam+Geprek', 1, 18000, 'Ayam krispi pedas dengan sambal bawang asli.', '2026-04-16 11:12:15', NULL),
(4, 'Sandwich Gandum', 'https://placehold.co/400x300?text=Sandwich', 1, 20000, 'Roti gandum isi daging asap, keju, dan selada.', '2026-04-16 11:12:15', NULL),
(5, 'Pasta Carbonara', 'https://placehold.co/400x300?text=Pasta', 1, 35000, 'Pasta creamy dengan saus keju dan topping beef bacon.', '2026-04-16 11:12:15', NULL),
(6, 'Kentang Goreng (Fries)', 'https://placehold.co/400x300?text=Fries', 1, 15000, 'Kentang goreng renyah dengan bumbu tabur original.', '2026-04-16 11:12:15', NULL),
(7, 'Club Sandwich', 'https://placehold.co/400x300?text=Club+Sandwich', 1, 28000, 'Roti lapis tiga tingkat dengan isi lengkap.', '2026-04-16 11:12:15', NULL),
(8, 'Sate Ayam Madura', 'https://placehold.co/400x300?text=Sate+Ayam', 1, 30000, '10 tusuk sate ayam dengan bumbu kacang kental.', '2026-04-16 11:12:15', NULL),
(9, 'Es Kopi Susu Gula Aren', 'https://placehold.co/400x300?text=Kopi+Susu', 2, 18000, 'Espresso dicampur susu segar dan gula aren asli.', '2026-04-16 11:12:15', NULL),
(10, 'Iced Cafe Latte', 'https://placehold.co/400x300?text=Latte', 2, 22000, 'Kopi espresso dengan susu uap yang lembut.', '2026-04-16 11:12:15', NULL),
(11, 'Green Tea Latte', 'https://placehold.co/400x300?text=Matcha', 2, 20000, 'Bubuk matcha premium dengan susu segar cold/hot.', '2026-04-16 11:12:15', NULL),
(12, 'Lemon Tea Ice', 'https://placehold.co/400x300?text=Lemon+Tea', 2, 12000, 'Teh segar dengan perasan jeruk lemon asli.', '2026-04-16 11:12:15', NULL),
(13, 'Chocolate Shake', 'https://placehold.co/400x300?text=Chocolate', 2, 25000, 'Minuman cokelat blend dengan topping whipped cream.', '2026-04-16 11:12:15', NULL),
(14, 'Americano Ice', 'https://placehold.co/400x300?text=Americano', 2, 15000, 'Double shot espresso dengan air mineral dingin.', '2026-04-16 11:12:15', NULL),
(15, 'Avocado Juice', 'https://placehold.co/400x300?text=Avocado', 2, 18000, 'Jus alpukat mentega dengan topping cokelat kental.', '2026-04-16 11:12:15', NULL),
(16, 'sd', 'assets/image/menu/menu_69ed402847a53.jpeg', 1, 1, '11', '2026-04-26 05:28:56', NULL),
(17, 'sfsdfsfd', 'assets/image/menu/menu_69f0211355c56.jpg', 2, 2444, 'aaaa', '2026-04-28 09:53:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `user_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `table_id` int UNSIGNED NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `customer_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `qty` int UNSIGNED NOT NULL COMMENT 'Total barang',
  `subtotal` int UNSIGNED NOT NULL,
  `tax` int UNSIGNED NOT NULL,
  `paid` int UNSIGNED DEFAULT NULL,
  `change` int UNSIGNED DEFAULT NULL,
  `total` int UNSIGNED NOT NULL,
  `payment` tinyint NOT NULL COMMENT '1: Kasir, 2: Online',
  `detail` text,
  `status` tinyint UNSIGNED NOT NULL DEFAULT '2' COMMENT '1: success, 2: pending, 3: expired',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `code`, `user_id`, `user_name`, `table_id`, `table_name`, `customer_name`, `customer_email`, `qty`, `subtotal`, `tax`, `paid`, `change`, `total`, `payment`, `detail`, `status`, `created_at`, `updated_at`, `expired_at`) VALUES
(67, 'ORD-tsvdt9Rt', NULL, NULL, 2, '7', '122', '', 1, 18000, 2160, 0, 0, 20160, 1, '1 Avocado Juice', 1, '2026-05-20 11:15:53', NULL, '2026-05-20 11:25:53'),
(72, 'ORD-wJhBPDAv', 1, 'q', 1, '1', 'qal', '', 4, 34888, 4187, 40000, 925, 39075, 1, '1 Lemon Tea Ice, 1 Es Kopi Susu Gula Aren, 2 sfsdfsfd', 1, '2026-05-21 10:57:25', NULL, '2026-05-21 11:07:25'),
(73, 'ORD-1wCeCCTn', 1, 'q', 1, '1', 'a', '', 1, 12000, 1440, 15000, 1560, 13440, 1, '1 Lemon Tea Ice', 1, '2026-05-21 12:44:40', NULL, '2026-05-21 12:54:40'),
(74, 'ORD-nVREaVZD', 1, 'q', 1, '1', 'aa', '', 1, 12000, 1440, 1000000000, 999986560, 13440, 1, '1 Lemon Tea Ice', 1, '2026-05-21 13:37:19', NULL, '2026-05-21 13:47:19'),
(75, 'ORD-cgEFKT8f', 1, 'q', 1, '1', 'sss', '', 2, 40000, 4800, 50000, 5200, 44800, 1, '1 Mie Goreng Jawa, 1 Avocado Juice', 1, '2026-05-22 10:14:04', NULL, '2026-05-22 10:24:04');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
  `id` int UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `menu_id` int UNSIGNED NOT NULL,
  `menu_name` varchar(50) NOT NULL,
  `qty` int UNSIGNED NOT NULL COMMENT 'Total per item',
  `subtotal` int UNSIGNED NOT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`id`, `order_id`, `menu_id`, `menu_name`, `qty`, `subtotal`, `notes`, `created_at`, `updated_at`) VALUES
(99, 67, 15, 'Avocado Juice', 1, 18000, '', '2026-05-20 11:15:53', NULL),
(109, 72, 12, 'Lemon Tea Ice', 1, 12000, '', '2026-05-21 10:57:25', NULL),
(110, 72, 9, 'Es Kopi Susu Gula Aren', 1, 18000, '', '2026-05-21 10:57:25', NULL),
(111, 72, 17, 'sfsdfsfd', 2, 4888, '', '2026-05-21 10:57:25', NULL),
(112, 73, 12, 'Lemon Tea Ice', 1, 12000, 'cihuy', '2026-05-21 12:44:40', NULL),
(113, 74, 12, 'Lemon Tea Ice', 1, 12000, '', '2026-05-21 13:37:19', NULL),
(114, 75, 2, 'Mie Goreng Jawa', 1, 22000, '', '2026-05-22 10:14:04', NULL),
(115, 75, 15, 'Avocado Juice', 1, 18000, '', '2026-05-22 10:14:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `printer`
--

DROP TABLE IF EXISTS `printer`;
CREATE TABLE `printer` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` tinyint UNSIGNED NOT NULL COMMENT '1=Bluetooth,2=Network,3=USB',
  `bt_mac` varchar(50) DEFAULT NULL,
  `bt_channel` int UNSIGNED DEFAULT NULL,
  `rfcomm_dev` varchar(50) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `port` int UNSIGNED DEFAULT NULL,
  `usb_device` varchar(100) DEFAULT NULL,
  `timeout` int DEFAULT '5',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `printer`
--

INSERT INTO `printer` (`id`, `name`, `type`, `bt_mac`, `bt_channel`, `rfcomm_dev`, `ip_address`, `port`, `usb_device`, `timeout`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Kasir', 1, 'DC:0D:51:78:D7:83', 1, '/dev/rfcomm0', NULL, NULL, NULL, 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `table`
--

DROP TABLE IF EXISTS `table`;
CREATE TABLE `table` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `table`
--

INSERT INTO `table` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, '1', '2026-04-21 10:20:20', NULL),
(2, '7', '2026-04-28 09:52:47', NULL),
(3, 'A1', '2026-05-21 08:42:42', NULL),
(4, '2', '2026-05-21 10:37:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax`
--

DROP TABLE IF EXISTS `tax`;
CREATE TABLE `tax` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `amount` decimal(10,0) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tax`
--

INSERT INTO `tax` (`id`, `name`, `amount`, `created_at`) VALUES
(1, 'pajak ppn', 12, '2026-05-20 06:02:30'),
(4, 'asuransi', 8, '2026-05-22 10:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` tinyint UNSIGNED NOT NULL COMMENT '1: Kasir, 2: Admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'q', 'q', 'q', 1, '2026-04-15 12:17:34', NULL),
(2, 'admin', 'admin', 'admin', 2, '2026-04-16 12:13:38', NULL),
(3, 'a', 'a', '1', 1, '2026-05-21 10:37:06', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order` (`order_id`);

--
-- Indexes for table `printer`
--
ALTER TABLE `printer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table`
--
ALTER TABLE `table`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tax`
--
ALTER TABLE `tax`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `printer`
--
ALTER TABLE `printer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `table`
--
ALTER TABLE `table`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tax`
--
ALTER TABLE `tax`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
DROP EVENT IF EXISTS `delete_status_expired`$$
CREATE DEFINER=`admin`@`localhost` EVENT `delete_status_expired` ON SCHEDULE EVERY 1 HOUR STARTS '2026-04-20 12:36:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `order` 
   WHERE status = 3$$

DROP EVENT IF EXISTS `set_status_expired`$$
CREATE DEFINER=`admin`@`localhost` EVENT `set_status_expired` ON SCHEDULE EVERY 1 SECOND STARTS '2026-04-20 12:35:20' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE `order` 
   SET status = 3 
   WHERE status = 2 
   AND expired_at < NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
