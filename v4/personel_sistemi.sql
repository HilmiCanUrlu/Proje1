-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 02 Mar 2025, 12:22:08
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
-- Tablo için tablo yapısı `dosyalar`
--

CREATE TABLE `dosyalar` (
  `dosya_id` int(11) NOT NULL,
  `izin_id` int(11) DEFAULT NULL,
  `personel_id` int(11) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `islem_turu` varchar(50) NOT NULL,
  `dosya_turu` varchar(50) NOT NULL,
  `il` varchar(50) NOT NULL,
  `ilce` varchar(50) NOT NULL,
  `mahalle` varchar(50) NOT NULL,
  `ada` varchar(50) DEFAULT NULL,
  `parsel` varchar(50) DEFAULT NULL,
  `dosya_durumu` enum('Hazırlandı','Belediyede','Kadastroda','Diğer','Tamamlandı') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musteriler`
--

CREATE TABLE `musteriler` (
  `musteri_id` int(11) NOT NULL,
  `musteri_turu` enum('Gerçek Kişi','Tüzel kişi') NOT NULL,
  `musteri_adi` varchar(100) NOT NULL,
  `tc_kimlik_no` char(11) DEFAULT NULL,
  `telefon` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fatura_adresi` text NOT NULL,
  `firma_adi` varchar(100) DEFAULT NULL,
  `vergi_dairesi` varchar(100) DEFAULT NULL,
  `vergi_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `personel`
--

CREATE TABLE `personel` (
  `personel_id` int(11) NOT NULL,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `tc_kimlik_no` char(11) NOT NULL,
  `telefon` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `personel`
--

INSERT INTO `personel` (`personel_id`, `ad`, `soyad`, `kullanici_adi`, `email`, `sifre`, `tc_kimlik_no`, `telefon`) VALUES
(1, 'Mehmet', 'Erkal', 'admin', 'admin@example.com', '123456', '12345678901', '5551234567');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `dosyalar`
--
ALTER TABLE `dosyalar`
  ADD PRIMARY KEY (`dosya_id`),
  ADD KEY `personel_id` (`personel_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Tablo için indeksler `muhasebe`
--
ALTER TABLE `muhasebe`
  ADD PRIMARY KEY (`muhasebe_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Tablo için indeksler `musteriler`
--
ALTER TABLE `musteriler`
  ADD PRIMARY KEY (`musteri_id`),
  ADD UNIQUE KEY `tc_kimlik_no` (`tc_kimlik_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `vergi_no` (`vergi_no`);

--
-- Tablo için indeksler `personel`
--
ALTER TABLE `personel`
  ADD PRIMARY KEY (`personel_id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `tc_kimlik_no` (`tc_kimlik_no`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `dosyalar`
--
ALTER TABLE `dosyalar`
  MODIFY `dosya_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `muhasebe`
--
ALTER TABLE `muhasebe`
  MODIFY `muhasebe_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musteriler`
--
ALTER TABLE `musteriler`
  MODIFY `musteri_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `personel`
--
ALTER TABLE `personel`
  MODIFY `personel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `dosyalar`
--
ALTER TABLE `dosyalar`
  ADD CONSTRAINT `dosyalar_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`personel_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dosyalar_ibfk_2` FOREIGN KEY (`musteri_id`) REFERENCES `musteriler` (`musteri_id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `muhasebe`
--
ALTER TABLE `muhasebe`
  ADD CONSTRAINT `muhasebe_ibfk_1` FOREIGN KEY (`musteri_id`) REFERENCES `musteriler` (`musteri_id`) ON DELETE CASCADE;

--
-- Tablo için tablo yapısı `sistem_loglar`
--

CREATE TABLE `sistem_loglar` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `personel_id` int(11) DEFAULT NULL,
  `islem_tipi` varchar(50) NOT NULL,
  `islem_detay` text DEFAULT NULL,
  `ip_adresi` varchar(45) NOT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `personel_id` (`personel_id`),
  CONSTRAINT `sistem_loglar_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`personel_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
