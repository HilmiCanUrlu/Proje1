-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: personel_sistemi
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dosyalar`
--

DROP TABLE IF EXISTS `dosyalar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dosyalar` (
  `dosya_id` int(11) NOT NULL AUTO_INCREMENT,
  `izin_id` int(11) DEFAULT NULL,
  `personel_id` int(11) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `tapu_maliki` varchar(255) DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `islem_turu` varchar(50) NOT NULL,
  `dosya_turu` varchar(50) NOT NULL,
  `il` varchar(50) NOT NULL,
  `ilce` varchar(50) NOT NULL,
  `mahalle` varchar(50) NOT NULL,
  `ada` varchar(50) DEFAULT NULL,
  `parsel` varchar(50) DEFAULT NULL,
  `dosya_durumu` enum('Hazırlandı','Belediyede','Kadastroda','Diğer','Tamamlandı') NOT NULL,
  `durum` enum('aktif','pasif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`dosya_id`),
  KEY `personel_id` (`personel_id`),
  KEY `musteri_id` (`musteri_id`),
  CONSTRAINT `dosyalar_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`personel_id`) ON DELETE SET NULL,
  CONSTRAINT `dosyalar_ibfk_2` FOREIGN KEY (`musteri_id`) REFERENCES `musteriler` (`musteri_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dosyalar`
--

LOCK TABLES `dosyalar` WRITE;
/*!40000 ALTER TABLE `dosyalar` DISABLE KEYS */;
INSERT INTO `dosyalar` VALUES (5,NULL,2,7,NULL,'2025-04-21 10:24:23','Mahkeme Dosyası','SHKM Dosyaları','ESKİŞEHİR','MERKEZ','kyk','88','129','Belediyede','pasif'),(6,NULL,1,8,'buğra k.','2025-04-21 10:40:15','İfraz Dosyası','LİHKAB','AYDIN','Kuyucak','küçükpark','1','26','Tamamlandı','pasif'),(7,NULL,5,9,'Nedim A.','2025-04-21 13:53:54','Tus Dosyası','Takım Proje','AYDIN','MERKEZ','yok','500','500','Hazırlandı','aktif'),(8,NULL,2,11,NULL,'2025-04-21 14:43:49','Tus Dosyası','LİHKAB','AKSARAY','MERKEZ','Yağcılar Mahhallesi','234','52525','Tamamlandı','pasif');
/*!40000 ALTER TABLE `dosyalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etkinlikler`
--

DROP TABLE IF EXISTS `etkinlikler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `etkinlikler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `recurrence_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etkinlikler`
--

LOCK TABLES `etkinlikler` WRITE;
/*!40000 ALTER TABLE `etkinlikler` DISABLE KEYS */;
INSERT INTO `etkinlikler` VALUES (132,'Uygulama Sunumu','2025-04-21','Tapu Otomasyon uygulamasının sunumu','1','Toplantı',NULL,NULL,NULL,0,0,'2025-04-21 13:51:17','68064d55a023f'),(133,'görev','2025-04-21','görev','1','Firma İşlemleri','Hilmi Can Ürlü','7','9',0,0,'2025-04-21 14:21:24','6806546424b3b'),(134,'onay','2025-04-21','kkkkk','1','Firma İşlemleri','Mehmet Erkal','8','11',0,0,'2025-04-21 14:48:18','68065ab28521f');
/*!40000 ALTER TABLE `etkinlikler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `dosya_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`file_id`),
  KEY `dosya_id` (`dosya_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`dosya_id`) REFERENCES `dosyalar` (`dosya_id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `personel` (`personel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (3,6,'68062cf634800.png','Ekran görüntüsü 2025-02-21 023818.png','uploads/68062cf634800.png','image/png',156506,'2025-04-21 11:33:10',1,0),(5,8,'68065e8d044d7.pdf','sistem_loglari_2025-04-21 (4).pdf','uploads/68065e8d044d7.pdf','application/pdf',97593,'2025-04-21 15:04:45',1,0),(6,8,'68065e95ea0f9.sql','muhasebe.sql','uploads/68065e95ea0f9.sql','application/octet-stream',2332,'2025-04-21 15:04:53',1,0);
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `muhasebe`
--

DROP TABLE IF EXISTS `muhasebe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `muhasebe` (
  `muhasebe_id` int(11) NOT NULL AUTO_INCREMENT,
  `dosya_id` int(11) NOT NULL,
  `toplam_tutar` decimal(10,2) NOT NULL,
  `kalan_tutar` decimal(10,2) NOT NULL,
  `yapilan_odeme` decimal(10,2) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`muhasebe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `muhasebe`
--

LOCK TABLES `muhasebe` WRITE;
/*!40000 ALTER TABLE `muhasebe` DISABLE KEYS */;
INSERT INTO `muhasebe` VALUES (1,5,75000.00,75000.00,0.00,'','2025-04-21 10:32:22'),(2,5,75000.00,63000.00,12000.00,'','2025-04-21 10:32:30'),(3,5,75000.00,70000.00,5000.00,'ödedik','2025-04-21 10:35:06'),(4,5,75000.00,73000.00,2000.00,'','2025-04-21 10:35:20'),(5,5,75000.00,74000.00,1000.00,'','2025-04-21 10:37:09'),(6,5,75000.00,65000.00,10000.00,'ödeme yapıldı','2025-04-21 10:38:53'),(7,6,20000.00,20000.00,0.00,'','2025-04-21 10:40:30'),(8,6,20000.00,20000.00,0.00,'','2025-04-21 10:43:07'),(9,6,20000.00,20000.00,0.00,'','2025-04-21 10:45:28'),(10,6,20000.00,20000.00,0.00,'','2025-04-21 10:47:17'),(11,5,75000.00,75000.00,0.00,'','2025-04-21 10:48:13'),(12,6,200000.00,200000.00,0.00,'','2025-04-21 10:49:20'),(13,6,200000.00,150000.00,50000.00,'','2025-04-21 10:51:51'),(14,6,200000.00,145000.00,5000.00,'','2025-04-21 10:59:31'),(15,6,200000.00,140000.00,5000.00,'','2025-04-21 11:00:24'),(16,6,200.00,-144800.00,145000.00,'','2025-04-21 11:08:54'),(17,5,75000.00,60000.00,15000.00,'','2025-04-21 11:12:40'),(18,5,75000.00,40000.00,20000.00,'','2025-04-21 11:12:54'),(19,7,100000.00,100000.00,0.00,'','2025-04-21 14:24:19'),(20,8,50000.00,30668.00,6000.00,'yok','2025-05-05 12:10:35');
/*!40000 ALTER TABLE `muhasebe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `musteriler`
--

DROP TABLE IF EXISTS `musteriler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musteriler` (
  `musteri_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_turu` enum('Gerçek Kişi','Tüzel kişi') NOT NULL,
  `musteri_adi` varchar(100) NOT NULL,
  `tc_kimlik_no` char(11) DEFAULT NULL,
  `telefon` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fatura_adresi` text NOT NULL,
  `firma_adi` varchar(100) DEFAULT NULL,
  `vergi_dairesi` varchar(100) DEFAULT NULL,
  `vergi_no` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`musteri_id`),
  UNIQUE KEY `tc_kimlik_no` (`tc_kimlik_no`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `vergi_no` (`vergi_no`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (7,'Gerçek Kişi','Buğra K.','11111111111','5055036126','bugra@gmail.com','ev',NULL,NULL,NULL),(8,'Gerçek Kişi','Yiğit F.','22222222222','53333333333','asas@gmail.com','iş',NULL,NULL,NULL),(9,'Gerçek Kişi','Nedim A.','12345678956','05455294555','gmail@gmail.com','gölhisar',NULL,NULL,NULL),(10,'Tüzel kişi','Hilmi Ü.','45256654919','05455294599','urluhilni7@gmail.com','yok','proje1','burdur','19+84+85167'),(11,'Gerçek Kişi','halil erkal','15245295492','05455294595','asdfghgfds@TATGTREWSSDFGF','fff',NULL,NULL,NULL);
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel`
--

DROP TABLE IF EXISTS `personel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personel` (
  `personel_id` int(11) NOT NULL AUTO_INCREMENT,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `tc_kimlik_no` char(11) NOT NULL,
  `telefon` varchar(15) NOT NULL,
  PRIMARY KEY (`personel_id`),
  UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `tc_kimlik_no` (`tc_kimlik_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel`
--

LOCK TABLES `personel` WRITE;
/*!40000 ALTER TABLE `personel` DISABLE KEYS */;
INSERT INTO `personel` VALUES (1,'Mehmet','Erkal','admin','admin@example.com','123456','12345678901','5551234567'),(2,'Ali','Veli','ali','ali@gmail.com','123456','11111111111','5555555555'),(4,'Buğra','Karaahmetoğlu','bugra','bugra@gmail.com','123456','22222222222','5555555555'),(5,'Hilmi Can','Ürlü','dev','hilmi.urlu07@gmail.com','123456','12345678905','05455294599');
/*!40000 ALTER TABLE `personel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistem_loglar`
--

DROP TABLE IF EXISTS `sistem_loglar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistem_loglar`
--

LOCK TABLES `sistem_loglar` WRITE;
/*!40000 ALTER TABLE `sistem_loglar` DISABLE KEYS */;
INSERT INTO `sistem_loglar` VALUES (1,1,'DOSYA_EKLE','Yeni dosya eklendi: SHKM Dosyaları - deneme','::1','2025-03-02 13:04:21'),(2,1,'DOSYA_EKLE','Yeni dosya eklendi: Takım Proje - deneme2','::1','2025-03-02 13:19:40'),(3,1,'DOSYA_EKLE','Yeni dosya eklendi: SHKM Dosyaları - Müşteri ID: 4','::1','2025-03-02 14:40:31'),(4,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-03-03 05:49:30'),(5,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-03-07 09:03:49'),(6,NULL,'LOGIN_FAILED','Başarısız giriş denemesi: admin','::1','2025-04-13 13:43:17'),(7,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-13 13:46:06'),(8,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-16 17:18:13'),(9,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-17 19:49:37'),(10,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-19 19:47:12'),(11,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-19 20:37:50'),(12,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-19 20:58:12'),(13,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-19 20:59:42'),(14,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-19 21:19:08'),(15,4,'LOGIN','Kullanıcı girişi yapıldı: bugra','::1','2025-04-19 21:20:48'),(16,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-19 21:25:58'),(17,4,'LOGIN','Kullanıcı girişi yapıldı: bugra','::1','2025-04-19 21:30:05'),(18,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-19 21:33:54'),(19,4,'LOGIN','Kullanıcı girişi yapıldı: bugra','::1','2025-04-19 21:43:00'),(20,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-19 21:51:26'),(21,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-19 22:20:31'),(22,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-19 23:16:46'),(23,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-20 10:41:11'),(24,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-20 19:35:52'),(25,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-20 19:38:50'),(26,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-20 19:51:26'),(27,1,'DOSYA_EKLE','Yeni dosya eklendi: LİHKAB - Müşteri ID: 6','::1','2025-04-20 21:53:57'),(28,2,'LOGIN','Kullanıcı girişi yapıldı: ali','::1','2025-04-20 21:56:33'),(29,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 10:10:49'),(30,1,'DOSYA_EKLE','Yeni dosya eklendi: SHKM Dosyaları - Müşteri ID: 7','::1','2025-04-21 10:24:23'),(31,1,'DOSYA_EKLE','Yeni dosya eklendi: LİHKAB - Müşteri ID: 7','::1','2025-04-21 10:40:15'),(32,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: pasif','::1','2025-04-21 11:20:02'),(33,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: aktif','::1','2025-04-21 11:30:30'),(34,1,'DOSYA_GUNCELLE','Dosya müşteri ve tapu maliki bilgileri güncellendi: Dosya ID: 6, Müşteri ID: 8, Tapu Maliki: yiğit','::1','2025-04-21 11:33:03'),(35,1,'DOSYA_GUNCELLE','Dosya müşteri ve tapu maliki bilgileri güncellendi: Dosya ID: 6, Müşteri ID: 7, Tapu Maliki: buğra k.','::1','2025-04-21 11:33:21'),(36,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 11:44:17'),(37,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 12:02:53'),(38,1,'DOSYA_GUNCELLE','Dosya müşteri bilgileri güncellendi: Dosya ID: 6, Müşteri ID: 8','::1','2025-04-21 12:03:51'),(39,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 12:05:00'),(40,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 6, Yeni Dosya Durumu: Tapuda','::1','2025-04-21 12:05:54'),(41,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: pasif','::1','2025-04-21 12:11:33'),(42,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 6, Yeni Dosya Durumu: Tamamlandı','::1','2025-04-21 12:11:39'),(43,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 12:25:01'),(44,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: aktif','::1','2025-04-21 12:25:57'),(45,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: pasif','::1','2025-04-21 12:26:02'),(46,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: aktif','::1','2025-04-21 12:26:08'),(47,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: pasif','::1','2025-04-21 12:26:13'),(48,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: aktif','::1','2025-04-21 12:26:18'),(49,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 6, Yeni Durum: pasif','::1','2025-04-21 12:26:24'),(50,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 12:30:21'),(51,5,'LOGIN','Kullanıcı girişi yapıldı: dev','::1','2025-04-21 12:30:49'),(52,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 13:50:19'),(53,1,'DOSYA_EKLE','Yeni dosya eklendi: Takım Proje - Müşteri ID: 9','::1','2025-04-21 13:53:54'),(54,1,'DOSYA_GUNCELLE','Dosya müşteri ve tapu maliki bilgileri güncellendi: Dosya ID: 7, Müşteri ID: 9, Tapu Maliki: Nedim Alptekin','::1','2025-04-21 13:56:03'),(55,1,'DOSYA_GUNCELLE','Dosya müşteri ve tapu maliki bilgileri güncellendi: Dosya ID: 7, Müşteri ID: 9, Tapu Maliki: Nedim A.','::1','2025-04-21 14:10:04'),(56,1,'DOSYA_GUNCELLE','Dosya parsel bilgileri güncellendi: Dosya ID: 7','::1','2025-04-21 14:10:07'),(57,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 14:12:57'),(58,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 14:17:46'),(59,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 5, Yeni Dosya Durumu: Belediyede','::1','2025-04-21 14:24:39'),(60,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 14:37:37'),(61,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 5, Yeni Durum: pasif','::1','2025-04-21 14:38:22'),(62,5,'LOGIN','Kullanıcı girişi yapıldı: dev','::1','2025-04-21 14:39:22'),(63,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-04-21 14:40:06'),(64,1,'DOSYA_EKLE','Yeni dosya eklendi: LİHKAB - Müşteri ID: 11','::1','2025-04-21 14:43:49'),(65,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 8, Yeni Dosya Durumu: Belediyede','::1','2025-04-21 14:46:00'),(66,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 8, Yeni Dosya Durumu: Tamamlandı','::1','2025-04-21 14:46:05'),(67,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 8, Yeni Durum: pasif','::1','2025-04-21 14:46:11'),(68,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-05-05 12:10:15'),(69,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-05-05 12:15:45'),(70,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-05-05 12:16:21'),(71,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-05-05 12:26:34'),(72,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-05-13 01:14:06'),(73,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 8, Yeni Dosya Durumu: Kadastroda','::1','2025-05-13 01:20:07'),(74,1,'DOSYA_DURUMU_GUNCELLEME','Dosya ID: 8, Yeni Dosya Durumu: Tamamlandı','::1','2025-05-13 01:20:10'),(75,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 8, Yeni Durum: aktif','::1','2025-05-13 01:39:30'),(76,1,'AKTIVITE_GUNCELLE','Dosya durumu güncellendi: Dosya ID: 8, Yeni Durum: pasif','::1','2025-05-13 01:39:32'),(77,5,'LOGIN','Kullanıcı girişi yapıldı: dev','::1','2025-05-13 03:32:55'),(78,1,'LOGIN','Kullanıcı girişi yapıldı: admin','::1','2025-05-13 03:38:56');
/*!40000 ALTER TABLE `sistem_loglar` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-13  6:41:36
