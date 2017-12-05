-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost:3306
-- Vytvořeno: Úte 05. pro 2017, 16:05
-- Verze serveru: 10.1.26-MariaDB-0+deb9u1
-- Verze PHP: 7.0.19-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `rtls`
--
CREATE DATABASE IF NOT EXISTS `rtls` DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;
USE `rtls`;

-- --------------------------------------------------------

--
-- Struktura tabulky `AR_AP_NOTIFICATION`
--

CREATE TABLE `AR_AP_NOTIFICATION` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `msg_id` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `ap_mac` varchar(11) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `AR_STATION_REPORT`
--

CREATE TABLE `AR_STATION_REPORT` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ap_mac` varchar(20) COLLATE utf8_czech_ci NOT NULL COMMENT 'drátová MAC adresa APčka',
  `station_mac` varchar(12) COLLATE utf8_czech_ci NOT NULL COMMENT 'MAC adresa klienta',
  `noise_floor` int(11) NOT NULL,
  `data_rate` varchar(2) COLLATE utf8_czech_ci NOT NULL,
  `channel` int(11) NOT NULL,
  `rssi` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `associated` varchar(50) COLLATE utf8_czech_ci NOT NULL COMMENT '01=připojen, 02=nepřipojen',
  `radio_bssid` varchar(12) COLLATE utf8_czech_ci NOT NULL COMMENT 'MAC adresa APčka, které ho slyšelo',
  `mon_bssid` varchar(12) COLLATE utf8_czech_ci NOT NULL COMMENT 'MAC adresa AP, kam je klient připojen',
  `age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Clients`
--

CREATE TABLE `Clients` (
  `id` int(11) NOT NULL,
  `mac` varchar(12) COLLATE utf8_czech_ci NOT NULL,
  `device` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `AR_AP_NOTIFICATION`
--
ALTER TABLE `AR_AP_NOTIFICATION`
  ADD PRIMARY KEY (`ap_mac`);

--
-- Klíče pro tabulku `Clients`
--
ALTER TABLE `Clients`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `Clients`
--
ALTER TABLE `Clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
