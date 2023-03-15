-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 15 2023 г., 15:07
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `chipization`
--

-- --------------------------------------------------------

--
-- Структура таблицы `accounts`
--

CREATE TABLE `accounts` (
  `id` int NOT NULL,
  `firstName` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `lastName` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `accounts`
--

INSERT INTO `accounts` (`id`, `firstName`, `lastName`, `email`, `password`) VALUES
(1, 'Nikolai', 'Petrov', 'emailemail@mail.mail', '123465qwe'),
(2, 'Egor', 'Yakovenko', 'superegor@mail.mail', 'egoregor12'),
(3, 'Niko', 'Ershov', 'ershov@mail.mail', 'ershov'),
(4, 'Alexey', 'Alexandrov', 'alex@mail.mail', 'alexalex'),
(5, 'Sergei', 'Sergeev', 'sergei@mail.mail', 'ser123'),
(8, 'Admin', 'AlsoAdmin', 'admin@mail.ru', 'admin');

-- --------------------------------------------------------

--
-- Структура таблицы `animals`
--

CREATE TABLE `animals` (
  `id` bigint NOT NULL,
  `weight` float NOT NULL,
  `length` float NOT NULL,
  `height` float NOT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'OTHER',
  `lifeStatus` varchar(5) NOT NULL DEFAULT 'ALIVE',
  `chippingDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chipperId` int NOT NULL,
  `chippingLocationId` bigint NOT NULL,
  `deathDateTime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `animals`
--

INSERT INTO `animals` (`id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, `chippingDateTime`, `chipperId`, `chippingLocationId`, `deathDateTime`) VALUES
(1, 30, 0.7, 0.6, 'MALE', 'ALIVE', '2023-03-08 06:51:51', 1, 1, NULL),
(2, 40, 0.87, 0.9, 'FEMALE', 'ALIVE', '2023-02-26 19:36:38', 2, 2, NULL),
(3, 120, 3.45, 0.14, 'MALE', 'ALIVE', '2023-02-26 19:38:27', 3, 3, NULL),
(4, 20, 50, 54, 'MALE', 'ALIVE', '2023-03-13 06:54:41', 1, 2, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `animal_locations`
--

CREATE TABLE `animal_locations` (
  `id` bigint NOT NULL,
  `id_animal` bigint NOT NULL,
  `id_location` bigint NOT NULL,
  `dateTimeOfVisitLocationPoint` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `animal_locations`
--

INSERT INTO `animal_locations` (`id`, `id_animal`, `id_location`, `dateTimeOfVisitLocationPoint`) VALUES
(1, 1, 1, '2023-03-01 07:40:16'),
(2, 1, 3, '2023-03-01 07:40:30'),
(3, 2, 1, '2023-03-01 07:35:30'),
(4, 2, 3, '2023-03-01 07:50:00'),
(5, 3, 1, '2023-03-01 07:45:16'),
(6, 4, 1, '2023-03-13 14:29:02'),
(7, 4, 2, '2023-03-13 14:29:08');

-- --------------------------------------------------------

--
-- Структура таблицы `animal_types`
--

CREATE TABLE `animal_types` (
  `id_animal` bigint NOT NULL,
  `id_type` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `animal_types`
--

INSERT INTO `animal_types` (`id_animal`, `id_type`) VALUES
(1, 1),
(1, 6),
(2, 3),
(2, 4),
(2, 6),
(3, 2),
(4, 2),
(4, 5);

-- --------------------------------------------------------

--
-- Структура таблицы `locations`
--

CREATE TABLE `locations` (
  `id` bigint NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `locations`
--

INSERT INTO `locations` (`id`, `latitude`, `longitude`) VALUES
(1, 89, 12),
(2, 67, 67),
(3, 89, 68),
(4, 56, 28),
(7, 71.2334564, 23.4536453),
(8, -17.4563564, 22.94655697719939);

-- --------------------------------------------------------

--
-- Структура таблицы `types`
--

CREATE TABLE `types` (
  `id` bigint NOT NULL,
  `type` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `types`
--

INSERT INTO `types` (`id`, `type`) VALUES
(1, 'dog'),
(2, 'snake'),
(3, 'cat'),
(4, 'bird'),
(5, 'insect'),
(6, 'four legs');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `animals`
--
ALTER TABLE `animals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chipperId` (`chipperId`) USING BTREE,
  ADD KEY `chippingLocationId` (`chippingLocationId`);

--
-- Индексы таблицы `animal_locations`
--
ALTER TABLE `animal_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_animal` (`id_animal`),
  ADD KEY `id_location` (`id_location`);

--
-- Индексы таблицы `animal_types`
--
ALTER TABLE `animal_types`
  ADD KEY `id_animal` (`id_animal`,`id_type`),
  ADD KEY `id_type` (`id_type`);

--
-- Индексы таблицы `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `animals`
--
ALTER TABLE `animals`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `animal_locations`
--
ALTER TABLE `animal_locations`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `types`
--
ALTER TABLE `types`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `animals`
--
ALTER TABLE `animals`
  ADD CONSTRAINT `animals_ibfk_1` FOREIGN KEY (`chipperId`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `animals_ibfk_2` FOREIGN KEY (`chippingLocationId`) REFERENCES `locations` (`id`);

--
-- Ограничения внешнего ключа таблицы `animal_locations`
--
ALTER TABLE `animal_locations`
  ADD CONSTRAINT `animal_locations_ibfk_1` FOREIGN KEY (`id_location`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `animal_locations_ibfk_2` FOREIGN KEY (`id_animal`) REFERENCES `animals` (`id`);

--
-- Ограничения внешнего ключа таблицы `animal_types`
--
ALTER TABLE `animal_types`
  ADD CONSTRAINT `animal_types_ibfk_1` FOREIGN KEY (`id_animal`) REFERENCES `animals` (`id`),
  ADD CONSTRAINT `animal_types_ibfk_2` FOREIGN KEY (`id_type`) REFERENCES `types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
