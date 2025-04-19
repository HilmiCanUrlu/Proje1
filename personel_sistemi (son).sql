-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 19 Nis 2025, 21:47:59
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

--
-- Tablo döküm verisi `dosyalar`
--

INSERT INTO `dosyalar` (`dosya_id`, `izin_id`, `personel_id`, `musteri_id`, `olusturma_tarihi`, `islem_turu`, `dosya_turu`, `il`, `ilce`, `mahalle`, `ada`, `parsel`, `dosya_durumu`) VALUES
(1, NULL, 1, NULL, '2025-03-02 13:04:21', 'İfraz Dosyası', 'SHKM Dosyaları', 'BURDUR', 'GÖLHİSAR', 'kyk', '1', '129', 'Hazırlandı'),
(2, NULL, 1, NULL, '2025-03-02 13:19:40', 'Yola Terk Dosyası', 'Takım Proje', 'BURDUR', 'GÖLHİSAR', 'kyk', '88', '200', 'Hazırlandı'),
(3, NULL, 1, 4, '2025-03-02 14:40:31', 'Röperli Kroki', 'SHKM Dosyaları', 'ADANA', 'Akdeniz', 'kyk', '88', '129', 'Hazırlandı');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `etkinlikler`
--

CREATE TABLE `etkinlikler` (
  `id` int(11) NOT NULL,
  `baslik` varchar(255) DEFAULT NULL,
  `tarih` date NOT NULL,
  `aciklama` text NOT NULL,
  `ekleyen` varchar(100) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `personel` varchar(100) DEFAULT NULL,
  `dosya_linki` varchar(255) DEFAULT NULL,
  `musteri` varchar(100) DEFAULT NULL,
  `tekrar_aylik` tinyint(1) DEFAULT 0,
  `tekrar_yillik` tinyint(1) DEFAULT 0,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `recurrence_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `etkinlikler`
--

INSERT INTO `etkinlikler` (`id`, `baslik`, `tarih`, `aciklama`, `ekleyen`, `kategori`, `personel`, `dosya_linki`, `musteri`, `tekrar_aylik`, `tekrar_yillik`, `olusturma_tarihi`, `recurrence_id`) VALUES
(13, 'görev deneme2', '2025-04-17', 'j', '1', 'Firma İşlemleri', 'Mehmet Erkal', '3', '4', 0, 0, '2025-04-17 20:42:21', NULL),
(51, 'g', '2025-04-18', 'g', '1', 'Firma İşlemleri', NULL, NULL, NULL, 0, 0, '2025-04-17 22:04:50', '68017b02ca42a');

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

--
-- Tablo döküm verisi `musteriler`
--

INSERT INTO `musteriler` (`musteri_id`, `musteri_turu`, `musteri_adi`, `tc_kimlik_no`, `telefon`, `email`, `fatura_adresi`, `firma_adi`, `vergi_dairesi`, `vergi_no`) VALUES
(4, 'Gerçek Kişi', 'deneyen2', '33333333333', '53333333333', 'asas@gmail.com', 'asfasfafs', NULL, NULL, NULL),
(5, 'Tüzel kişi', 'deneyent', NULL, '55555555555', 'gwgwe@mail.com', 'htfgghgtghd', 'tadgt', 'fdrrrrr', '555555555556'),
(6, 'Gerçek Kişi', 'deneyen3', '99999999999', '571965714', 'asaspas@gmail.com', 'sfffasfasf', NULL, NULL, NULL);

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

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sistem_loglar`
--

CREATE TABLE `sistem_loglar` (
  `log_id` int(11) NOT NULL,
  `personel_id` int(11) DEFAULT NULL,
  `islem_tipi` varchar(50) NOT NULL,
  `islem_detay` text DEFAULT NULL,
  `ip_adresi` varchar(45) NOT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `sistem_loglar`
--

INSERT INTO `sistem_loglar` (`log_id`, `personel_id`, `islem_tipi`, `islem_detay`, `ip_adresi`, `tarih`) VALUES
(1, 1, 'DOSYA_EKLE', 'Yeni dosya eklendi: SHKM Dosyaları - deneme', '::1', '2025-03-02 13:04:21'),
(2, 1, 'DOSYA_EKLE', 'Yeni dosya eklendi: Takım Proje - deneme2', '::1', '2025-03-02 13:19:40'),
(3, 1, 'DOSYA_EKLE', 'Yeni dosya eklendi: SHKM Dosyaları - Müşteri ID: 4', '::1', '2025-03-02 14:40:31'),
(4, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-03-03 05:49:30'),
(5, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-03-07 09:03:49'),
(6, NULL, 'LOGIN_FAILED', 'Başarısız giriş denemesi: admin', '::1', '2025-04-13 13:43:17'),
(7, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-13 13:46:06'),
(8, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-16 17:18:13'),
(9, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-17 19:49:37'),
(10, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-19 19:47:12');

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
-- Tablo için indeksler `etkinlikler`
--
ALTER TABLE `etkinlikler`
  ADD PRIMARY KEY (`id`);

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
-- Tablo için indeksler `sistem_loglar`
--
ALTER TABLE `sistem_loglar`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `personel_id` (`personel_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `dosyalar`
--
ALTER TABLE `dosyalar`
  MODIFY `dosya_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `etkinlikler`
--
ALTER TABLE `etkinlikler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- Tablo için AUTO_INCREMENT değeri `muhasebe`
--
ALTER TABLE `muhasebe`
  MODIFY `muhasebe_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musteriler`
--
ALTER TABLE `musteriler`
  MODIFY `musteri_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `personel`
--
ALTER TABLE `personel`
  MODIFY `personel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `sistem_loglar`
--
ALTER TABLE `sistem_loglar`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Tablo kısıtlamaları `sistem_loglar`
--
ALTER TABLE `sistem_loglar`
  ADD CONSTRAINT `sistem_loglar_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`personel_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
