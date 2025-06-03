-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Июн 03 2025 г., 22:38
-- Версия сервера: 8.0.25-15
-- Версия PHP: 8.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u3145574_default`
--

-- --------------------------------------------------------

--
-- Структура таблицы `administrators`
--

CREATE TABLE `administrators` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `administrators`
--

INSERT INTO `administrators` (`id`, `username`, `password`, `email`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'u3145574_devilpurrp', '$2y$10$RpjzUTZLXLrvUg6QP2s81OHuTsUo2Di0YnxxtPru9o3le0DtKk81C', 'admin@sz-novostroi-yug.ru', 1, '2025-06-03 00:33:06', '2025-06-01 20:01:33', '2025-06-02 21:33:06');

-- --------------------------------------------------------

--
-- Структура таблицы `apartment_images`
--

CREATE TABLE `apartment_images` (
  `id` int NOT NULL,
  `apartment_type_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_order` int DEFAULT '0',
  `is_main` tinyint(1) DEFAULT '0',
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `apartment_images`
--

INSERT INTO `apartment_images` (`id`, `apartment_type_id`, `image_path`, `image_order`, `is_main`, `alt_text`, `created_at`) VALUES
(1, 1, '/images/apartments/1k/plan1.jpg', 1, 1, 'Планировка 1-комнатной квартиры', '2025-06-01 20:01:33'),
(2, 2, '/images/apartments/2k/plan1.jpg', 1, 1, 'Планировка 2-комнатной квартиры', '2025-06-01 20:01:33');

-- --------------------------------------------------------

--
-- Структура таблицы `apartment_types`
--

CREATE TABLE `apartment_types` (
  `id` int NOT NULL,
  `complex_id` int NOT NULL,
  `rooms_count` int NOT NULL,
  `total_count` int NOT NULL,
  `min_area` decimal(10,2) DEFAULT NULL,
  `max_area` decimal(10,2) DEFAULT NULL,
  `min_price` decimal(15,2) DEFAULT NULL,
  `max_price` decimal(15,2) DEFAULT NULL,
  `min_living_area` decimal(10,2) DEFAULT NULL,
  `max_living_area` decimal(10,2) DEFAULT NULL,
  `has_balcony` tinyint(1) DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `apartment_types`
--

INSERT INTO `apartment_types` (`id`, `complex_id`, `rooms_count`, `total_count`, `min_area`, `max_area`, `min_price`, `max_price`, `min_living_area`, `max_living_area`, `has_balcony`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 77, 36.71, 56.11, 4405000.00, 6733000.00, 11.57, 15.13, 1, 'Просторные однокомнатные квартиры с удобной планировкой', '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(2, 1, 2, 28, 53.79, 80.72, 6454000.00, 9686000.00, 24.19, 33.88, 1, 'Светлые двухкомнатные квартиры с большой кухней', '2025-06-01 20:01:33', '2025-06-01 20:01:33');

-- --------------------------------------------------------

--
-- Структура таблицы `complex_features`
--

CREATE TABLE `complex_features` (
  `id` int NOT NULL,
  `complex_id` int NOT NULL,
  `feature_category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_value` text COLLATE utf8mb4_unicode_ci,
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `complex_features`
--

INSERT INTO `complex_features` (`id`, `complex_id`, `feature_category`, `feature_name`, `feature_value`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Благоустройство двора', 'Велосипедные дорожки', 'Нет', 1, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(2, 1, 'Благоустройство двора', 'Количество детских площадок', '1', 2, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(3, 1, 'Благоустройство двора', 'Количество спортивных площадок', '2', 3, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(4, 1, 'Благоустройство двора', 'Количество площадок для сбора мусора', '1', 4, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(5, 1, 'Парковочное пространство', 'Количество мест в паркинге', '47', 1, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(6, 1, 'Парковочное пространство', 'Гостевые места на придомовой территории', '5', 2, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(7, 1, 'Парковочное пространство', 'Гостевые места вне придомовой территории', 'Нет', 3, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(8, 1, 'Безбарьерная среда', 'Наличие пандуса', 'Есть', 1, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(9, 1, 'Безбарьерная среда', 'Наличие понижающих площадок', 'Есть', 2, '2025-06-01 20:01:33', '2025-06-01 20:01:33'),
(10, 1, 'Безбарьерная среда', 'Количество инвалидных подъемников', 'Нет', 3, '2025-06-01 20:01:33', '2025-06-01 20:01:33');

-- --------------------------------------------------------

--
-- Структура таблицы `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `complex_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `success`, `attempt_time`) VALUES
(1, 'u3145574_devilpurrp', '95.25.64.5', 0, '2025-06-01 21:05:54'),
(2, 'u3145574_devilpurrp', '95.25.64.5', 0, '2025-06-01 21:20:42'),
(3, 'u3145574_devilpurrp', '95.25.64.5', 1, '2025-06-01 21:23:04'),
(4, 'u3145574_devilpurrp', '95.25.64.5', 1, '2025-06-02 21:33:06');

-- --------------------------------------------------------

--
-- Структура таблицы `residential_complexes`
--

CREATE TABLE `residential_complexes` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `class` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wall_material` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `finishing_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `free_layout` tinyint(1) DEFAULT '0',
  `floors_count` int NOT NULL,
  `apartments_count` int NOT NULL,
  `living_area` decimal(10,2) DEFAULT NULL,
  `ceiling_height` decimal(3,2) DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_from` decimal(15,2) DEFAULT NULL,
  `price_to` decimal(15,2) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `layout_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `residential_complexes`
--

INSERT INTO `residential_complexes` (`id`, `name`, `description`, `class`, `wall_material`, `finishing_type`, `free_layout`, `floors_count`, `apartments_count`, `living_area`, `ceiling_height`, `address`, `phone`, `email`, `price_from`, `price_to`, `completion_date`, `status`, `main_image`, `layout_image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ПРОСТОР', 'Жилой комплекс \"ПРОСТОР\" - современный комфортабельный комплекс в Ростове-на-Дону', 'Комфорт', 'Кирпич', 'Предчистовая', 0, 9, 105, 5375.00, 2.70, '344020, г.Ростов-на-Дону, ул.Курчатова, 1Д', '+7 (863) 333-26-26', 'noreply@sz-novostroi-yug.ru', 4405000.00, 9686000.00, NULL, 'Строительство', '/images/main.jpg', '/images/layout.jpg', 1, '2025-06-01 20:01:33', '2025-06-02 21:33:37');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `apartment_images`
--
ALTER TABLE `apartment_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apartment_type_id` (`apartment_type_id`);

--
-- Индексы таблицы `apartment_types`
--
ALTER TABLE `apartment_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_apartment_types_complex_id` (`complex_id`);

--
-- Индексы таблицы `complex_features`
--
ALTER TABLE `complex_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complex_features_complex_id` (`complex_id`);

--
-- Индексы таблицы `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_complex_id` (`complex_id`);

--
-- Индексы таблицы `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_username` (`username`,`attempt_time`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`,`attempt_time`);

--
-- Индексы таблицы `residential_complexes`
--
ALTER TABLE `residential_complexes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `apartment_images`
--
ALTER TABLE `apartment_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `apartment_types`
--
ALTER TABLE `apartment_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `complex_features`
--
ALTER TABLE `complex_features`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `residential_complexes`
--
ALTER TABLE `residential_complexes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `apartment_images`
--
ALTER TABLE `apartment_images`
  ADD CONSTRAINT `apartment_images_ibfk_1` FOREIGN KEY (`apartment_type_id`) REFERENCES `apartment_types` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `apartment_types`
--
ALTER TABLE `apartment_types`
  ADD CONSTRAINT `apartment_types_ibfk_1` FOREIGN KEY (`complex_id`) REFERENCES `residential_complexes` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `complex_features`
--
ALTER TABLE `complex_features`
  ADD CONSTRAINT `complex_features_ibfk_1` FOREIGN KEY (`complex_id`) REFERENCES `residential_complexes` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`complex_id`) REFERENCES `residential_complexes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
