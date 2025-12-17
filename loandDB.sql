-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: loan_db
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `_prisma_migrations`
--

DROP TABLE IF EXISTS `_prisma_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `_prisma_migrations` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checksum` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `finished_at` datetime(3) DEFAULT NULL,
  `migration_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logs` text COLLATE utf8mb4_unicode_ci,
  `rolled_back_at` datetime(3) DEFAULT NULL,
  `started_at` datetime(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `applied_steps_count` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_prisma_migrations`
--

LOCK TABLES `_prisma_migrations` WRITE;
/*!40000 ALTER TABLE `_prisma_migrations` DISABLE KEYS */;
INSERT INTO `_prisma_migrations` VALUES ('0d8b8094-024d-4ae0-aea1-b9834e210b18','bba54836e10708e8a2c14616215f348c28f31ab08969d086bdd6ec44f461577c','2025-10-31 15:12:46.767','20251031151246_',NULL,NULL,'2025-10-31 15:12:46.755',1),('0e3692de-a69f-482b-8810-9c2e63359cf7','b734c190af94737e4aae1a801c6ab9a67be3d673428d251eebc37b1108770c29','2025-10-31 16:51:18.529','20251031165118_add_cancelled_status',NULL,NULL,'2025-10-31 16:51:18.196',1),('16baa539-c413-4a56-b7e0-90e308a23f5f','5e1ff9dedbd94d03ae1f6f6580c0aae1a92619925bc417b3fef7a180ed1f5a5f','2025-10-31 15:12:25.005','20251031151207_add_documents_table',NULL,NULL,'2025-10-31 15:12:24.420',1),('32910488-1d7f-4fe9-96e9-6b04719ec3e6','dae16e528e88b84f39390c41119ffa2c809aa414d51c8f413ccd2e5fda6d25fb','2025-12-08 13:39:35.601','20251208140000_add_barangay_city_to_borrower',NULL,NULL,'2025-12-08 13:39:35.258',1),('9481bc45-982a-4410-baaf-66e7d6170698','dae16e528e88b84f39390c41119ffa2c809aa414d51c8f413ccd2e5fda6d25fb','2025-12-08 13:39:09.155','20251208213806_add_barangay_city_to_borrower','',NULL,'2025-12-08 13:39:09.155',0),('9a037674-82fa-412f-a8db-b14c794147d9','0ca0d9443ee3fa1488d9edb6925c88e6717e709bf3bd19e4139a49533dd83b6a','2025-10-31 12:06:59.241','0002_initial_schema',NULL,NULL,'2025-10-31 12:06:53.568',1),('a857b71c-080c-48a6-a6fb-463c2add3dcc','0fb9573de20d7d3a3410afdd7f5e4abbdea15ae75eae75cded78864944bffcb6','2025-10-31 16:38:08.487','20251031163808_add_notifications',NULL,NULL,'2025-10-31 16:38:08.283',1),('de72fb2c-03ee-4edb-9775-a214e9986543','1d19bdbd6dda7207339ec81ead14e671ca74f51af3b07d9294d2e875d7099226','2025-12-09 11:40:53.103','20251209194034_add_signature_photo2x2_to_document_type',NULL,NULL,'2025-12-09 11:40:52.620',1),('effcc7ea-f2e9-488e-9650-6b33b49af30f','a8c69b3958a3fc53a2c549f177d7c2a49dda1647283fb566186a3d5838e5e446','2025-12-13 01:50:06.961','20251213000000_add_guarantor_and_location_fields','',NULL,'2025-12-13 01:50:06.961',0);
/*!40000 ALTER TABLE `_prisma_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_accounts`
--

DROP TABLE IF EXISTS `bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Manila',
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_accounts_code_key` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_accounts`
--

LOCK TABLES `bank_accounts` WRITE;
/*!40000 ALTER TABLE `bank_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_statements`
--

DROP TABLE IF EXISTS `bank_statements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_statements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bank_account_id` bigint unsigned NOT NULL,
  `statement_end_date` date NOT NULL,
  `ending_balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `source_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_statements_bankAccountId_idx` (`bank_account_id`),
  KEY `bank_statements_statement_end_date_idx` (`statement_end_date`),
  KEY `bank_statements_bank_account_id_fkey` (`bank_account_id`),
  CONSTRAINT `bank_statements_bank_account_id_fkey` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_statements`
--

LOCK TABLES `bank_statements` WRITE;
/*!40000 ALTER TABLE `bank_statements` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_statements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_transactions`
--

DROP TABLE IF EXISTS `bank_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bank_account_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `loan_id` bigint unsigned DEFAULT NULL,
  `borrower_id` bigint unsigned DEFAULT NULL,
  `ref_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kind` enum('bank','journal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank',
  `tx_date` date NOT NULL,
  `contact_display` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spent` decimal(14,2) DEFAULT NULL,
  `received` decimal(14,2) DEFAULT NULL,
  `reconcile_status` enum('pending','ok','match') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `ledger_contact` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_class` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','posted','excluded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `posted_at` datetime(3) DEFAULT NULL,
  `is_transfer` tinyint(1) NOT NULL DEFAULT '0',
  `bank_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_transactions_tx_date_idx` (`tx_date`),
  KEY `bank_transactions_kind_idx` (`kind`),
  KEY `bank_transactions_reconcile_status_idx` (`reconcile_status`),
  KEY `bank_transactions_status_idx` (`status`),
  KEY `bank_transactions_posted_at_idx` (`posted_at`),
  KEY `bank_transactions_is_transfer_idx` (`is_transfer`),
  KEY `bank_transactions_match_id_idx` (`match_id`),
  KEY `bank_transactions_bank_account_id_fkey` (`bank_account_id`),
  KEY `bank_transactions_loan_id_fkey` (`loan_id`),
  FULLTEXT KEY `bank_transactions_contact_display_description_ledger_contact_idx` (`contact_display`,`description`,`ledger_contact`,`account_name`,`remarks`,`tx_class`),
  CONSTRAINT `bank_transactions_bank_account_id_fkey` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_transactions`
--

LOCK TABLES `bank_transactions` WRITE;
/*!40000 ALTER TABLE `bank_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrowers`
--

DROP TABLE IF EXISTS `borrowers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `borrowers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `monthly_income` decimal(12,2) DEFAULT NULL,
  `civil_status` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_no` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','delinquent','closed','blacklisted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `archived_at` datetime(3) DEFAULT NULL,
  `created_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` datetime(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `deleted_at` datetime(3) DEFAULT NULL,
  `barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `borrowers_email_key` (`email`),
  KEY `borrowers_phone_idx` (`phone`),
  KEY `borrowers_reference_no_idx` (`reference_no`),
  KEY `borrowers_status_idx` (`status`),
  KEY `borrowers_is_archived_idx` (`is_archived`),
  KEY `borrowers_full_name_email_idx` (`full_name`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrowers`
--

LOCK TABLES `borrowers` WRITE;
/*!40000 ALTER TABLE `borrowers` DISABLE KEYS */;
INSERT INTO `borrowers` VALUES (4,'ARISTON CARAGAY','arisrobles07@gmail.com','$2b$12$UJs2hy/YdpSd3FAaRT/DuOxyPflrTolpqVk5vIKCEojWEHp4K.gA6','09664487480','Olo Cacamposan Mangataarem Pangasinan ','Male','Programmer ','2001-05-25',100000.00,'Single ',NULL,'active',0,NULL,'2025-10-31 13:09:46.268','2025-10-31 15:46:12.041',NULL,NULL,NULL),(5,'Jeon Jungkook ','jascode21@gmail.com','$2b$12$o.q.WXXLC3iAtbMaHPXM7ePRxBwVKveBfF1rjo6qDDqYjON/2DShK',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,NULL,'2025-10-31 16:09:39.621','2025-10-31 16:09:39.621',NULL,NULL,NULL),(6,'ARISTON CARAGAY','21ln1104_ms@psu.edu.oh','$2b$12$dfyz0OsU1dyDKjOdD4RJKePr3rAdP78UD4MQGzLRNVjCrpW.iFBiy',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,NULL,'2025-11-18 14:25:10.850','2025-11-18 14:25:10.850',NULL,NULL,NULL),(7,'Ariston','shawngavin07@gmail.com','$2b$12$1h/CWKn3kaagMOV.HFJ9eO2oC4qz1DJODi5.DDd5RB3e47js768re',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,NULL,'2025-12-07 04:20:37.819','2025-12-07 04:20:37.819',NULL,NULL,NULL),(8,'ARISTON CARAGAY','21ln1104_ms@psu.edu.ph','$2b$12$4QjL8702LCglk7/4WlXrd.tHcDP6qwI8.x2sMADNaJY/jxHl9p.bm','09020250504','Barangka Itaas Mandaluyong City Metro Manila Philippines ','Male','Programmer ','2001-05-25',30000.00,'single',NULL,'active',0,NULL,'2025-12-08 04:12:19.194','2025-12-13 01:29:58.016',NULL,NULL,NULL);
/*!40000 ALTER TABLE `borrowers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_of_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `report` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_account` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `normal_balance` enum('Debit','Credit') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debit_effect` enum('Increase','Decrease') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_effect` enum('Increase','Decrease') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_of_accounts_code_key` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `borrower_id` bigint unsigned DEFAULT NULL,
  `loan_id` bigint unsigned DEFAULT NULL,
  `document_type` enum('PRIMARY_ID','SECONDARY_ID','AGREEMENT','RECEIPT','OTHER','SIGNATURE','PHOTO_2X2') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `created_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_borrower_id_idx` (`borrower_id`),
  KEY `documents_loan_id_idx` (`loan_id`),
  KEY `documents_document_type_idx` (`document_type`),
  KEY `documents_uploaded_at_idx` (`uploaded_at`),
  CONSTRAINT `documents_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `documents_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,4,NULL,'RECEIPT','aad21784-195c-4cb5-b455-4806624905cb.jpeg','/uploads/file-1761924140234-497476049.jpeg',32295,'image/jpeg','2025-10-31 15:22:20.287','2025-10-31 15:22:20.287','2025-10-31 15:22:20.287'),(2,4,8,'RECEIPT','d6ba7821-ba63-431d-ab25-8f1359b40204.jpeg','/uploads/file-1764405241544-364422642.jpeg',475284,'image/jpeg','2025-11-29 08:34:01.769','2025-11-29 08:34:01.769','2025-11-29 08:34:01.769'),(3,4,8,'RECEIPT','028db674-f10c-471d-9b59-97ce7690d5a1.jpeg','/uploads/file-1764472699237-515723540.jpeg',152313,'image/jpeg','2025-11-30 03:18:19.364','2025-11-30 03:18:19.364','2025-11-30 03:18:19.364'),(4,8,NULL,'PRIMARY_ID','Screenshot_20251207_214223.jpg','/uploads/file-1765167798696-530267656.jpg',286435,'image/jpeg','2025-12-08 04:23:19.247','2025-12-08 04:23:19.247','2025-12-08 04:23:19.247'),(5,8,NULL,'PRIMARY_ID','Screenshot_20251207_214223.jpg','/uploads/loan-documents/8/file-1765169867348-986775626.jpg',286435,'image/jpeg','2025-12-08 04:57:47.486','2025-12-08 04:57:47.486','2025-12-08 04:57:47.486'),(6,8,NULL,'SECONDARY_ID','Screenshot_20251207_174651.jpg','/uploads/loan-documents/8/file-1765169867567-961741129.jpg',285696,'image/jpeg','2025-12-08 04:57:47.693','2025-12-08 04:57:47.693','2025-12-08 04:57:47.693'),(7,8,NULL,'PRIMARY_ID','Screenshot_20251207_214223.jpg','/uploads/loan-documents/8/file-1765170275798-850765978.jpg',286435,'image/jpeg','2025-12-08 05:04:35.886','2025-12-08 05:04:35.886','2025-12-08 05:04:35.886'),(8,8,NULL,'SECONDARY_ID','Screenshot_20251207_174651.jpg','/uploads/loan-documents/8/file-1765170275964-21928060.jpg',285696,'image/jpeg','2025-12-08 05:04:36.051','2025-12-08 05:04:36.051','2025-12-08 05:04:36.051'),(9,8,10,'AGREEMENT','Loan_Agreement_MF-2025-0010.txt','/agreements/MF-2025-0010.txt',NULL,'text/plain','2025-12-08 05:05:11.021','2025-12-08 05:05:11.021','2025-12-08 05:05:11.021'),(10,8,11,'AGREEMENT','Loan_Agreement_MF-2025-0011.txt','/agreements/MF-2025-0011.txt',NULL,'text/plain','2025-12-08 05:16:51.902','2025-12-08 05:16:51.902','2025-12-08 05:16:51.902'),(11,8,12,'AGREEMENT','Loan_Agreement_MF-2025-0012.txt','/agreements/MF-2025-0012.txt',NULL,'text/plain','2025-12-08 05:21:04.143','2025-12-08 05:21:04.143','2025-12-08 05:21:04.143'),(12,8,13,'AGREEMENT','Loan_Agreement_MF-2025-0013.txt','/agreements/MF-2025-0013.txt',NULL,'text/plain','2025-12-08 05:23:44.034','2025-12-08 05:23:44.034','2025-12-08 05:23:44.034'),(13,8,13,'AGREEMENT','Loan_Agreement_MF-2025-0013.txt','/agreements/MF-2025-0013.txt',NULL,'text/plain','2025-12-08 05:24:21.492','2025-12-08 05:24:21.492','2025-12-08 05:24:21.492'),(14,8,14,'AGREEMENT','Loan_Agreement_MF-2025-0014.txt','/agreements/MF-2025-0014.txt',NULL,'text/plain','2025-12-08 05:27:24.111','2025-12-08 05:27:24.111','2025-12-08 05:27:24.111'),(15,8,15,'AGREEMENT','Loan_Agreement_MF-2025-0015.txt','/agreements/MF-2025-0015.txt',NULL,'text/plain','2025-12-08 05:29:28.312','2025-12-08 05:29:28.312','2025-12-08 05:29:28.312'),(16,8,15,'AGREEMENT','Loan_Agreement_MF-2025-0015.txt','/agreements/MF-2025-0015.txt',NULL,'text/plain','2025-12-08 05:29:34.568','2025-12-08 05:29:34.568','2025-12-08 05:29:34.568'),(17,8,16,'AGREEMENT','Loan_Agreement_MF-2025-0016.txt','/agreements/MF-2025-0016.txt',NULL,'text/plain','2025-12-08 05:37:00.906','2025-12-08 05:37:00.906','2025-12-08 05:37:00.906'),(18,8,17,'PRIMARY_ID','Screenshot_20251207_214223.jpg','/uploads/loan-documents/8/file-1765172803555-437898732.jpg',286435,'image/jpeg','2025-12-08 05:46:43.691','2025-12-08 05:46:43.691','2025-12-08 05:46:43.691'),(19,8,17,'SECONDARY_ID','Screenshot_20251207_214223.jpg','/uploads/loan-documents/8/file-1765172803757-238337070.jpg',286435,'image/jpeg','2025-12-08 05:46:43.883','2025-12-08 05:46:43.883','2025-12-08 05:46:43.883'),(20,8,17,'AGREEMENT','Loan_Agreement_MF-2025-0017.txt','/agreements/MF-2025-0017.txt',NULL,'text/plain','2025-12-08 05:46:43.953','2025-12-08 05:46:43.953','2025-12-08 05:46:43.953'),(21,8,18,'PRIMARY_ID','Screenshot_20251207_214223.jpg','/uploads/loan-documents/8/file-1765182783617-477716352.jpg',286435,'image/jpeg','2025-12-08 08:33:04.348','2025-12-08 08:33:04.348','2025-12-08 08:33:04.348'),(22,8,18,'PRIMARY_ID','Screenshot_20251207_174651.jpg','/uploads/loan-documents/8/file-1765182784410-545007801.jpg',285696,'image/jpeg','2025-12-08 08:33:05.090','2025-12-08 08:33:05.090','2025-12-08 08:33:05.090'),(23,8,18,'SECONDARY_ID','Screenshot_20251207_214223.jpg','/uploads/loan-documents/8/file-1765182785136-690650613.jpg',286435,'image/jpeg','2025-12-08 08:33:05.710','2025-12-08 08:33:05.710','2025-12-08 08:33:05.710'),(24,8,18,'SECONDARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765182785755-176222096.jpg',44862,'image/jpeg','2025-12-08 08:33:05.853','2025-12-08 08:33:05.853','2025-12-08 08:33:05.853'),(25,8,18,'AGREEMENT','Loan_Agreement_MF-2025-0018.txt','/agreements/MF-2025-0018.txt',NULL,'text/plain','2025-12-08 08:33:05.921','2025-12-08 08:33:05.921','2025-12-08 08:33:05.921'),(26,8,19,'PRIMARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765194292417-698260330.jpg',162467,'image/jpeg','2025-12-08 11:44:52.566','2025-12-08 11:44:52.566','2025-12-08 11:44:52.566'),(27,8,19,'PRIMARY_ID','Screenshot_20251208_183325.jpg','/uploads/loan-documents/8/file-1765194292602-708910581.jpg',135384,'image/jpeg','2025-12-08 11:44:52.835','2025-12-08 11:44:52.835','2025-12-08 11:44:52.835'),(28,8,19,'SECONDARY_ID','Screenshot_20251208_183319.jpg','/uploads/loan-documents/8/file-1765194292875-332893172.jpg',137595,'image/jpeg','2025-12-08 11:44:52.998','2025-12-08 11:44:52.998','2025-12-08 11:44:52.998'),(29,8,19,'SECONDARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765194293138-203916053.jpg',44862,'image/jpeg','2025-12-08 11:44:53.168','2025-12-08 11:44:53.168','2025-12-08 11:44:53.168'),(30,8,19,'AGREEMENT','Loan_Agreement_MF-2025-0019_1765194293229.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0019_1765194293229.docx',9467,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 11:44:53.282','2025-12-08 11:44:53.282','2025-12-08 11:44:53.282'),(31,8,20,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765201646011-189319899.jpg',340839,'image/jpeg','2025-12-08 13:47:26.174','2025-12-08 13:47:26.174','2025-12-08 13:47:26.174'),(32,8,20,'PRIMARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765201646220-794437930.jpg',162467,'image/jpeg','2025-12-08 13:47:26.295','2025-12-08 13:47:26.295','2025-12-08 13:47:26.295'),(33,8,20,'SECONDARY_ID','Screenshot_20251208_183325.jpg','/uploads/loan-documents/8/file-1765201646333-166696765.jpg',135384,'image/jpeg','2025-12-08 13:47:26.399','2025-12-08 13:47:26.399','2025-12-08 13:47:26.399'),(34,8,20,'SECONDARY_ID','Screenshot_20251208_183319.jpg','/uploads/loan-documents/8/file-1765201646440-548916152.jpg',137595,'image/jpeg','2025-12-08 13:47:26.515','2025-12-08 13:47:26.515','2025-12-08 13:47:26.515'),(35,8,20,'AGREEMENT','Loan_Agreement_MF-2025-0020_1765201646565.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0020_1765201646565.docx',10157,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 13:47:26.739','2025-12-08 13:47:26.739','2025-12-08 13:47:26.739'),(36,8,21,'PRIMARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765203301073-339453977.jpg',162467,'image/jpeg','2025-12-08 14:15:01.165','2025-12-08 14:15:01.165','2025-12-08 14:15:01.165'),(37,8,21,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765203301342-649133902.jpg',340839,'image/jpeg','2025-12-08 14:15:01.467','2025-12-08 14:15:01.467','2025-12-08 14:15:01.467'),(38,8,21,'SECONDARY_ID','Screenshot_20251208_183325.jpg','/uploads/loan-documents/8/file-1765203301509-900398742.jpg',135384,'image/jpeg','2025-12-08 14:15:01.577','2025-12-08 14:15:01.577','2025-12-08 14:15:01.577'),(39,8,21,'SECONDARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765203301623-664250006.jpg',44862,'image/jpeg','2025-12-08 14:15:01.650','2025-12-08 14:15:01.650','2025-12-08 14:15:01.650'),(40,8,21,'AGREEMENT','Loan_Agreement_MF-2025-0021_1765203301703.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0021_1765203301703.docx',10372,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:15:01.782','2025-12-08 14:15:01.782','2025-12-08 14:15:01.782'),(41,8,22,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765203613993-112498202.jpg',340839,'image/jpeg','2025-12-08 14:20:14.155','2025-12-08 14:20:14.155','2025-12-08 14:20:14.155'),(42,8,22,'PRIMARY_ID','Screenshot_20251208_183319.jpg','/uploads/loan-documents/8/file-1765203614202-525957488.jpg',137595,'image/jpeg','2025-12-08 14:20:14.253','2025-12-08 14:20:14.253','2025-12-08 14:20:14.253'),(43,8,22,'SECONDARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765203614282-82115598.jpg',162467,'image/jpeg','2025-12-08 14:20:14.343','2025-12-08 14:20:14.343','2025-12-08 14:20:14.343'),(44,8,22,'SECONDARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765203614372-561781539.jpg',44862,'image/jpeg','2025-12-08 14:20:14.389','2025-12-08 14:20:14.389','2025-12-08 14:20:14.389'),(45,8,22,'AGREEMENT','Loan_Agreement_MF-2025-0022_1765203614439.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0022_1765203614439.docx',10339,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:20:14.500','2025-12-08 14:20:14.500','2025-12-08 14:20:14.500'),(46,8,23,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765203852721-786802688.jpg',340839,'image/jpeg','2025-12-08 14:24:12.893','2025-12-08 14:24:12.893','2025-12-08 14:24:12.893'),(47,8,23,'PRIMARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765203852929-949316705.jpg',162467,'image/jpeg','2025-12-08 14:24:13.016','2025-12-08 14:24:13.016','2025-12-08 14:24:13.016'),(48,8,23,'SECONDARY_ID','Screenshot_20251208_183319.jpg','/uploads/loan-documents/8/file-1765203853054-29410272.jpg',137595,'image/jpeg','2025-12-08 14:24:13.137','2025-12-08 14:24:13.137','2025-12-08 14:24:13.137'),(49,8,23,'SECONDARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765203853201-588332937.jpg',44862,'image/jpeg','2025-12-08 14:24:13.228','2025-12-08 14:24:13.228','2025-12-08 14:24:13.228'),(50,8,23,'AGREEMENT','Loan_Agreement_MF-2025-0023_1765203853276.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0023_1765203853276.docx',10340,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:24:13.383','2025-12-08 14:24:13.383','2025-12-08 14:24:13.383'),(51,8,24,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765204119133-211356956.jpg',340839,'image/jpeg','2025-12-08 14:28:39.310','2025-12-08 14:28:39.310','2025-12-08 14:28:39.310'),(52,8,24,'PRIMARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765204119343-480295879.jpg',44862,'image/jpeg','2025-12-08 14:28:39.364','2025-12-08 14:28:39.364','2025-12-08 14:28:39.364'),(53,8,24,'SECONDARY_ID','Screenshot_20251208_183319.jpg','/uploads/loan-documents/8/file-1765204119406-369634398.jpg',137595,'image/jpeg','2025-12-08 14:28:39.467','2025-12-08 14:28:39.467','2025-12-08 14:28:39.467'),(54,8,24,'SECONDARY_ID','Screenshot_20251207_174651.jpg','/uploads/loan-documents/8/file-1765204119495-903908940.jpg',285696,'image/jpeg','2025-12-08 14:28:39.606','2025-12-08 14:28:39.606','2025-12-08 14:28:39.606'),(55,8,24,'AGREEMENT','Loan_Agreement_MF-2025-0024_1765204119657.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0024_1765204119657.docx',10341,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:28:39.719','2025-12-08 14:28:39.719','2025-12-08 14:28:39.719'),(56,8,25,'PRIMARY_ID','Screenshot_20251208_222059.jpg','/uploads/loan-documents/8/file-1765204303335-494591091.jpg',143591,'image/jpeg','2025-12-08 14:31:43.362','2025-12-08 14:31:43.362','2025-12-08 14:31:43.362'),(57,8,25,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765204303383-507506382.jpg',340839,'image/jpeg','2025-12-08 14:31:43.440','2025-12-08 14:31:43.440','2025-12-08 14:31:43.440'),(58,8,25,'SECONDARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765204303460-271492091.jpg',162467,'image/jpeg','2025-12-08 14:31:43.487','2025-12-08 14:31:43.487','2025-12-08 14:31:43.487'),(59,8,25,'SECONDARY_ID','Screenshot_20251208_183325.jpg','/uploads/loan-documents/8/file-1765204303506-509330424.jpg',135384,'image/jpeg','2025-12-08 14:31:43.532','2025-12-08 14:31:43.532','2025-12-08 14:31:43.532'),(60,8,25,'AGREEMENT','Loan_Agreement_MF-2025-0025_1765204303555.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0025_1765204303555.docx',10341,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:31:43.605','2025-12-08 14:31:43.605','2025-12-08 14:31:43.605'),(61,8,26,'PRIMARY_ID','Screenshot_20251208_183325.jpg','/uploads/loan-documents/8/file-1765204509328-190668102.jpg',135384,'image/jpeg','2025-12-08 14:35:09.389','2025-12-08 14:35:09.389','2025-12-08 14:35:09.389'),(62,8,26,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765204509420-254355425.jpg',340839,'image/jpeg','2025-12-08 14:35:09.585','2025-12-08 14:35:09.585','2025-12-08 14:35:09.585'),(63,8,26,'SECONDARY_ID','Screenshot_20251208_222059.jpg','/uploads/loan-documents/8/file-1765204509614-970246141.jpg',143591,'image/jpeg','2025-12-08 14:35:09.684','2025-12-08 14:35:09.684','2025-12-08 14:35:09.684'),(64,8,26,'SECONDARY_ID','IMG-fcea9bb8633b788ee9b059489e18663f-V.jpg','/uploads/loan-documents/8/file-1765204509721-984965595.jpg',44862,'image/jpeg','2025-12-08 14:35:09.745','2025-12-08 14:35:09.745','2025-12-08 14:35:09.745'),(65,8,26,'AGREEMENT','Loan_Agreement_MF-2025-0026_1765204509786.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0026_1765204509786.docx',10341,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:35:09.839','2025-12-08 14:35:09.839','2025-12-08 14:35:09.839'),(66,8,27,'PRIMARY_ID','Screenshot_20251208_204709.jpg','/uploads/loan-documents/8/file-1765204729292-563797185.jpg',340839,'image/jpeg','2025-12-08 14:38:49.479','2025-12-08 14:38:49.479','2025-12-08 14:38:49.479'),(67,8,27,'PRIMARY_ID','Screenshot_20251208_191010.jpg','/uploads/loan-documents/8/file-1765204729524-250575802.jpg',162467,'image/jpeg','2025-12-08 14:38:49.587','2025-12-08 14:38:49.587','2025-12-08 14:38:49.587'),(68,8,27,'SECONDARY_ID','Screenshot_20251207_174651.jpg','/uploads/loan-documents/8/file-1765204729623-6163814.jpg',285696,'image/jpeg','2025-12-08 14:38:49.742','2025-12-08 14:38:49.742','2025-12-08 14:38:49.742'),(69,8,27,'SECONDARY_ID','Screenshot_20251208_183319.jpg','/uploads/loan-documents/8/file-1765204729841-386173265.jpg',137595,'image/jpeg','2025-12-08 14:38:49.917','2025-12-08 14:38:49.917','2025-12-08 14:38:49.917'),(70,8,27,'AGREEMENT','Loan_Agreement_MF-2025-0027_1765204729959.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0027_1765204729959.docx',10341,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-08 14:38:50.025','2025-12-08 14:38:50.025','2025-12-08 14:38:50.025'),(71,8,28,'PRIMARY_ID','IMG-0103855aa4ea43c1e390df16b265bb6a-V.jpg','/uploads/loan-documents/8/file-1765277336147-460020706.jpg',585855,'image/jpeg','2025-12-09 10:48:56.373','2025-12-09 10:48:56.373','2025-12-09 10:48:56.373'),(72,8,28,'PRIMARY_ID','IMG-60e4bd593674cd44d8025b6ad31a3014-V.jpg','/uploads/loan-documents/8/file-1765277336410-143632694.jpg',571996,'image/jpeg','2025-12-09 10:48:56.652','2025-12-09 10:48:56.652','2025-12-09 10:48:56.652'),(73,8,28,'SECONDARY_ID','IMG_20251209_070438.jpg','/uploads/loan-documents/8/file-1765277336676-714380015.jpg',2662478,'image/jpeg','2025-12-09 10:48:57.532','2025-12-09 10:48:57.532','2025-12-09 10:48:57.532'),(74,8,28,'SECONDARY_ID','IMG-33573a2419408b6f5d93ef4833499672-V.jpg','/uploads/loan-documents/8/file-1765277337564-103653495.jpg',499118,'image/jpeg','2025-12-09 10:48:57.782','2025-12-09 10:48:57.782','2025-12-09 10:48:57.782'),(75,8,28,'AGREEMENT','Loan_Agreement_MF-2025-0028_1765277337815.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0028_1765277337815.docx',10341,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-09 10:48:57.874','2025-12-09 10:48:57.874','2025-12-09 10:48:57.874'),(76,8,29,'PRIMARY_ID','IMG-60e4bd593674cd44d8025b6ad31a3014-V.jpg','/uploads/loan-documents/8/file-1765279993282-937111022.jpg',571996,'image/jpeg','2025-12-09 11:33:13.588','2025-12-09 11:33:13.588','2025-12-09 11:33:13.588'),(77,8,29,'PRIMARY_ID','Screenshot_20251209_193210.jpg','/uploads/loan-documents/8/file-1765279993614-793629835.jpg',297385,'image/jpeg','2025-12-09 11:33:13.769','2025-12-09 11:33:13.769','2025-12-09 11:33:13.769'),(78,8,29,'SECONDARY_ID','IMG-60e4bd593674cd44d8025b6ad31a3014-V.jpg','/uploads/loan-documents/8/file-1765279993810-397662670.jpg',571996,'image/jpeg','2025-12-09 11:33:14.042','2025-12-09 11:33:14.042','2025-12-09 11:33:14.042'),(79,8,29,'SECONDARY_ID','Screenshot_20251208_222059.jpg','/uploads/loan-documents/8/file-1765279994063-212319088.jpg',143591,'image/jpeg','2025-12-09 11:33:14.121','2025-12-09 11:33:14.121','2025-12-09 11:33:14.121'),(80,8,30,'PRIMARY_ID','Screenshot_20251209_193326.jpg','/uploads/loan-documents/8/file-1765280343875-158458089.jpg',393429,'image/jpeg','2025-12-09 11:39:04.069','2025-12-09 11:39:04.069','2025-12-09 11:39:04.069'),(81,8,30,'PRIMARY_ID','Screenshot_20251209_193342.jpg','/uploads/loan-documents/8/file-1765280344106-362606684.jpg',274074,'image/jpeg','2025-12-09 11:39:04.249','2025-12-09 11:39:04.249','2025-12-09 11:39:04.249'),(82,8,30,'SECONDARY_ID','Screenshot_20251209_193333.jpg','/uploads/loan-documents/8/file-1765280344308-804262009.jpg',299188,'image/jpeg','2025-12-09 11:39:04.479','2025-12-09 11:39:04.479','2025-12-09 11:39:04.479'),(83,8,30,'SECONDARY_ID','Screenshot_20251209_193326.jpg','/uploads/loan-documents/8/file-1765280344522-778388898.jpg',393429,'image/jpeg','2025-12-09 11:39:04.701','2025-12-09 11:39:04.701','2025-12-09 11:39:04.701'),(84,8,31,'PRIMARY_ID','Screenshot_20251209_193342.jpg','/uploads/loan-documents/8/file-1765280571839-322132949.jpg',274074,'image/jpeg','2025-12-09 11:42:51.966','2025-12-09 11:42:51.966','2025-12-09 11:42:51.966'),(85,8,31,'PRIMARY_ID','Screenshot_20251209_193326.jpg','/uploads/loan-documents/8/file-1765280571988-2806352.jpg',393429,'image/jpeg','2025-12-09 11:42:52.166','2025-12-09 11:42:52.166','2025-12-09 11:42:52.166'),(86,8,31,'SECONDARY_ID','Screenshot_20251209_193326.jpg','/uploads/loan-documents/8/file-1765280572192-936767070.jpg',393429,'image/jpeg','2025-12-09 11:42:52.342','2025-12-09 11:42:52.342','2025-12-09 11:42:52.342'),(87,8,31,'SECONDARY_ID','Screenshot_20251209_193326.jpg','/uploads/loan-documents/8/file-1765280572370-931626678.jpg',393429,'image/jpeg','2025-12-09 11:42:52.523','2025-12-09 11:42:52.523','2025-12-09 11:42:52.523'),(88,8,31,'SIGNATURE','Screenshot_20251209_193342.jpg','/uploads/loan-documents/8/file-1765280572543-660379608.jpg',274074,'image/jpeg','2025-12-09 11:42:52.628','2025-12-09 11:42:52.628','2025-12-09 11:42:52.628'),(89,8,31,'PHOTO_2X2','Screenshot_20251209_193326.jpg','/uploads/loan-documents/8/file-1765280572651-332863578.jpg',393429,'image/jpeg','2025-12-09 11:42:52.763','2025-12-09 11:42:52.763','2025-12-09 11:42:52.763'),(90,8,31,'AGREEMENT','Loan_Agreement_MF-2025-0031_1765280572802.docx','/uploads/loan-documents/8/Loan_Agreement_MF-2025-0031_1765280572802.docx',10372,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-12-09 11:42:52.862','2025-12-09 11:42:52.862','2025-12-09 11:42:52.862');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guarantors`
--

DROP TABLE IF EXISTS `guarantors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guarantors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint unsigned NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guarantors_loan_id_unique` (`loan_id`),
  CONSTRAINT `guarantors_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guarantors`
--

LOCK TABLES `guarantors` WRITE;
/*!40000 ALTER TABLE `guarantors` DISABLE KEYS */;
/*!40000 ALTER TABLE `guarantors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `borrower_id` bigint unsigned DEFAULT NULL,
  `disbursement_account_id` bigint unsigned DEFAULT NULL,
  `borrower_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `principal_amount` decimal(14,2) NOT NULL,
  `interest_rate` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `application_date` date NOT NULL,
  `maturity_date` date DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `status` enum('new_application','under_review','approved','for_release','disbursed','closed','rejected','cancelled','restructured') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new_application',
  `total_disbursed` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_paid` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_penalties` decimal(14,2) NOT NULL DEFAULT '0.00',
  `penalty_grace_days` int unsigned NOT NULL DEFAULT '0',
  `penalty_daily_rate` decimal(7,6) NOT NULL DEFAULT '0.001000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_latitude` decimal(10,8) DEFAULT NULL,
  `application_longitude` decimal(11,8) DEFAULT NULL,
  `application_location_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` datetime(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`id`),
  UNIQUE KEY `loans_reference_key` (`reference`),
  KEY `loans_application_date_idx` (`application_date`),
  KEY `loans_release_date_idx` (`release_date`),
  KEY `loans_maturity_date_idx` (`maturity_date`),
  KEY `loans_status_idx` (`status`),
  KEY `loans_is_active_idx` (`is_active`),
  KEY `loans_status_is_active_idx` (`status`,`is_active`),
  KEY `loans_borrower_id_fkey` (`borrower_id`),
  CONSTRAINT `loans_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,'MF-2025-0001',4,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-10-31','2026-05-01',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-10-31 14:42:06.974','2025-11-19 11:32:36.464'),(2,'MF-2025-0002',5,NULL,'Jeon Jungkook ',13800.00,0.2400,'2025-10-31','2026-04-30',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-10-31 16:27:15.106','2025-10-31 17:00:02.749'),(3,'MF-2025-0003',5,NULL,'Jeon Jungkook ',13800.00,0.2400,'2025-10-31','2026-10-31',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-10-31 16:42:48.870','2025-10-31 16:51:43.995'),(4,'MF-2025-0004',5,NULL,'Jeon Jungkook ',13800.00,0.2400,'2025-10-31','2028-10-31',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-10-31 16:44:19.780','2025-10-31 16:51:47.790'),(5,'MF-2025-0005',5,NULL,'Jeon Jungkook ',13800.00,0.2400,'2025-10-31','2026-04-30',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-10-31 16:45:04.458','2025-10-31 16:51:50.518'),(6,'MF-2025-0006',5,NULL,'Jeon Jungkook ',13800.00,0.2400,'2025-10-31','2026-04-30',NULL,'new_application',0.00,0.00,0.00,0,0.001000,1,'Mobile app application',NULL,NULL,NULL,'2025-10-31 17:00:34.924','2025-10-31 17:00:34.924'),(7,'MF-2025-0007',6,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-11-17','2026-05-17',NULL,'new_application',0.00,0.00,0.00,0,0.001000,1,'Mobile app application',NULL,NULL,NULL,'2025-11-18 14:26:23.061','2025-11-18 14:26:23.061'),(8,'MF-2025-0008',4,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-11-18','2026-05-18','2025-11-29','disbursed',13800.00,2463.66,0.00,0,0.001000,1,'Mobile app application',NULL,NULL,NULL,'2025-11-19 11:33:40.867','2025-11-30 03:49:46.000'),(9,'MF-2025-0009',7,NULL,'Ariston',13800.00,0.2400,'2025-12-06','2026-06-06','2025-12-08','disbursed',13800.00,0.00,0.00,0,0.001000,1,'Mobile app application',NULL,NULL,NULL,'2025-12-07 04:24:20.150','2025-12-07 04:50:20.000'),(10,'MF-2025-0010',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:05:10.815','2025-12-08 05:08:18.713'),(11,'MF-2025-0011',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:16:51.610','2025-12-08 05:19:04.917'),(12,'MF-2025-0012',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:21:03.807','2025-12-08 05:23:21.074'),(13,'MF-2025-0013',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:23:43.888','2025-12-08 05:26:35.481'),(14,'MF-2025-0014',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:27:24.020','2025-12-08 05:28:34.110'),(15,'MF-2025-0015',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:29:28.172','2025-12-08 05:33:52.998'),(16,'MF-2025-0016',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:37:00.607','2025-12-08 05:44:46.040'),(17,'MF-2025-0017',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 05:46:43.332','2025-12-08 05:47:06.194'),(18,'MF-2025-0018',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 08:33:03.361','2025-12-08 09:31:02.981'),(19,'MF-2025-0019',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 11:44:52.159','2025-12-08 11:58:29.137'),(20,'MF-2025-0020',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 13:47:25.607','2025-12-08 13:48:50.427'),(21,'MF-2025-0021',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-07','2026-06-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:15:00.923','2025-12-08 14:15:18.825'),(22,'MF-2025-0022',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-07','2026-12-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:20:13.880','2025-12-08 14:23:05.301'),(23,'MF-2025-0023',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-07','2026-12-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:24:12.378','2025-12-08 14:24:31.143'),(24,'MF-2025-0024',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-07','2026-12-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:28:39.008','2025-12-08 14:31:03.103'),(25,'MF-2025-0025',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-07','2026-12-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:31:43.209','2025-12-08 14:34:29.535'),(26,'MF-2025-0026',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-07','2026-12-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:35:09.123','2025-12-08 14:38:12.331'),(27,'MF-2025-0027',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-07','2026-12-07',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-08 14:38:49.045','2025-12-09 10:47:40.004'),(28,'MF-2025-0028',8,NULL,'ARISTON CARAGAY',50000.00,0.2400,'2025-12-08','2026-06-08',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-09 10:48:55.903','2025-12-09 11:04:21.502'),(29,'MF-2025-0029',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-08','2026-04-08',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-09 11:33:13.037','2025-12-09 11:38:21.414'),(30,'MF-2025-0030',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-08','2026-04-08',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-09 11:39:03.660','2025-12-09 11:39:24.687'),(31,'MF-2025-0031',8,NULL,'ARISTON CARAGAY',13800.00,0.2400,'2025-12-08','2026-04-08',NULL,'cancelled',0.00,0.00,0.00,0,0.001000,0,'Mobile app application',NULL,NULL,NULL,'2025-12-09 11:42:51.664','2025-12-13 01:10:12.924');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2025_11_29_084124_create_payments_table',1),(2,'2025_11_30_115753_create_support_messages_table',2),(3,'2025_12_13_000001_add_location_and_guarantor_to_loans',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `borrower_id` bigint unsigned NOT NULL,
  `loan_id` bigint unsigned DEFAULT NULL,
  `type` enum('info','reminder','approval','payment_received','payment_due','loan_status_change') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_borrower_id_idx` (`borrower_id`),
  KEY `notifications_loan_id_idx` (`loan_id`),
  KEY `notifications_is_read_idx` (`is_read`),
  KEY `notifications_created_at_idx` (`created_at`),
  CONSTRAINT `notifications_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notifications_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,4,8,'loan_status_change','Loan Under Review','Your loan application MF-2025-0008 is now under review. We will notify you once a decision has been made.',1,'2025-11-30 11:18:41','2025-11-29 08:23:08.000','2025-11-30 11:18:41.389'),(2,4,8,'approval','Loan Approved! ?','Congratulations! Your loan application MF-2025-0008 has been approved. It will be processed for release soon.',0,NULL,'2025-11-29 08:23:34.000','2025-11-29 08:23:34.000'),(3,4,8,'loan_status_change','Loan Ready for Release','Your loan application MF-2025-0008 is ready for release. It will be disbursed soon.',1,'2025-11-30 11:26:46','2025-11-29 08:25:08.000','2025-11-30 11:26:46.278'),(4,4,8,'approval','Loan Disbursed! ?','Your loan MF-2025-0008 has been disbursed on November 29, 2025. Amount: ₱13,800.00. Please check your account and start making payments according to your schedule.',1,'2025-11-29 08:26:02','2025-11-29 08:25:50.000','2025-11-29 08:26:02.045'),(5,4,8,'loan_status_change','Loan Under Review','Your loan application MF-2025-0008 is now under review. We will notify you once a decision has been made.',0,NULL,'2025-11-29 08:28:29.000','2025-11-29 08:28:29.000'),(6,4,8,'approval','Loan Disbursed! ?','Your loan MF-2025-0008 has been disbursed on November 29, 2025. Amount: ₱13,800.00. Please check your account and start making payments according to your schedule.',0,NULL,'2025-11-29 08:33:30.000','2025-11-29 08:33:30.000'),(7,4,8,'loan_status_change','Loan Under Review','Your loan application MF-2025-0008 is now under review. We will notify you once a decision has been made.',0,NULL,'2025-11-29 13:36:16.000','2025-11-29 13:36:16.000'),(8,4,8,'approval','Loan Approved! ?','Congratulations! Your loan application MF-2025-0008 has been approved. It will be processed for release soon.',0,NULL,'2025-11-29 13:38:17.000','2025-11-29 13:38:17.000'),(9,4,8,'loan_status_change','Loan Ready for Release','Your loan application MF-2025-0008 is ready for release. It will be disbursed soon.',0,NULL,'2025-11-29 13:41:51.000','2025-11-29 13:41:51.000'),(10,4,8,'approval','Loan Disbursed! ?','Your loan MF-2025-0008 has been disbursed on November 29, 2025. Amount: ₱13,800.00. Please check your account and start making payments according to your schedule.',1,'2025-11-30 11:18:30','2025-11-29 13:42:01.000','2025-11-30 11:18:30.153'),(11,4,8,'info','Payment Submitted','Your payment of ₱2,463.66 for loan MF-2025-0008 has been submitted and is pending admin approval.',1,'2025-11-30 11:18:29','2025-11-30 03:18:19.875','2025-11-30 11:18:29.202'),(12,4,8,'payment_received','Payment Approved','Your payment of ₱2,463.66 for loan MF-2025-0008 has been approved and recorded.',1,'2025-11-30 11:18:27','2025-11-30 03:49:47.000','2025-11-30 11:18:27.296'),(13,4,NULL,'info','Support Response','We have responded to your support message: Hello\n\nResponse: gvhjb,knl',1,'2025-11-30 11:59:54','2025-11-30 11:59:46.000','2025-11-30 11:59:53.571'),(14,7,9,'info','Loan Application Submitted','Your loan application MF-2025-0009 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-07 04:24:20.284','2025-12-07 04:24:20.284'),(15,7,9,'loan_status_change','Loan Under Review','Your loan application MF-2025-0009 is now under review. We will notify you once a decision has been made.',0,NULL,'2025-12-07 04:45:36.000','2025-12-07 04:45:36.000'),(16,7,9,'approval','Loan Approved! ?','Congratulations! Your loan application MF-2025-0009 has been approved. It will be processed for release soon.',0,NULL,'2025-12-07 04:48:53.000','2025-12-07 04:48:53.000'),(17,7,9,'approval','Loan Disbursed! ?','Your loan MF-2025-0009 has been disbursed on December 07, 2025. Amount: ₱13,800.00. Please check your account and start making payments according to your schedule.',1,'2025-12-07 05:02:58','2025-12-07 04:49:23.000','2025-12-07 05:02:58.208'),(18,8,10,'info','Loan Application Submitted','Your loan application MF-2025-0010 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',1,'2025-12-08 07:31:59','2025-12-08 05:05:10.896','2025-12-08 07:31:58.572'),(19,8,11,'info','Loan Application Submitted','Your loan application MF-2025-0011 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 05:16:51.723','2025-12-08 05:16:51.723'),(20,8,12,'info','Loan Application Submitted','Your loan application MF-2025-0012 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 05:21:03.860','2025-12-08 05:21:03.860'),(21,8,13,'info','Loan Application Submitted','Your loan application MF-2025-0013 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 05:23:43.897','2025-12-08 05:23:43.897'),(22,8,14,'info','Loan Application Submitted','Your loan application MF-2025-0014 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 05:27:24.040','2025-12-08 05:27:24.040'),(23,8,15,'info','Loan Application Submitted','Your loan application MF-2025-0015 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 05:29:28.186','2025-12-08 05:29:28.186'),(24,8,16,'info','Loan Application Submitted','Your loan application MF-2025-0016 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 05:37:00.738','2025-12-08 05:37:00.738'),(25,8,17,'info','Loan Application Submitted','Your loan application MF-2025-0017 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',1,'2025-12-08 07:02:29','2025-12-08 05:46:43.358','2025-12-08 07:02:28.669'),(26,8,18,'info','Loan Application Submitted','Your loan application MF-2025-0018 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 08:33:03.403','2025-12-08 08:33:03.403'),(27,8,19,'info','Loan Application Submitted','Your loan application MF-2025-0019 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 11:44:52.188','2025-12-08 11:44:52.188'),(28,8,20,'info','Loan Application Submitted','Your loan application MF-2025-0020 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 13:47:25.707','2025-12-08 13:47:25.707'),(29,8,21,'info','Loan Application Submitted','Your loan application MF-2025-0021 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:15:00.965','2025-12-08 14:15:00.965'),(30,8,22,'info','Loan Application Submitted','Your loan application MF-2025-0022 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:20:13.924','2025-12-08 14:20:13.924'),(31,8,23,'info','Loan Application Submitted','Your loan application MF-2025-0023 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:24:12.531','2025-12-08 14:24:12.531'),(32,8,24,'info','Loan Application Submitted','Your loan application MF-2025-0024 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:28:39.044','2025-12-08 14:28:39.044'),(33,8,25,'info','Loan Application Submitted','Your loan application MF-2025-0025 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:31:43.230','2025-12-08 14:31:43.230'),(34,8,26,'info','Loan Application Submitted','Your loan application MF-2025-0026 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:35:09.145','2025-12-08 14:35:09.145'),(35,8,27,'info','Loan Application Submitted','Your loan application MF-2025-0027 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-08 14:38:49.085','2025-12-08 14:38:49.085'),(36,8,28,'info','Loan Application Submitted','Your loan application MF-2025-0028 for ₱50,000.00 has been submitted successfully. We will review it and notify you of the status.',1,'2025-12-09 10:49:59','2025-12-09 10:48:55.919','2025-12-09 10:49:59.149'),(37,8,29,'info','Loan Application Submitted','Your loan application MF-2025-0029 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-09 11:33:13.075','2025-12-09 11:33:13.075'),(38,8,30,'info','Loan Application Submitted','Your loan application MF-2025-0030 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-09 11:39:03.704','2025-12-09 11:39:03.704'),(39,8,31,'info','Loan Application Submitted','Your loan application MF-2025-0031 for ₱13,800.00 has been submitted successfully. We will review it and notify you of the status.',0,NULL,'2025-12-09 11:42:51.677','2025-12-09 11:42:51.677');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint unsigned NOT NULL,
  `borrower_id` bigint unsigned NOT NULL,
  `repayment_id` bigint unsigned DEFAULT NULL,
  `receipt_document_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `penalty_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `approved_by_user_id` bigint unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_repayment_id_foreign` (`repayment_id`),
  KEY `payments_receipt_document_id_foreign` (`receipt_document_id`),
  KEY `payments_approved_by_user_id_foreign` (`approved_by_user_id`),
  KEY `payments_loan_id_status_index` (`loan_id`,`status`),
  KEY `payments_borrower_id_status_index` (`borrower_id`,`status`),
  KEY `payments_status_index` (`status`),
  KEY `payments_paid_at_index` (`paid_at`),
  CONSTRAINT `payments_approved_by_user_id_foreign` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_borrower_id_foreign` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_receipt_document_id_foreign` FOREIGN KEY (`receipt_document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_repayment_id_foreign` FOREIGN KEY (`repayment_id`) REFERENCES `repayments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,8,4,79,3,2463.66,0.00,'approved',NULL,NULL,1,'2025-11-29 19:18:20','2025-11-29 19:49:47','2025-11-29 19:18:20','2025-11-29 19:49:47');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_key_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'View Loans','loans.view','Loans','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(2,'Create Loans','loans.create','Loans','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(3,'Edit Loans','loans.edit','Loans','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(4,'Delete Loans','loans.delete','Loans','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(5,'Approve Loans','loans.approve','Loans','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(6,'Disburse Loans','loans.disburse','Loans','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(7,'View Borrowers','borrowers.view','Borrowers','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(8,'Create Borrowers','borrowers.create','Borrowers','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(9,'Edit Borrowers','borrowers.edit','Borrowers','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(10,'Delete Borrowers','borrowers.delete','Borrowers','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(11,'View Payments','payments.view','Payments','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(12,'Approve Payments','payments.approve','Payments','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(13,'Reject Payments','payments.reject','Payments','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(14,'Record Payments','payments.record','Payments','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(15,'View Reports','reports.view','Reports','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(16,'Export Reports','reports.export','Reports','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(17,'Manage Settings','settings.manage','Settings','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(18,'Manage Roles','settings.roles','Settings','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(19,'Manage Chart of Accounts','settings.coa','Settings','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(20,'View Bank Transactions','bank.view','Bank','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(21,'Create Bank Transactions','bank.create','Bank','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(22,'Edit Bank Transactions','bank.edit','Bank','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repayments`
--

DROP TABLE IF EXISTS `repayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repayments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint unsigned NOT NULL,
  `due_date` date NOT NULL,
  `amount_due` decimal(14,2) NOT NULL,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT '0.00',
  `paid_at` datetime DEFAULT NULL,
  `penalty_applied` decimal(14,2) NOT NULL DEFAULT '0.00',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` datetime(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`id`),
  KEY `repayments_loan_id_due_date_idx` (`loan_id`,`due_date`),
  KEY `repayments_due_date_idx` (`due_date`),
  KEY `repayments_loan_id_fkey` (`loan_id`),
  CONSTRAINT `repayments_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repayments`
--

LOCK TABLES `repayments` WRITE;
/*!40000 ALTER TABLE `repayments` DISABLE KEYS */;
INSERT INTO `repayments` VALUES (1,1,'2025-12-01',2463.66,0.00,NULL,0.00,'First payment','2025-10-31 14:42:07.047','2025-10-31 14:42:07.047'),(2,1,'2025-12-31',2463.66,0.00,NULL,0.00,'Payment 2','2025-10-31 14:42:07.047','2025-10-31 14:42:07.047'),(3,1,'2026-01-31',2463.66,0.00,NULL,0.00,'Payment 3','2025-10-31 14:42:07.047','2025-10-31 14:42:07.047'),(4,1,'2026-03-03',2463.66,0.00,NULL,0.00,'Payment 4','2025-10-31 14:42:07.047','2025-10-31 14:42:07.047'),(5,1,'2026-03-31',2463.66,0.00,NULL,0.00,'Payment 5','2025-10-31 14:42:07.047','2025-10-31 14:42:07.047'),(6,1,'2026-05-01',2463.66,0.00,NULL,0.00,'Payment 6','2025-10-31 14:42:07.047','2025-10-31 14:42:07.047'),(7,2,'2025-11-30',2463.66,0.00,NULL,0.00,'First payment','2025-10-31 16:27:15.159','2025-10-31 16:27:15.159'),(8,2,'2025-12-31',2463.66,0.00,NULL,0.00,'Payment 2','2025-10-31 16:27:15.159','2025-10-31 16:27:15.159'),(9,2,'2026-01-31',2463.66,0.00,NULL,0.00,'Payment 3','2025-10-31 16:27:15.159','2025-10-31 16:27:15.159'),(10,2,'2026-02-28',2463.66,0.00,NULL,0.00,'Payment 4','2025-10-31 16:27:15.159','2025-10-31 16:27:15.159'),(11,2,'2026-03-31',2463.66,0.00,NULL,0.00,'Payment 5','2025-10-31 16:27:15.159','2025-10-31 16:27:15.159'),(12,2,'2026-04-30',2463.66,0.00,NULL,0.00,'Payment 6','2025-10-31 16:27:15.159','2025-10-31 16:27:15.159'),(13,3,'2025-11-30',1304.92,0.00,NULL,0.00,'First payment','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(14,3,'2025-12-31',1304.92,0.00,NULL,0.00,'Payment 2','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(15,3,'2026-01-31',1304.92,0.00,NULL,0.00,'Payment 3','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(16,3,'2026-02-28',1304.92,0.00,NULL,0.00,'Payment 4','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(17,3,'2026-03-31',1304.92,0.00,NULL,0.00,'Payment 5','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(18,3,'2026-04-30',1304.92,0.00,NULL,0.00,'Payment 6','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(19,3,'2026-05-31',1304.92,0.00,NULL,0.00,'Payment 7','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(20,3,'2026-06-30',1304.92,0.00,NULL,0.00,'Payment 8','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(21,3,'2026-07-31',1304.92,0.00,NULL,0.00,'Payment 9','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(22,3,'2026-08-31',1304.92,0.00,NULL,0.00,'Payment 10','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(23,3,'2026-09-30',1304.92,0.00,NULL,0.00,'Payment 11','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(24,3,'2026-10-31',1304.92,0.00,NULL,0.00,'Payment 12','2025-10-31 16:42:48.907','2025-10-31 16:42:48.907'),(25,4,'2025-11-30',541.41,0.00,NULL,0.00,'First payment','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(26,4,'2025-12-31',541.41,0.00,NULL,0.00,'Payment 2','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(27,4,'2026-01-31',541.41,0.00,NULL,0.00,'Payment 3','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(28,4,'2026-02-28',541.41,0.00,NULL,0.00,'Payment 4','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(29,4,'2026-03-31',541.41,0.00,NULL,0.00,'Payment 5','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(30,4,'2026-04-30',541.41,0.00,NULL,0.00,'Payment 6','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(31,4,'2026-05-31',541.41,0.00,NULL,0.00,'Payment 7','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(32,4,'2026-06-30',541.41,0.00,NULL,0.00,'Payment 8','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(33,4,'2026-07-31',541.41,0.00,NULL,0.00,'Payment 9','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(34,4,'2026-08-31',541.41,0.00,NULL,0.00,'Payment 10','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(35,4,'2026-09-30',541.41,0.00,NULL,0.00,'Payment 11','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(36,4,'2026-10-31',541.41,0.00,NULL,0.00,'Payment 12','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(37,4,'2026-11-30',541.41,0.00,NULL,0.00,'Payment 13','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(38,4,'2026-12-31',541.41,0.00,NULL,0.00,'Payment 14','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(39,4,'2027-01-31',541.41,0.00,NULL,0.00,'Payment 15','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(40,4,'2027-02-28',541.41,0.00,NULL,0.00,'Payment 16','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(41,4,'2027-03-31',541.41,0.00,NULL,0.00,'Payment 17','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(42,4,'2027-04-30',541.41,0.00,NULL,0.00,'Payment 18','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(43,4,'2027-05-31',541.41,0.00,NULL,0.00,'Payment 19','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(44,4,'2027-06-30',541.41,0.00,NULL,0.00,'Payment 20','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(45,4,'2027-07-31',541.41,0.00,NULL,0.00,'Payment 21','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(46,4,'2027-08-31',541.41,0.00,NULL,0.00,'Payment 22','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(47,4,'2027-09-30',541.41,0.00,NULL,0.00,'Payment 23','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(48,4,'2027-10-31',541.41,0.00,NULL,0.00,'Payment 24','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(49,4,'2027-11-30',541.41,0.00,NULL,0.00,'Payment 25','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(50,4,'2027-12-31',541.41,0.00,NULL,0.00,'Payment 26','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(51,4,'2028-01-31',541.41,0.00,NULL,0.00,'Payment 27','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(52,4,'2028-02-29',541.41,0.00,NULL,0.00,'Payment 28','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(53,4,'2028-03-31',541.41,0.00,NULL,0.00,'Payment 29','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(54,4,'2028-04-30',541.41,0.00,NULL,0.00,'Payment 30','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(55,4,'2028-05-31',541.41,0.00,NULL,0.00,'Payment 31','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(56,4,'2028-06-30',541.41,0.00,NULL,0.00,'Payment 32','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(57,4,'2028-07-31',541.41,0.00,NULL,0.00,'Payment 33','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(58,4,'2028-08-31',541.41,0.00,NULL,0.00,'Payment 34','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(59,4,'2028-09-30',541.41,0.00,NULL,0.00,'Payment 35','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(60,4,'2028-10-31',541.41,0.00,NULL,0.00,'Payment 36','2025-10-31 16:44:19.787','2025-10-31 16:44:19.787'),(61,5,'2025-11-30',2463.66,0.00,NULL,0.00,'First payment','2025-10-31 16:45:04.464','2025-10-31 16:45:04.464'),(62,5,'2025-12-31',2463.66,0.00,NULL,0.00,'Payment 2','2025-10-31 16:45:04.464','2025-10-31 16:45:04.464'),(63,5,'2026-01-31',2463.66,0.00,NULL,0.00,'Payment 3','2025-10-31 16:45:04.464','2025-10-31 16:45:04.464'),(64,5,'2026-02-28',2463.66,0.00,NULL,0.00,'Payment 4','2025-10-31 16:45:04.464','2025-10-31 16:45:04.464'),(65,5,'2026-03-31',2463.66,0.00,NULL,0.00,'Payment 5','2025-10-31 16:45:04.464','2025-10-31 16:45:04.464'),(66,5,'2026-04-30',2463.66,0.00,NULL,0.00,'Payment 6','2025-10-31 16:45:04.464','2025-10-31 16:45:04.464'),(67,6,'2025-11-30',2463.66,0.00,NULL,0.00,'First payment','2025-10-31 17:00:34.942','2025-10-31 17:00:34.942'),(68,6,'2025-12-31',2463.66,0.00,NULL,0.00,'Payment 2','2025-10-31 17:00:34.942','2025-10-31 17:00:34.942'),(69,6,'2026-01-31',2463.66,0.00,NULL,0.00,'Payment 3','2025-10-31 17:00:34.942','2025-10-31 17:00:34.942'),(70,6,'2026-02-28',2463.66,0.00,NULL,0.00,'Payment 4','2025-10-31 17:00:34.942','2025-10-31 17:00:34.942'),(71,6,'2026-03-31',2463.66,0.00,NULL,0.00,'Payment 5','2025-10-31 17:00:34.942','2025-10-31 17:00:34.942'),(72,6,'2026-04-30',2463.66,0.00,NULL,0.00,'Payment 6','2025-10-31 17:00:34.942','2025-10-31 17:00:34.942'),(73,7,'2025-12-17',2463.66,0.00,NULL,0.00,'First payment','2025-11-18 14:26:23.073','2025-11-18 14:26:23.073'),(74,7,'2026-01-17',2463.66,0.00,NULL,0.00,'Payment 2','2025-11-18 14:26:23.073','2025-11-18 14:26:23.073'),(75,7,'2026-02-17',2463.66,0.00,NULL,0.00,'Payment 3','2025-11-18 14:26:23.073','2025-11-18 14:26:23.073'),(76,7,'2026-03-17',2463.66,0.00,NULL,0.00,'Payment 4','2025-11-18 14:26:23.073','2025-11-18 14:26:23.073'),(77,7,'2026-04-17',2463.66,0.00,NULL,0.00,'Payment 5','2025-11-18 14:26:23.073','2025-11-18 14:26:23.073'),(78,7,'2026-05-17',2463.66,0.00,NULL,0.00,'Payment 6','2025-11-18 14:26:23.073','2025-11-18 14:26:23.073'),(79,8,'2025-12-18',2463.66,2463.66,'2025-11-30 03:18:20',0.00,'First payment','2025-11-19 11:33:40.881','2025-11-30 03:49:46.000'),(80,8,'2026-01-18',2463.66,0.00,NULL,0.00,'Payment 2','2025-11-19 11:33:40.881','2025-11-19 11:33:40.881'),(81,8,'2026-02-18',2463.66,0.00,NULL,0.00,'Payment 3','2025-11-19 11:33:40.881','2025-11-19 11:33:40.881'),(82,8,'2026-03-18',2463.66,0.00,NULL,0.00,'Payment 4','2025-11-19 11:33:40.881','2025-11-19 11:33:40.881'),(83,8,'2026-04-18',2463.66,0.00,NULL,0.00,'Payment 5','2025-11-19 11:33:40.881','2025-11-19 11:33:40.881'),(84,8,'2026-05-18',2463.66,0.00,NULL,0.00,'Payment 6','2025-11-19 11:33:40.881','2025-11-19 11:33:40.881'),(85,9,'2026-01-06',2463.66,0.00,NULL,0.00,'First payment','2025-12-07 04:24:20.174','2025-12-07 04:24:20.174'),(86,9,'2026-02-06',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-07 04:24:20.174','2025-12-07 04:24:20.174'),(87,9,'2026-03-06',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-07 04:24:20.174','2025-12-07 04:24:20.174'),(88,9,'2026-04-06',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-07 04:24:20.174','2025-12-07 04:24:20.174'),(89,9,'2026-05-06',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-07 04:24:20.174','2025-12-07 04:24:20.174'),(90,9,'2026-06-06',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-07 04:24:20.174','2025-12-07 04:24:20.174'),(91,10,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:05:10.826','2025-12-08 05:05:10.826'),(92,10,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:05:10.826','2025-12-08 05:05:10.826'),(93,10,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:05:10.826','2025-12-08 05:05:10.826'),(94,10,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:05:10.826','2025-12-08 05:05:10.826'),(95,10,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:05:10.826','2025-12-08 05:05:10.826'),(96,10,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:05:10.826','2025-12-08 05:05:10.826'),(97,11,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:16:51.653','2025-12-08 05:16:51.653'),(98,11,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:16:51.653','2025-12-08 05:16:51.653'),(99,11,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:16:51.653','2025-12-08 05:16:51.653'),(100,11,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:16:51.653','2025-12-08 05:16:51.653'),(101,11,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:16:51.653','2025-12-08 05:16:51.653'),(102,11,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:16:51.653','2025-12-08 05:16:51.653'),(103,12,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:21:03.818','2025-12-08 05:21:03.818'),(104,12,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:21:03.818','2025-12-08 05:21:03.818'),(105,12,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:21:03.818','2025-12-08 05:21:03.818'),(106,12,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:21:03.818','2025-12-08 05:21:03.818'),(107,12,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:21:03.818','2025-12-08 05:21:03.818'),(108,12,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:21:03.818','2025-12-08 05:21:03.818'),(109,13,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:23:43.893','2025-12-08 05:23:43.893'),(110,13,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:23:43.893','2025-12-08 05:23:43.893'),(111,13,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:23:43.893','2025-12-08 05:23:43.893'),(112,13,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:23:43.893','2025-12-08 05:23:43.893'),(113,13,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:23:43.893','2025-12-08 05:23:43.893'),(114,13,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:23:43.893','2025-12-08 05:23:43.893'),(115,14,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:27:24.030','2025-12-08 05:27:24.030'),(116,14,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:27:24.030','2025-12-08 05:27:24.030'),(117,14,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:27:24.030','2025-12-08 05:27:24.030'),(118,14,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:27:24.030','2025-12-08 05:27:24.030'),(119,14,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:27:24.030','2025-12-08 05:27:24.030'),(120,14,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:27:24.030','2025-12-08 05:27:24.030'),(121,15,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:29:28.180','2025-12-08 05:29:28.180'),(122,15,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:29:28.180','2025-12-08 05:29:28.180'),(123,15,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:29:28.180','2025-12-08 05:29:28.180'),(124,15,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:29:28.180','2025-12-08 05:29:28.180'),(125,15,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:29:28.180','2025-12-08 05:29:28.180'),(126,15,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:29:28.180','2025-12-08 05:29:28.180'),(127,16,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:37:00.622','2025-12-08 05:37:00.622'),(128,16,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:37:00.622','2025-12-08 05:37:00.622'),(129,16,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:37:00.622','2025-12-08 05:37:00.622'),(130,16,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:37:00.622','2025-12-08 05:37:00.622'),(131,16,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:37:00.622','2025-12-08 05:37:00.622'),(132,16,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:37:00.622','2025-12-08 05:37:00.622'),(133,17,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 05:46:43.348','2025-12-08 05:46:43.348'),(134,17,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 05:46:43.348','2025-12-08 05:46:43.348'),(135,17,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 05:46:43.348','2025-12-08 05:46:43.348'),(136,17,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 05:46:43.348','2025-12-08 05:46:43.348'),(137,17,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 05:46:43.348','2025-12-08 05:46:43.348'),(138,17,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 05:46:43.348','2025-12-08 05:46:43.348'),(139,18,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 08:33:03.386','2025-12-08 08:33:03.386'),(140,18,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 08:33:03.386','2025-12-08 08:33:03.386'),(141,18,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 08:33:03.386','2025-12-08 08:33:03.386'),(142,18,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 08:33:03.386','2025-12-08 08:33:03.386'),(143,18,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 08:33:03.386','2025-12-08 08:33:03.386'),(144,18,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 08:33:03.386','2025-12-08 08:33:03.386'),(145,19,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 11:44:52.176','2025-12-08 11:44:52.176'),(146,19,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 11:44:52.176','2025-12-08 11:44:52.176'),(147,19,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 11:44:52.176','2025-12-08 11:44:52.176'),(148,19,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 11:44:52.176','2025-12-08 11:44:52.176'),(149,19,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 11:44:52.176','2025-12-08 11:44:52.176'),(150,19,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 11:44:52.176','2025-12-08 11:44:52.176'),(151,20,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 13:47:25.625','2025-12-08 13:47:25.625'),(152,20,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 13:47:25.625','2025-12-08 13:47:25.625'),(153,20,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 13:47:25.625','2025-12-08 13:47:25.625'),(154,20,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 13:47:25.625','2025-12-08 13:47:25.625'),(155,20,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 13:47:25.625','2025-12-08 13:47:25.625'),(156,20,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 13:47:25.625','2025-12-08 13:47:25.625'),(157,21,'2026-01-07',2463.66,0.00,NULL,0.00,'First payment','2025-12-08 14:15:00.949','2025-12-08 14:15:00.949'),(158,21,'2026-02-07',2463.66,0.00,NULL,0.00,'Payment 2','2025-12-08 14:15:00.949','2025-12-08 14:15:00.949'),(159,21,'2026-03-07',2463.66,0.00,NULL,0.00,'Payment 3','2025-12-08 14:15:00.949','2025-12-08 14:15:00.949'),(160,21,'2026-04-07',2463.66,0.00,NULL,0.00,'Payment 4','2025-12-08 14:15:00.949','2025-12-08 14:15:00.949'),(161,21,'2026-05-07',2463.66,0.00,NULL,0.00,'Payment 5','2025-12-08 14:15:00.949','2025-12-08 14:15:00.949'),(162,21,'2026-06-07',2463.66,0.00,NULL,0.00,'Payment 6','2025-12-08 14:15:00.949','2025-12-08 14:15:00.949'),(163,22,'2026-01-07',4727.98,0.00,NULL,0.00,'First payment','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(164,22,'2026-02-07',4727.98,0.00,NULL,0.00,'Payment 2','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(165,22,'2026-03-07',4727.98,0.00,NULL,0.00,'Payment 3','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(166,22,'2026-04-07',4727.98,0.00,NULL,0.00,'Payment 4','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(167,22,'2026-05-07',4727.98,0.00,NULL,0.00,'Payment 5','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(168,22,'2026-06-07',4727.98,0.00,NULL,0.00,'Payment 6','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(169,22,'2026-07-07',4727.98,0.00,NULL,0.00,'Payment 7','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(170,22,'2026-08-07',4727.98,0.00,NULL,0.00,'Payment 8','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(171,22,'2026-09-07',4727.98,0.00,NULL,0.00,'Payment 9','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(172,22,'2026-10-07',4727.98,0.00,NULL,0.00,'Payment 10','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(173,22,'2026-11-07',4727.98,0.00,NULL,0.00,'Payment 11','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(174,22,'2026-12-07',4727.98,0.00,NULL,0.00,'Payment 12','2025-12-08 14:20:13.902','2025-12-08 14:20:13.902'),(175,23,'2026-01-07',4727.98,0.00,NULL,0.00,'First payment','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(176,23,'2026-02-07',4727.98,0.00,NULL,0.00,'Payment 2','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(177,23,'2026-03-07',4727.98,0.00,NULL,0.00,'Payment 3','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(178,23,'2026-04-07',4727.98,0.00,NULL,0.00,'Payment 4','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(179,23,'2026-05-07',4727.98,0.00,NULL,0.00,'Payment 5','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(180,23,'2026-06-07',4727.98,0.00,NULL,0.00,'Payment 6','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(181,23,'2026-07-07',4727.98,0.00,NULL,0.00,'Payment 7','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(182,23,'2026-08-07',4727.98,0.00,NULL,0.00,'Payment 8','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(183,23,'2026-09-07',4727.98,0.00,NULL,0.00,'Payment 9','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(184,23,'2026-10-07',4727.98,0.00,NULL,0.00,'Payment 10','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(185,23,'2026-11-07',4727.98,0.00,NULL,0.00,'Payment 11','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(186,23,'2026-12-07',4727.98,0.00,NULL,0.00,'Payment 12','2025-12-08 14:24:12.404','2025-12-08 14:24:12.404'),(187,24,'2026-01-07',4727.98,0.00,NULL,0.00,'First payment','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(188,24,'2026-02-07',4727.98,0.00,NULL,0.00,'Payment 2','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(189,24,'2026-03-07',4727.98,0.00,NULL,0.00,'Payment 3','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(190,24,'2026-04-07',4727.98,0.00,NULL,0.00,'Payment 4','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(191,24,'2026-05-07',4727.98,0.00,NULL,0.00,'Payment 5','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(192,24,'2026-06-07',4727.98,0.00,NULL,0.00,'Payment 6','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(193,24,'2026-07-07',4727.98,0.00,NULL,0.00,'Payment 7','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(194,24,'2026-08-07',4727.98,0.00,NULL,0.00,'Payment 8','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(195,24,'2026-09-07',4727.98,0.00,NULL,0.00,'Payment 9','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(196,24,'2026-10-07',4727.98,0.00,NULL,0.00,'Payment 10','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(197,24,'2026-11-07',4727.98,0.00,NULL,0.00,'Payment 11','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(198,24,'2026-12-07',4727.98,0.00,NULL,0.00,'Payment 12','2025-12-08 14:28:39.026','2025-12-08 14:28:39.026'),(199,25,'2026-01-07',4727.98,0.00,NULL,0.00,'First payment','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(200,25,'2026-02-07',4727.98,0.00,NULL,0.00,'Payment 2','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(201,25,'2026-03-07',4727.98,0.00,NULL,0.00,'Payment 3','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(202,25,'2026-04-07',4727.98,0.00,NULL,0.00,'Payment 4','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(203,25,'2026-05-07',4727.98,0.00,NULL,0.00,'Payment 5','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(204,25,'2026-06-07',4727.98,0.00,NULL,0.00,'Payment 6','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(205,25,'2026-07-07',4727.98,0.00,NULL,0.00,'Payment 7','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(206,25,'2026-08-07',4727.98,0.00,NULL,0.00,'Payment 8','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(207,25,'2026-09-07',4727.98,0.00,NULL,0.00,'Payment 9','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(208,25,'2026-10-07',4727.98,0.00,NULL,0.00,'Payment 10','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(209,25,'2026-11-07',4727.98,0.00,NULL,0.00,'Payment 11','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(210,25,'2026-12-07',4727.98,0.00,NULL,0.00,'Payment 12','2025-12-08 14:31:43.218','2025-12-08 14:31:43.218'),(211,26,'2026-01-07',4727.98,0.00,NULL,0.00,'First payment','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(212,26,'2026-02-07',4727.98,0.00,NULL,0.00,'Payment 2','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(213,26,'2026-03-07',4727.98,0.00,NULL,0.00,'Payment 3','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(214,26,'2026-04-07',4727.98,0.00,NULL,0.00,'Payment 4','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(215,26,'2026-05-07',4727.98,0.00,NULL,0.00,'Payment 5','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(216,26,'2026-06-07',4727.98,0.00,NULL,0.00,'Payment 6','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(217,26,'2026-07-07',4727.98,0.00,NULL,0.00,'Payment 7','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(218,26,'2026-08-07',4727.98,0.00,NULL,0.00,'Payment 8','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(219,26,'2026-09-07',4727.98,0.00,NULL,0.00,'Payment 9','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(220,26,'2026-10-07',4727.98,0.00,NULL,0.00,'Payment 10','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(221,26,'2026-11-07',4727.98,0.00,NULL,0.00,'Payment 11','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(222,26,'2026-12-07',4727.98,0.00,NULL,0.00,'Payment 12','2025-12-08 14:35:09.135','2025-12-08 14:35:09.135'),(223,27,'2026-01-07',4727.98,0.00,NULL,0.00,'First payment','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(224,27,'2026-02-07',4727.98,0.00,NULL,0.00,'Payment 2','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(225,27,'2026-03-07',4727.98,0.00,NULL,0.00,'Payment 3','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(226,27,'2026-04-07',4727.98,0.00,NULL,0.00,'Payment 4','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(227,27,'2026-05-07',4727.98,0.00,NULL,0.00,'Payment 5','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(228,27,'2026-06-07',4727.98,0.00,NULL,0.00,'Payment 6','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(229,27,'2026-07-07',4727.98,0.00,NULL,0.00,'Payment 7','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(230,27,'2026-08-07',4727.98,0.00,NULL,0.00,'Payment 8','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(231,27,'2026-09-07',4727.98,0.00,NULL,0.00,'Payment 9','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(232,27,'2026-10-07',4727.98,0.00,NULL,0.00,'Payment 10','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(233,27,'2026-11-07',4727.98,0.00,NULL,0.00,'Payment 11','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(234,27,'2026-12-07',4727.98,0.00,NULL,0.00,'Payment 12','2025-12-08 14:38:49.056','2025-12-08 14:38:49.056'),(235,28,'2026-01-08',8926.29,0.00,NULL,0.00,'First payment','2025-12-09 10:48:55.911','2025-12-09 10:48:55.911'),(236,28,'2026-02-08',8926.29,0.00,NULL,0.00,'Payment 2','2025-12-09 10:48:55.911','2025-12-09 10:48:55.911'),(237,28,'2026-03-08',8926.29,0.00,NULL,0.00,'Payment 3','2025-12-09 10:48:55.911','2025-12-09 10:48:55.911'),(238,28,'2026-04-08',8926.29,0.00,NULL,0.00,'Payment 4','2025-12-09 10:48:55.911','2025-12-09 10:48:55.911'),(239,28,'2026-05-08',8926.29,0.00,NULL,0.00,'Payment 5','2025-12-09 10:48:55.911','2025-12-09 10:48:55.911'),(240,28,'2026-06-08',8926.29,0.00,NULL,0.00,'Payment 6','2025-12-09 10:48:55.911','2025-12-09 10:48:55.911'),(241,29,'2026-01-08',3624.21,0.00,NULL,0.00,'First payment','2025-12-09 11:33:13.060','2025-12-09 11:33:13.060'),(242,29,'2026-02-08',3624.21,0.00,NULL,0.00,'Payment 2','2025-12-09 11:33:13.060','2025-12-09 11:33:13.060'),(243,29,'2026-03-08',3624.21,0.00,NULL,0.00,'Payment 3','2025-12-09 11:33:13.060','2025-12-09 11:33:13.060'),(244,29,'2026-04-08',3624.21,0.00,NULL,0.00,'Payment 4','2025-12-09 11:33:13.060','2025-12-09 11:33:13.060'),(245,30,'2026-01-08',3624.21,0.00,NULL,0.00,'First payment','2025-12-09 11:39:03.687','2025-12-09 11:39:03.687'),(246,30,'2026-02-08',3624.21,0.00,NULL,0.00,'Payment 2','2025-12-09 11:39:03.687','2025-12-09 11:39:03.687'),(247,30,'2026-03-08',3624.21,0.00,NULL,0.00,'Payment 3','2025-12-09 11:39:03.687','2025-12-09 11:39:03.687'),(248,30,'2026-04-08',3624.21,0.00,NULL,0.00,'Payment 4','2025-12-09 11:39:03.687','2025-12-09 11:39:03.687'),(249,31,'2026-01-08',3624.21,0.00,NULL,0.00,'First payment','2025-12-09 11:42:51.672','2025-12-09 11:42:51.672'),(250,31,'2026-02-08',3624.21,0.00,NULL,0.00,'Payment 2','2025-12-09 11:42:51.672','2025-12-09 11:42:51.672'),(251,31,'2026-03-08',3624.21,0.00,NULL,0.00,'Payment 3','2025-12-09 11:42:51.672','2025-12-09 11:42:51.672'),(252,31,'2026-04-08',3624.21,0.00,NULL,0.00,'Payment 4','2025-12-09 11:42:51.672','2025-12-09 11:42:51.672');
/*!40000 ALTER TABLE `repayments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permission` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permission_permission_id_fkey` (`permission_id`),
  CONSTRAINT `role_permission_permission_id_fkey` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permission_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permission`
--

LOCK TABLES `role_permission` WRITE;
/*!40000 ALTER TABLE `role_permission` DISABLE KEYS */;
INSERT INTO `role_permission` VALUES (1,1,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,2,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,3,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,4,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,5,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,6,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,7,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,8,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,9,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,10,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,11,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,12,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,13,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,14,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,15,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,16,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,17,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,18,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,19,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,20,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,21,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(1,22,1,'2025-11-30 04:56:38.000','2025-11-30 04:56:38.000');
/*!40000 ALTER TABLE `role_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_key` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrator','admin','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(2,'Manager','manager','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(3,'Staff','staff','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000'),(4,'Viewer','viewer','2025-11-30 04:56:38.000','2025-11-30 04:56:38.000');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_messages`
--

DROP TABLE IF EXISTS `support_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `borrower_id` bigint unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_response` text COLLATE utf8mb4_unicode_ci,
  `responded_by_user_id` bigint unsigned DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_messages_responded_by_user_id_foreign` (`responded_by_user_id`),
  KEY `support_messages_borrower_id_index` (`borrower_id`),
  KEY `support_messages_status_index` (`status`),
  KEY `support_messages_created_at_index` (`created_at`),
  CONSTRAINT `support_messages_borrower_id_foreign` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_messages_responded_by_user_id_foreign` FOREIGN KEY (`responded_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_messages`
--

LOCK TABLES `support_messages` WRITE;
/*!40000 ALTER TABLE `support_messages` DISABLE KEYS */;
INSERT INTO `support_messages` VALUES (1,4,'Hello','Hello','pending','gvhjb,knl',1,'2025-11-30 03:59:46','2025-11-30 03:59:12','2025-11-30 03:59:46'),(2,4,'Halla','Halla','pending',NULL,NULL,NULL,'2025-11-30 04:14:36','2025-11-30 04:14:36');
/*!40000 ALTER TABLE `support_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime(3) DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` datetime(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_key` (`username`),
  KEY `users_role_id_fkey` (`role_id`),
  CONSTRAINT `users_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$12$RsWnbh32aA0nySgoFKDtVeznnP3GMrlgM7G3YOk4ZuX2XjFOpq1iq',1,'2025-11-29 07:47:24.000','2025-11-30 04:56:38.000');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-16 18:10:58
