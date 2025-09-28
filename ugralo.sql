-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Sze 28. 19:17
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

CREATE DATABASE IF NOT EXISTS `ugralo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci;
USE `ugralo`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `ugralo`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `status` enum('Függőben','Jóváhagyva','Elutasítva','Törölve') DEFAULT 'Függőben',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `game_id`, `booking_date`, `status`, `note`, `created_at`) VALUES
(25, 3, 3, '2025-08-28', 'Törölve', '', '2025-08-23 09:52:34'),
(27, 1, 22, '2025-09-17', 'Törölve', '', '2025-09-16 18:17:29'),
(28, 1, 2, '2025-09-17', 'Törölve', '', '2025-09-16 18:18:30'),
(29, 3, 5, '2025-09-28', 'Törölve', 'teszt\r\n', '2025-09-28 15:18:24');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `games`
--

INSERT INTO `games` (`id`, `name`, `description`, `price`, `image_url`, `created_at`) VALUES
(1, 'Körhinta', 'Színes autók,és cuki állatok társaságában élvezhetik a gyerekek ezt az örökzöld játékot.', 32900.00, 'uploads/games/game_68a95c10105730.89299525.jpg', '2025-08-12 09:56:38'),
(2, 'Kalóz légvár', 'A nyílt tengeri kalózok társaságában ugrálhatnak,csúszdázhatnak a gyerekek.', 38900.00, 'uploads/games/game_68a95d02be8024.60128340.jpg', '2025-08-12 09:56:38'),
(3, 'UFO körhinta', 'Vidám,színes járművekkel,és állatokkal a gyerekek a felhők közt élvezhetik a nagy kalandot.', 29900.00, 'uploads/games/game_68a95e15e24944.68132028.jpg', '2025-08-12 09:56:38'),
(4, 'Lánchinta', 'Ismert mesehősök társaságában szórakozhatnak a gyerekek,ezzel az örök klasszikus játékkal', 34900.00, 'uploads/games/game_68a96095c6e187.63172610.jpg', '2025-08-12 09:56:38'),
(5, 'Flintstones játszóház', 'A két ismert,kőkorszaki szaki társaságában élvezhetik a gyerekek a játszóház nyújtotta lehetőségeket.', 31900.00, 'uploads/games/game_68a961e60c27b5.55795893.jpg', '2025-08-12 09:56:38'),
(18, 'Póniló - Csillag', 'Csillag póni hűséges hátán a gyerekek a végtelen mezőkön élvezhetik a lovaglás és a vágtatás nagy kalandját.', 14900.00, 'uploads/games/game_68c9966fac11b0.73472302.png', '2025-09-16 16:55:11'),
(19, 'Póniló - Szellő', 'Szellő póni sebes hátán a gyerekek a napsütötte réteken száguldva élvezhetik a szabadság nagy kalandját.', 14900.00, 'uploads/games/game_68c996a12f8827.59940961.jpg', '2025-09-16 16:56:01'),
(20, 'Póniló - Marci', 'Marci póni barátságos hátán a gyerekek a legizgalmasabb ösvényeken járva élvezhetik a felfedezés nagy kalandját.', 14900.00, 'uploads/games/game_68c9970c7eadc3.28808197.jpg', '2025-09-16 16:57:48'),
(21, 'Lovaskocsi', 'A gyerekek mosolyogva figyelhetik a tájat, miközben a szél finoman borzolja a hajukat, és együtt élvezhetik a nyugodt, békés utazást.', 19900.00, 'uploads/games/game_68c9a6f7254bc7.70170768.jpg', '2025-09-16 18:05:43'),
(22, 'Trambulin', 'A trambulin izgalmas ugrálási élményt kínál a gyerekeknek, ahol szabadon kiélhetik energiáikat és fejleszthetik egyensúlyérzéküket.', 16900.00, 'uploads/games/game_68c9a94866e830.48352859.jpg', '2025-09-16 18:15:36');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Toxicgamer13', 'sandorbence30@proton.me', '$2y$10$.uWi.qaE96Yaf4DLWlmP2eQv3R9ouWHHmqmTj6JYpFzg5ZigVYzMW', 'user', '2025-08-12 09:32:49'),
(3, 'admin', '', '$2y$10$RslMQCGwnXlnLOW1G25GQuS/29lFVUFuGozI3VtSNaSN6Q8Mw6XXy', 'admin', '2025-08-12 09:48:47'),
(4, 'admin2', 'sandorbence31@proton.me', '$2y$10$p5ZXVY4MWH3ghVYxrt4WUOO/5e4lKKrEQT6P5ri4R8wjrQOatcSJG', 'admin', '2025-09-28 15:47:27');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- A tábla indexei `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT a táblához `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
