-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 20, 2026 at 05:36 AM
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
(15, 'Avocado Juice', 'https://placehold.co/400x300?text=Avocado', 2, 18000, 'Jus alpukat mentega dengan topping cokelat kental.', '2026-04-16 11:12:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `table_id` int UNSIGNED NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `customer_name` varchar(50) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `qty` int UNSIGNED NOT NULL COMMENT 'Total barang',
  `subtotal` int UNSIGNED NOT NULL,
  `tax` int UNSIGNED NOT NULL,
  `total` int UNSIGNED NOT NULL,
  `payment` tinyint NOT NULL COMMENT '1: Kasir, 2: Online',
  `detail` text,
  `status` tinyint UNSIGNED NOT NULL DEFAULT '2' COMMENT '1: success, 2: pending, 3: expired',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `table`
--

CREATE TABLE `table` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

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
(1, 'rtyry', 'rtr', 'tyry', 1, '2026-04-15 12:17:34', NULL),
(2, 'admin', 'admin', 'admin', 2, '2026-04-16 12:13:38', NULL);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order` (`order_id`);

--
-- Indexes for table `table`
--
ALTER TABLE `table`
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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `table`
--
ALTER TABLE `table`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
CREATE DEFINER=`admin`@`localhost` EVENT `set_status_expired` ON SCHEDULE EVERY 5 MINUTE STARTS '2026-04-20 12:35:20' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE `order` 
   SET status = 3 
   WHERE status = 2 
   AND expired_at < NOW()$$

CREATE DEFINER=`admin`@`localhost` EVENT `delete_status_expired` ON SCHEDULE EVERY 1 DAY STARTS '2026-04-20 12:36:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `order` 
   WHERE status = 3$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
