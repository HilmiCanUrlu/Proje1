-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 21 Nis 2025, 12:16:53
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `personel_sistemi`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `muhasebe`
--

CREATE TABLE `muhasebe` (
  `muhasebe_id` int(11) NOT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `toplam_tutar` decimal(10,2) NOT NULL,
  `kalan_tutar` decimal(10,2) NOT NULL,
  `yapilan_odeme` decimal(10,2) NOT NULL,
  `aciklama` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `muhasebe`
--

INSERT INTO `muhasebe` (`muhasebe_id`, `musteri_id`, `toplam_tutar`, `kalan_tutar`, `yapilan_odeme`, `aciklama`) VALUES
(1, 4, 40000.00, -59123.00, 99123.00, ''),
(2, 6, 180000.00, 180000.00, 0.00, ''),
(3, 4, 400000.00, -59123.00, 99123.00, ''),
(4, 6, 180000.00, 180000.00, 0.00, 'Toplam tutar güncellendi'),
(5, 4, 500000.00, 400877.00, 99123.00, ''),
(6, 4, 500000.00, 400877.00, 99123.00, 'Toplam tutar güncellendi');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `muhasebe`
--
ALTER TABLE `muhasebe`
  ADD PRIMARY KEY (`muhasebe_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `muhasebe`
--
ALTER TABLE `muhasebe`
  MODIFY `muhasebe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `muhasebe`
--
ALTER TABLE `muhasebe`
  ADD CONSTRAINT `muhasebe_ibfk_1` FOREIGN KEY (`musteri_id`) REFERENCES `musteriler` (`musteri_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
