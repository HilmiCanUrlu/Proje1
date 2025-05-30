-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 20 Nis 2025, 23:59:58
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
(3, 4, 2, 4, '2025-03-02 14:40:31', 'Röperli Kroki', 'SHKM Dosyaları', 'ADANA', 'Akdeniz', 'kyk', '88', '129', 'Hazırlandı'),
(4, NULL, 2, 6, '2025-04-20 21:53:57', 'Röperli Kroki', 'LİHKAB', 'İZMİR', 'BORNOVA', 'küçükpark', '336', '36', 'Hazırlandı');

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
(51, 'g', '2025-04-18', 'g', '1', 'Firma İşlemleri', NULL, NULL, NULL, 0, 0, '2025-04-17 22:04:50', '68017b02ca42a'),
(131, 't', '2025-04-18', 'd', '1', 'Kişisel Gelişim', NULL, NULL, NULL, 0, 0, '2025-04-20 20:02:09', '680552c13ca19');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `files`
--

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `dosya_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `files`
--

INSERT INTO `files` (`file_id`, `dosya_id`, `file_name`, `original_name`, `file_path`, `file_type`, `file_size`, `upload_date`, `uploaded_by`, `is_deleted`) VALUES
(1, 3, '68042e83297d9.csv', 'breast_cancer.csv', 'uploads/68042e83297d9.csv', 'text/csv', 19635, '2025-04-19 23:15:15', 1, 0),
(2, 3, '68042ef6b9009.png', 'Ekran Görüntüsü (1).png', 'uploads/68042ef6b9009.png', 'image/png', 255779, '2025-04-19 23:17:10', 2, 0);

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
(1, 4, 50000.00, 25000.00, 25000.00, '');

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
(6, 'Gerçek Kişi', 'deneyent3', '99999999999', '571965715', 'asaspas@gmail.com', 'sfffasfasf', NULL, NULL, NULL);

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
(1, 'Mehmet', 'Erkal', 'admin', 'admin@example.com', '123456', '12345678901', '5551234567'),
(2, 'ali', 'veli', 'ali', 'ali@gmail.com', '123456', '11111111111', '5555555555'),
(4, 'buğra', 'karaahmetoğlu', 'bugra', 'bugra@gmail.com', '123456', '22222222222', '5555555555');

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
(10, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-19 19:47:12'),
(11, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-19 20:37:50'),
(12, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-19 20:58:12'),
(13, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-19 20:59:42'),
(14, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-19 21:19:08'),
(15, 4, 'LOGIN', 'Kullanıcı girişi yapıldı: bugra', '::1', '2025-04-19 21:20:48'),
(16, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-19 21:25:58'),
(17, 4, 'LOGIN', 'Kullanıcı girişi yapıldı: bugra', '::1', '2025-04-19 21:30:05'),
(18, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-19 21:33:54'),
(19, 4, 'LOGIN', 'Kullanıcı girişi yapıldı: bugra', '::1', '2025-04-19 21:43:00'),
(20, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-19 21:51:26'),
(21, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-19 22:20:31'),
(22, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-19 23:16:46'),
(23, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-20 10:41:11'),
(24, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-20 19:35:52'),
(25, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-20 19:38:50'),
(26, 1, 'LOGIN', 'Kullanıcı girişi yapıldı: admin', '::1', '2025-04-20 19:51:26'),
(27, 1, 'DOSYA_EKLE', 'Yeni dosya eklendi: LİHKAB - Müşteri ID: 6', '::1', '2025-04-20 21:53:57'),
(28, 2, 'LOGIN', 'Kullanıcı girişi yapıldı: ali', '::1', '2025-04-20 21:56:33');

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
-- Tablo için indeksler `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `dosya_id` (`dosya_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

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
  MODIFY `dosya_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `etkinlikler`
--
ALTER TABLE `etkinlikler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- Tablo için AUTO_INCREMENT değeri `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `muhasebe`
--
ALTER TABLE `muhasebe`
  MODIFY `muhasebe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `musteriler`
--
ALTER TABLE `musteriler`
  MODIFY `musteri_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `personel`
--
ALTER TABLE `personel`
  MODIFY `personel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `sistem_loglar`
--
ALTER TABLE `sistem_loglar`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
-- Tablo kısıtlamaları `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`dosya_id`) REFERENCES `dosyalar` (`dosya_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `personel` (`personel_id`);

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
