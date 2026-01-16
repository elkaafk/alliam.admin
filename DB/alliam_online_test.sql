-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Янв 16 2026 г., 14:31
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `alliam_online_test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'Тип сущности (client, payment, etc)',
  `entity_id` int(11) DEFAULT NULL COMMENT 'ID сущности',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 10:59:54'),
(2, 1, 'logout', 'system', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 11:32:44'),
(3, 2, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 11:33:27'),
(4, 2, 'logout', 'system', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:00:10'),
(5, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:00:33'),
(6, 1, 'logout', 'system', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:36:57'),
(7, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:40:26'),
(8, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:43:23'),
(9, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:44:18'),
(10, 2, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:55:09'),
(11, 2, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 12:55:45'),
(12, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 13:01:03'),
(13, 2, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 13:01:28'),
(14, 1, 'login', 'system', NULL, '{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 13:04:11');

-- --------------------------------------------------------

--
-- Структура таблицы `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `message_type` enum('text','file','system') DEFAULT 'text',
  `content` text NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON с файлами' CHECK (json_valid(`attachments`)),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('superadmin','lawyer','manager') DEFAULT 'manager',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Дополнительные права в JSON' CHECK (json_valid(`permissions`)),
  `max_clients` int(11) DEFAULT 50 COMMENT 'Макс. кол-во клиентов',
  `is_active` tinyint(1) DEFAULT 1,
  `last_active` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'Кем создан',
  `remember_token` varchar(255) DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `avatar`, `role`, `permissions`, `max_clients`, `is_active`, `last_active`, `created_at`, `updated_at`, `created_by`, `remember_token`, `failed_attempts`, `locked_until`) VALUES
(1, 'superadmin', 'admin@alliam.online', '$2y$10$EHM5E4dUwDQ6.8h/jDC7HetgOhgOgds7WWR6FnpfhCf38jjypNsQy', 'Администратор Системы', NULL, NULL, 'superadmin', NULL, 50, 1, '2026-01-16 13:04:11', '2026-01-16 09:58:54', '2026-01-16 13:04:11', NULL, NULL, 0, NULL),
(2, 'lawyer1', 'lawyer@alliam.online', '$2y$10$EHM5E4dUwDQ6.8h/jDC7HetgOhgOgds7WWR6FnpfhCf38jjypNsQy', 'Иванов Юрист Иванович', NULL, NULL, 'lawyer', NULL, 50, 1, '2026-01-16 13:04:09', '2026-01-16 09:58:54', '2026-01-16 13:04:09', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `bankruptcy_stages`
--

CREATE TABLE `bankruptcy_stages` (
  `id` int(11) NOT NULL,
  `stage_key` varchar(50) NOT NULL COMMENT 'Ключ этапа (например, consultation)',
  `stage_name` varchar(100) NOT NULL COMMENT 'Название этапа на русском',
  `stage_description` text DEFAULT NULL COMMENT 'Описание этапа',
  `stage_order` int(11) NOT NULL COMMENT 'Порядковый номер этапа',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Активен ли этап',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `bankruptcy_stages`
--

INSERT INTO `bankruptcy_stages` (`id`, `stage_key`, `stage_name`, `stage_description`, `stage_order`, `is_active`, `created_at`) VALUES
(1, 'initial_consultation', 'Первичная консультация', 'Первичная консультация с юристом, оценка ситуации', 1, 1, '2026-01-14 10:35:57'),
(2, 'questionnaire_filling', 'Заполнение анкеты', 'Заполнение анкеты клиента и сбор первичных данных', 2, 1, '2026-01-14 10:35:57'),
(3, 'document_preparation', 'Подготовка комплекта документов', 'Сбор и оформление необходимых документов', 3, 1, '2026-01-14 10:35:57'),
(4, 'court_submission', 'Подача в суд', 'Подача заявления и документов в суд', 4, 1, '2026-01-14 10:35:57'),
(5, 'court_decision', 'Вынесение решения', 'Рассмотрение дела в суде и вынесение решения', 5, 1, '2026-01-14 10:35:57'),
(6, 'property_implementation', 'Реализация имущества', 'Оценка и реализация имущества должника (если требуется)', 6, 1, '2026-01-14 10:35:57'),
(7, 'restructuring', 'Реструктуризация', 'Разработка и реализация плана реструктуризации долгов', 7, 1, '2026-01-14 10:35:57'),
(8, 'procedure_completion', 'Завершение процедуры', 'Завершение процедуры банкротства', 8, 1, '2026-01-14 10:35:57');

-- --------------------------------------------------------

--
-- Структура таблицы `client_assignments`
--

CREATE TABLE `client_assignments` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL COMMENT 'ID из таблицы users',
  `admin_id` int(11) NOT NULL COMMENT 'ID ответственного',
  `assignment_type` enum('primary','secondary','temporary') DEFAULT 'primary',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `previous_last_names` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `snils` varchar(14) DEFAULT NULL,
  `inn` varchar(12) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `document_issue_date` date DEFAULT NULL,
  `address_index` varchar(10) DEFAULT NULL,
  `address_region` varchar(100) DEFAULT NULL,
  `address_district` varchar(100) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_settlement` varchar(100) DEFAULT NULL,
  `address_street` varchar(100) DEFAULT NULL,
  `address_house` varchar(20) DEFAULT NULL,
  `address_building` varchar(20) DEFAULT NULL,
  `address_apartment` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `last_name`, `first_name`, `middle_name`, `previous_last_names`, `birth_date`, `birth_place`, `snils`, `inn`, `document_type`, `document_number`, `document_issue_date`, `address_index`, `address_region`, `address_district`, `address_city`, `address_settlement`, `address_street`, `address_house`, `address_building`, `address_apartment`, `role`, `phone`, `created_at`, `last_login`, `is_active`, `remember_token`, `token_expires`) VALUES
(10, 'admin', 'admin@alliam.online', '$2y$10$VGT1h.x60P0tScpWP5l/nuuE0ZgSgLc59gmUVBnKfILc1Rp5ymOuq', 'Администратор Профессионал Онлайн', 'Онлайн', 'Администратор', 'Профессионал', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', '79999999999', '2026-01-12 12:27:19', NULL, 1, NULL, NULL),
(11, 'user1', 'user1@alliam.online', '$2y$10$VGT1h.x60P0tScpWP5l/nuuE0ZgSgLc59gmUVBnKfILc1Rp5ymOuq', 'Иванов Иван Иванович', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', '79123456789', '2026-01-12 12:27:19', NULL, 1, NULL, NULL),
(12, 'user2', 'user2@alliam.online', '$2y$10$VGT1h.x60P0tScpWP5l/nuuE0ZgSgLc59gmUVBnKfILc1Rp5ymOuq', 'Петров Петр Петрович', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', '79234567890', '2026-01-12 12:27:19', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_admin` (`admin_id`),
  ADD KEY `idx_activity_action` (`action`),
  ADD KEY `idx_activity_created` (`created_at`);

--
-- Индексы таблицы `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_client` (`client_id`),
  ADD KEY `idx_messages_admin` (`admin_id`),
  ADD KEY `idx_messages_created` (`created_at`);

--
-- Индексы таблицы `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_admin_role` (`role`),
  ADD KEY `idx_admin_active` (`is_active`);

--
-- Индексы таблицы `bankruptcy_stages`
--
ALTER TABLE `bankruptcy_stages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stage_key` (`stage_key`);

--
-- Индексы таблицы `client_assignments`
--
ALTER TABLE `client_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`client_id`,`admin_id`,`assignment_type`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_client_assignments_client` (`client_id`),
  ADD KEY `idx_client_assignments_admin` (`admin_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `bankruptcy_stages`
--
ALTER TABLE `bankruptcy_stages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `client_assignments`
--
ALTER TABLE `client_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD CONSTRAINT `admin_messages_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `client_assignments`
--
ALTER TABLE `client_assignments`
  ADD CONSTRAINT `client_assignments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_assignments_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
