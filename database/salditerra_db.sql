-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 25-Fev-2026 às 13:33
-- Versão do servidor: 8.0.17
-- versão do PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `salditerra_db`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `birth_date` date NOT NULL,
  `address` varchar(255) NOT NULL,
  `status` enum('pending','completed','','') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `tbl_orders`
--

INSERT INTO `tbl_orders` (`id`, `user_id`, `birth_date`, `address`, `status`) VALUES
(1, 1, '1994-01-20', 'Rua Cristovão de Castro, nº46', 'completed'),
(2, 1, '1994-01-20', 'Rua Cristovão de Castro, nº46', 'pending'),
(3, 1, '1994-01-20', 'Praceta Jaime Cortesão', 'pending');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_order_items`
--

CREATE TABLE `tbl_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `tbl_order_items`
--

INSERT INTO `tbl_order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 2),
(2, 1, 2, 2),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 2, 1, 2),
(6, 3, 1, 2),
(7, 3, 2, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_products`
--

CREATE TABLE `tbl_products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `tbl_products`
--

INSERT INTO `tbl_products` (`id`, `name`, `description`, `price`, `stock`, `image`) VALUES
(1, 'Rich Cachupa', 'Corn, beans, sweet potato, cassava, vegetables, assorted meats and sausages, fresh herbs, and optional salted fish.', '24.99', 4, 'uploads/img_1771517739_467134dc.png'),
(2, 'Grilled Lobster', 'Fresh lobster, garlic, and butter, served with smoked sweet potato purée, seasonal vegetables, and fresh herbs.', '44.99', 7, 'uploads/img_1771517772_364bfa85.png'),
(3, 'Tuna & Mango', 'Seared fresh tuna with mango sauce, microgreens, crunchy seeds, and optional balsamic reduction.', '34.99', 9, 'uploads/img_1771517821_3eee6a45.png'),
(4, 'Fish Broth', 'Seafood broth with fresh fish, shrimp, vegetables, herbs, and optional rice.', '28.99', 9, 'uploads/img_1771517856_c9049e9e.png'),
(5, 'Papaya & Cheese', 'Ripe papaya with fresh cheese, cream, brown sugar, citrus zest, and mint garnish.', '10.99', 10, 'uploads/img_1771517886_ba4265a8.png'),
(6, 'Cow\'s Sarabudja', 'Selected cow\'s foot, white beans, corn, sweet potato, vegetables, fresh herbs, and a touch of white wine.', '30.99', 0, 'uploads/img_1771517943_0ffdafc1.png');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_reservation`
--

CREATE TABLE `tbl_reservation` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `guest` int(11) NOT NULL,
  `date` date NOT NULL,
  `hour` time NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `tbl_reservation`
--

INSERT INTO `tbl_reservation` (`id`, `name`, `phone`, `guest`, `date`, `hour`, `message`) VALUES
(1, 'Elvio Patrick', '912814170', 2, '2026-02-20', '20:00:00', 'If possible, I would really appreciate sitting near the window. Thank you!');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('user','admin') NOT NULL DEFAULT 'user',
  `profile_pic` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `username`, `email`, `password_hash`, `user_type`, `profile_pic`) VALUES
(1, 'Elvio Patrick', 'elviopatrick.pt@gmail.com', '$2y$10$SBjRJsiOJ7z4SkhCWmqPYOcbQpTaAuyl5fDZtUINeabnVcwysviw.', 'admin', 'uploads/img_6995e048a7b60.png'),
(2, 'Elvio Lopes', 'elviopatrick.dev@gmail.com', '$2y$10$tj3S2YVCRyyKO6qNmxHDYuSSBiSu3PwUvGSXmUGh2KZy/rCWH/4Ze', 'user', 'uploads/1771513411_yupp-generated-image-501846.png');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_orders_ibfk_1` (`user_id`);

--
-- Índices para tabela `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_order_items_ibfk_1` (`order_id`),
  ADD KEY `tbl_order_items_ibfk_2` (`product_id`);

--
-- Índices para tabela `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `tbl_reservation`
--
ALTER TABLE `tbl_reservation`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `tbl_reservation`
--
ALTER TABLE `tbl_reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD CONSTRAINT `tbl_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limitadores para a tabela `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD CONSTRAINT `tbl_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tbl_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
