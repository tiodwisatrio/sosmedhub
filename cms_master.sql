
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_banner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_banner` text COLLATE utf8mb4_unicode_ci,
  `gambar_banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'Wujudkan Ide Anda Bersama Kami','Konsultasi gratis, tanpa ragu. Ceritakan kebutuhan Anda dan kami akan bantu menemukan solusi terbaik.','banners/82YrrMSOEAQ0GOoa9Tcl7m1JDBhBzbXgp5N1Q2Kk.webp',1,'2026-06-28 04:05:45','2026-06-29 09:23:21',NULL);
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('tiocompany-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1783093188),('tiocompany-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1783093188;',1783093188),('tiocompany-cache-boost:mcp:database-schema:mysql::0:0:0:0','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:26:{s:7:\"banners\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:11:\"nama_banner\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:16:\"deskripsi_banner\";a:1:{s:4:\"type\";s:4:\"text\";}s:13:\"gambar_banner\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:5:\"cache\";a:5:{s:7:\"columns\";a:3:{s:3:\"key\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"value\";a:1:{s:4:\"type\";s:10:\"mediumtext\";}s:10:\"expiration\";a:1:{s:4:\"type\";s:6:\"bigint\";}}s:7:\"indexes\";a:2:{s:22:\"cache_expiration_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"expiration\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:3:\"key\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:11:\"cache_locks\";a:5:{s:7:\"columns\";a:3:{s:3:\"key\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"owner\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"expiration\";a:1:{s:4:\"type\";s:6:\"bigint\";}}s:7:\"indexes\";a:2:{s:28:\"cache_locks_expiration_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"expiration\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:3:\"key\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:10:\"categories\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"type\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"slug\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"description\";a:1:{s:4:\"type\";s:4:\"text\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:3:{s:22:\"categories_slug_unique\";a:4:{s:7:\"columns\";a:1:{i:0;s:4:\"slug\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:21:\"categories_type_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:4:\"type\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:11:\"failed_jobs\";a:5:{s:7:\"columns\";a:7:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"uuid\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"connection\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"queue\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:7:\"payload\";a:1:{s:4:\"type\";s:8:\"longtext\";}s:9:\"exception\";a:1:{s:4:\"type\";s:8:\"longtext\";}s:9:\"failed_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:3:{s:44:\"failed_jobs_connection_queue_failed_at_index\";a:4:{s:7:\"columns\";a:3:{i:0;s:10:\"connection\";i:1;s:5:\"queue\";i:2;s:9:\"failed_at\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:23:\"failed_jobs_uuid_unique\";a:4:{s:7:\"columns\";a:1:{i:0;s:4:\"uuid\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:4:\"faqs\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"pertanyaan\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:7:\"jawaban\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:6:\"heroes\";a:5:{s:7:\"columns\";a:7:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"judul_hero\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"deskripsi_hero\";a:1:{s:4:\"type\";s:4:\"text\";}s:11:\"button_hero\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:11:\"job_batches\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"total_jobs\";a:1:{s:4:\"type\";s:3:\"int\";}s:12:\"pending_jobs\";a:1:{s:4:\"type\";s:3:\"int\";}s:11:\"failed_jobs\";a:1:{s:4:\"type\";s:3:\"int\";}s:14:\"failed_job_ids\";a:1:{s:4:\"type\";s:8:\"longtext\";}s:7:\"options\";a:1:{s:4:\"type\";s:10:\"mediumtext\";}s:12:\"cancelled_at\";a:1:{s:4:\"type\";s:3:\"int\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:3:\"int\";}s:11:\"finished_at\";a:1:{s:4:\"type\";s:3:\"int\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:4:\"jobs\";a:5:{s:7:\"columns\";a:7:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:5:\"queue\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:7:\"payload\";a:1:{s:4:\"type\";s:8:\"longtext\";}s:8:\"attempts\";a:1:{s:4:\"type\";s:17:\"smallint unsigned\";}s:11:\"reserved_at\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:12:\"available_at\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}}s:7:\"indexes\";a:2:{s:16:\"jobs_queue_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:5:\"queue\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:11:\"keunggulans\";a:5:{s:7:\"columns\";a:9:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"description\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:3:\"int\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:6:\"kliens\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"nama_klien\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"logo_klien\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:8:\"layanans\";a:5:{s:7:\"columns\";a:9:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"description\";a:1:{s:4:\"type\";s:4:\"text\";}s:5:\"image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:5:\"menus\";a:5:{s:7:\"columns\";a:12:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:9:\"parent_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:5:\"label\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:10:\"route_name\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:12:\"route_params\";a:1:{s:4:\"type\";s:4:\"json\";}s:14:\"active_pattern\";a:1:{s:4:\"type\";s:12:\"varchar(150)\";}s:10:\"permission\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:4:\"icon\";a:1:{s:4:\"type\";s:4:\"text\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:17:\"smallint unsigned\";}s:9:\"is_active\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:2:{s:23:\"menus_parent_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:9:\"parent_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:1:{i:0;a:7:{s:4:\"name\";s:23:\"menus_parent_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"parent_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:5:\"menus\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:10:\"migrations\";a:5:{s:7:\"columns\";a:3:{s:2:\"id\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:9:\"migration\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"batch\";a:1:{s:4:\"type\";s:3:\"int\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:21:\"model_has_permissions\";a:5:{s:7:\"columns\";a:3:{s:13:\"permission_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"model_type\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:8:\"model_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}}s:7:\"indexes\";a:2:{s:47:\"model_has_permissions_model_id_model_type_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:8:\"model_id\";i:1;s:10:\"model_type\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:3:{i:0;s:13:\"permission_id\";i:1;s:8:\"model_id\";i:2;s:10:\"model_type\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:1:{i:0;a:7:{s:4:\"name\";s:43:\"model_has_permissions_permission_id_foreign\";s:7:\"columns\";a:1:{i:0;s:13:\"permission_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:11:\"permissions\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:15:\"model_has_roles\";a:5:{s:7:\"columns\";a:3:{s:7:\"role_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"model_type\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:8:\"model_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}}s:7:\"indexes\";a:2:{s:41:\"model_has_roles_model_id_model_type_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:8:\"model_id\";i:1;s:10:\"model_type\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:3:{i:0;s:7:\"role_id\";i:1;s:8:\"model_id\";i:2;s:10:\"model_type\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:1:{i:0;a:7:{s:4:\"name\";s:31:\"model_has_roles_role_id_foreign\";s:7:\"columns\";a:1:{i:0;s:7:\"role_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:5:\"roles\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:6:\"pakets\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"nama_paket\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:15:\"deskripsi_paket\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"harga_paket\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:12:\"gambar_paket\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:21:\"password_reset_tokens\";a:5:{s:7:\"columns\";a:3:{s:5:\"email\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"token\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:5:\"email\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:11:\"permissions\";a:5:{s:7:\"columns\";a:5:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"guard_name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:2:{s:34:\"permissions_name_guard_name_unique\";a:4:{s:7:\"columns\";a:2:{i:0;s:4:\"name\";i:1;s:10:\"guard_name\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:5:\"posts\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:11:\"category_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:5:\"title\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"slug\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:7:\"content\";a:1:{s:4:\"type\";s:4:\"text\";}s:5:\"image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:6:\"author\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:20:\"role_has_permissions\";a:5:{s:7:\"columns\";a:2:{s:13:\"permission_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:7:\"role_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}}s:7:\"indexes\";a:2:{s:7:\"primary\";a:4:{s:7:\"columns\";a:2:{i:0;s:13:\"permission_id\";i:1;s:7:\"role_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:36:\"role_has_permissions_role_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:7:\"role_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:2:{i:0;a:7:{s:4:\"name\";s:42:\"role_has_permissions_permission_id_foreign\";s:7:\"columns\";a:1:{i:0;s:13:\"permission_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:11:\"permissions\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"role_has_permissions_role_id_foreign\";s:7:\"columns\";a:1:{i:0;s:7:\"role_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:5:\"roles\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:5:\"roles\";a:5:{s:7:\"columns\";a:5:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"guard_name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:2:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:28:\"roles_name_guard_name_unique\";a:4:{s:7:\"columns\";a:2:{i:0;s:4:\"name\";i:1;s:10:\"guard_name\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:8:\"sessions\";a:5:{s:7:\"columns\";a:6:{s:2:\"id\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:7:\"user_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"ip_address\";a:1:{s:4:\"type\";s:11:\"varchar(45)\";}s:10:\"user_agent\";a:1:{s:4:\"type\";s:4:\"text\";}s:7:\"payload\";a:1:{s:4:\"type\";s:8:\"longtext\";}s:13:\"last_activity\";a:1:{s:4:\"type\";s:3:\"int\";}}s:7:\"indexes\";a:3:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:28:\"sessions_last_activity_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:13:\"last_activity\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:22:\"sessions_user_id_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:7:\"user_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:13:\"site_settings\";a:5:{s:7:\"columns\";a:30:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:8:\"app_name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:9:\"deskripsi\";a:1:{s:4:\"type\";s:4:\"text\";}s:6:\"alamat\";a:1:{s:4:\"type\";s:4:\"text\";}s:7:\"no_telp\";a:1:{s:4:\"type\";s:11:\"varchar(30)\";}s:11:\"no_whatsapp\";a:1:{s:4:\"type\";s:11:\"varchar(30)\";}s:5:\"email\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:9:\"logo_atas\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"logo_bawah\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"icon\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:8:\"og_image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"iframe_map\";a:1:{s:4:\"type\";s:4:\"text\";}s:14:\"instagram_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"instagram_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:13:\"facebook_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:13:\"facebook_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"tiktok_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"tiktok_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:12:\"youtube_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:12:\"youtube_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"x_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"x_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"shopee_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"shopee_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"tokopedia_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"tokopedia_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"blibli_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"blibli_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:5:\"teams\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"description\";a:1:{s:4:\"type\";s:4:\"text\";}s:11:\"category_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:5:\"image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"keterangan\";a:1:{s:4:\"type\";s:4:\"text\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:2:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:25:\"teams_category_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:11:\"category_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:1:{i:0;a:7:{s:4:\"name\";s:25:\"teams_category_id_foreign\";s:7:\"columns\";a:1:{i:0;s:11:\"category_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:10:\"categories\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:5:\"users\";a:5:{s:7:\"columns\";a:11:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:4:\"name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"email\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:5:\"phone\";a:1:{s:4:\"type\";s:11:\"varchar(20)\";}s:6:\"avatar\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:17:\"email_verified_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:8:\"password\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"remember_token\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:2:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:18:\"users_email_unique\";a:4:{s:7:\"columns\";a:1:{i:0;s:5:\"email\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1783093334),('tiocompany-cache-boost:mcp:database-schema:mysql:client:0:0:0:0','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:0:{}}',1782747048),('tiocompany-cache-boost:mcp:database-schema:mysql:heroes:0:0:0:1','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:6:\"heroes\";a:5:{s:7:\"columns\";a:7:{s:2:\"id\";a:4:{s:4:\"type\";s:15:\"bigint unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:10:\"judul_hero\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:14:\"deskripsi_hero\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"button_hero\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"deleted_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1782741737),('tiocompany-cache-boost:mcp:database-schema:mysql:klien:0:0:0:0','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:6:\"kliens\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:10:\"nama_klien\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"logo_klien\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:12:\"int unsigned\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1782747071),('tiocompany-cache-boost:mcp:database-schema:mysql:layanan:0:0:0:1','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:8:\"layanans\";a:5:{s:7:\"columns\";a:9:{s:2:\"id\";a:4:{s:4:\"type\";s:15:\"bigint unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:4:\"name\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"description\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:5:\"image\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:6:\"urutan\";a:4:{s:4:\"type\";s:12:\"int unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";s:1:\"0\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:7:\"tinyint\";s:8:\"nullable\";b:0;s:7:\"default\";s:1:\"1\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"deleted_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1782744066),('tiocompany-cache-boost:mcp:database-schema:mysql:menus:0:0:0:0','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:5:\"menus\";a:5:{s:7:\"columns\";a:12:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:9:\"parent_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:5:\"label\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:10:\"route_name\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:12:\"route_params\";a:1:{s:4:\"type\";s:4:\"json\";}s:14:\"active_pattern\";a:1:{s:4:\"type\";s:12:\"varchar(150)\";}s:10:\"permission\";a:1:{s:4:\"type\";s:12:\"varchar(100)\";}s:4:\"icon\";a:1:{s:4:\"type\";s:4:\"text\";}s:6:\"urutan\";a:1:{s:4:\"type\";s:17:\"smallint unsigned\";}s:9:\"is_active\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:2:{s:23:\"menus_parent_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:9:\"parent_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:1:{i:0;a:7:{s:4:\"name\";s:23:\"menus_parent_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"parent_id\";}s:14:\"foreign_schema\";s:10:\"cms_master\";s:13:\"foreign_table\";s:5:\"menus\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:9:\"no action\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1783090871),('tiocompany-cache-boost:mcp:database-schema:mysql:posts:0:0:0:0','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:5:\"posts\";a:5:{s:7:\"columns\";a:11:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:11:\"category_id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:5:\"title\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"slug\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:7:\"content\";a:1:{s:4:\"type\";s:4:\"text\";}s:5:\"image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"status\";a:1:{s:4:\"type\";s:7:\"tinyint\";}s:6:\"author\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"deleted_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1783093746),('tiocompany-cache-boost:mcp:database-schema:mysql:site_settings:0:0:0:0','a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:13:\"site_settings\";a:5:{s:7:\"columns\";a:30:{s:2:\"id\";a:1:{s:4:\"type\";s:15:\"bigint unsigned\";}s:8:\"app_name\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:9:\"deskripsi\";a:1:{s:4:\"type\";s:4:\"text\";}s:6:\"alamat\";a:1:{s:4:\"type\";s:4:\"text\";}s:7:\"no_telp\";a:1:{s:4:\"type\";s:11:\"varchar(30)\";}s:11:\"no_whatsapp\";a:1:{s:4:\"type\";s:11:\"varchar(30)\";}s:5:\"email\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:9:\"logo_atas\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"logo_bawah\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:4:\"icon\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:8:\"og_image\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"iframe_map\";a:1:{s:4:\"type\";s:4:\"text\";}s:14:\"instagram_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"instagram_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:13:\"facebook_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:13:\"facebook_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"tiktok_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"tiktok_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:12:\"youtube_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:12:\"youtube_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"x_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:6:\"x_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"shopee_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"shopee_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"tokopedia_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:14:\"tokopedia_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"blibli_nama\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:11:\"blibli_link\";a:1:{s:4:\"type\";s:12:\"varchar(255)\";}s:10:\"created_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}s:10:\"updated_at\";a:1:{s:4:\"type\";s:9:\"timestamp\";}}s:7:\"indexes\";a:1:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}',1782913601),('tiocompany-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:56:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"category.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"category.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"category.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"category.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:4;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:9:\"user.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:5;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:11:\"user.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:6;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:9:\"user.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:7;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:11:\"user.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:8;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:9:\"team.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:9;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:11:\"team.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:10;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:9:\"team.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:11;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:11:\"team.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:12;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:12:\"layanan.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:13;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:14:\"layanan.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:12:\"layanan.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:14:\"layanan.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:9:\"role.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:11:\"role.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:9:\"role.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:11:\"role.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:9:\"menu.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:11:\"menu.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:9:\"menu.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:11:\"menu.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:15:\"keunggulan.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:25;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"keunggulan.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:26;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:15:\"keunggulan.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:27;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:17:\"keunggulan.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:28;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:17:\"site-setting.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:29;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:17:\"site-setting.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:30;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:14:\"generator.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:31;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:16:\"generator.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:32;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:11:\"banner.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:33;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:13:\"banner.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:34;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:11:\"banner.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:35;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:13:\"banner.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:36;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:9:\"hero.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:37;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:11:\"hero.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:38;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:9:\"hero.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:39;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:11:\"hero.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:40;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:10:\"klien.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:41;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:12:\"klien.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:42;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:10:\"klien.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:43;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:12:\"klien.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:44;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:8:\"faq.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:45;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:10:\"faq.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:46;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:8:\"faq.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:47;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:10:\"faq.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:48;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:9:\"post.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:49;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:11:\"post.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:50;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:9:\"post.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:51;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:11:\"post.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:52;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:10:\"paket.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:53;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:12:\"paket.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:54;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:10:\"paket.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:55;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:12:\"paket.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:9:\"developer\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"superadmin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}',1783175246);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'team','Designer','designer','Designer Muahal','2026-06-23 11:28:06','2026-06-23 11:28:06',NULL),(2,'post','Pendidikan','pendidikan',NULL,'2026-06-28 03:41:46','2026-06-28 03:41:46',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pertanyaan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawaban` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `heroes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `heroes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul_hero` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_hero` text COLLATE utf8mb4_unicode_ci,
  `button_hero` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `heroes` WRITE;
/*!40000 ALTER TABLE `heroes` DISABLE KEYS */;
INSERT INTO `heroes` VALUES (1,'Langkah Awal Menuju Bisnis Yang Lebih Professional.','Bangun kepercayaan pelanggan melalui website yang modern, responsif, dan dirancang sesuai kebutuhan bisnis Anda. Kami membantu menciptakan kehadiran digital yang profesional dan siap berkembang.','Konsultasikan Sekarang','2026-06-29 06:58:03','2026-06-29 06:58:03',NULL);
/*!40000 ALTER TABLE `heroes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `keunggulans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keunggulans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `keunggulans` WRITE;
/*!40000 ALTER TABLE `keunggulans` DISABLE KEYS */;
/*!40000 ALTER TABLE `keunggulans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `kliens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kliens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_klien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_klien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `kliens` WRITE;
/*!40000 ALTER TABLE `kliens` DISABLE KEYS */;
INSERT INTO `kliens` VALUES (1,'Facebook','kliens/Hs6gCeNAX8R9kYu3hmkxV6LST1YcAzU7LBCsI6aK.webp',1,1,'2026-06-29 08:23:56','2026-06-29 08:23:56',NULL),(2,'Google','kliens/NJkfHdWinxxRIeRFr2kBLATjOA2MO3Wyx9AqAwB4.webp',2,1,'2026-06-29 08:24:13','2026-06-29 08:24:13',NULL),(3,'BCA','kliens/yP4R13q5DyoUa6PTVAW3KQUckk0gc9sVGj762KzU.jpg',3,1,'2026-06-29 08:24:26','2026-06-29 08:24:26',NULL),(4,'Mandiri','kliens/VXYiKvgAmVYwopXRNwGgiFdHkcNsrGO0CFRkXBff.jpg',4,1,'2026-06-29 08:24:43','2026-06-29 08:24:43',NULL),(5,'Instagram','kliens/UHjt5XFQnzICANFh1WYBK9hILFAKpOVnXhLK2fRx.webp',5,1,'2026-06-29 08:25:15','2026-06-29 08:25:15',NULL);
/*!40000 ALTER TABLE `kliens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `layanans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `layanans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `layanans` WRITE;
/*!40000 ALTER TABLE `layanans` DISABLE KEYS */;
INSERT INTO `layanans` VALUES (1,'Website Company Profile','Bangun identitas digital yang profesional dan meningkatkan kepercayaan pelanggan melalui website yang modern dan responsif.','layanans/DsNzNAyCVjYNKYLzjTpqQvJk6r1NsNKk8A6tU9r5.jpg',1,1,'2026-06-23 11:33:01','2026-07-01 05:40:54',NULL),(2,'Online Store','Website toko online dengan pengalaman belanja yang nyaman, aman, dan siap mendukung pertumbuhan bisnis Anda.','layanans/D0zC9X5E93x19YSOtbiKriV4D3xGy2KwIPHcVpuE.jpg',2,1,'2026-06-29 07:38:18','2026-07-01 05:40:44',NULL),(3,'Custom Web System','Pengembangan sistem dan aplikasi berbasis web yang disesuaikan dengan kebutuhan operasional maupun bisnis Anda.','layanans/ot7dbAVjk1fIn4POR6kRud6pous2auxvie2b6C7e.jpg',3,1,'2026-06-29 07:38:49','2026-06-29 07:38:49',NULL);
/*!40000 ALTER TABLE `layanans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_params` json DEFAULT NULL,
  `active_pattern` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permission` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_parent_id_foreign` (`parent_id`),
  CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,NULL,'Dashboard','admin.dashboard',NULL,'admin.dashboard',NULL,'home',0,1,'2026-06-23 11:48:37','2026-07-01 07:08:00'),(2,NULL,'Layanan','admin.layanans.index',NULL,'admin.layanans.*','layanan.view','heart',2,1,'2026-06-23 11:48:37','2026-07-01 07:08:00'),(3,NULL,'Tim',NULL,NULL,NULL,'team.view','user-group',9,1,'2026-06-23 11:48:37','2026-07-01 07:08:00'),(4,3,'Data Tim','admin.teams.index',NULL,'admin.teams.*','team.view',NULL,0,1,'2026-06-23 11:48:37','2026-06-23 11:48:37'),(5,3,'Kategori Tim','admin.categories.index','{\"type\": \"team\"}','admin.categories.*','category.view',NULL,1,1,'2026-06-23 11:48:37','2026-06-23 11:48:37'),(6,11,'Pengguna','admin.users.index',NULL,'admin.users.*','user.view','user',2,1,'2026-06-23 11:48:37','2026-06-27 02:56:24'),(7,11,'Role & Akses','admin.roles.index',NULL,'admin.roles.*','role.view','shield-check',1,1,'2026-06-23 11:48:37','2026-06-27 02:56:24'),(8,11,'Menu','admin.menus.index',NULL,'admin.menus.*','menu.view','bars-3',0,1,'2026-06-23 12:02:03','2026-06-27 02:56:24'),(9,NULL,'Keunggulan','admin.keunggulans.index',NULL,'admin.keunggulans.*','keunggulan.view','star',6,1,'2026-06-24 10:45:42','2026-07-01 07:08:00'),(11,NULL,'Setting',NULL,NULL,NULL,NULL,'wrench-screwdriver',10,1,'2026-06-27 02:55:50','2026-07-01 07:08:00'),(12,11,'Pengaturan Situs','admin.site-settings.index',NULL,'admin.site-settings.*','site-setting.view',NULL,3,1,'2026-06-27 03:14:06','2026-06-27 03:14:06'),(13,15,'Data Post','admin.posts.index',NULL,'admin.posts.*','post.view','clipboard-document-list',0,1,'2026-06-27 05:50:35','2026-06-29 09:39:18'),(15,NULL,'Post',NULL,NULL,NULL,NULL,'clipboard-document-list',8,1,'2026-06-27 05:55:28','2026-07-01 07:08:00'),(16,15,'Kategori Post','admin.categories.index','{\"type\": \"post\"}','admin.categories.*','category.view',NULL,1,1,'2026-06-27 05:57:33','2026-07-01 06:25:58'),(17,11,'Generator Modul','admin.generator.index',NULL,'admin.generator.*','generator.view','cog',4,1,'2026-06-28 03:47:46','2026-06-28 04:05:12'),(19,NULL,'Banner','admin.banners.index',NULL,'admin.banners.*','banner.view','photo',4,1,'2026-06-28 04:01:17','2026-07-01 07:08:00'),(20,NULL,'Hero','admin.heroes.index',NULL,'admin.heroes.*','hero.view','computer-desktop',1,1,'2026-06-29 06:50:46','2026-07-01 07:08:00'),(21,NULL,'Klien','admin.kliens.index',NULL,'admin.kliens.*','klien.view','users',3,1,'2026-06-29 08:20:58','2026-07-01 07:08:00'),(22,NULL,'Faq','admin.faqs.index',NULL,'admin.faqs.*','faq.view','question-mark-circle',5,1,'2026-06-29 09:42:26','2026-07-01 07:08:00'),(23,NULL,'Paket','admin.pakets.index',NULL,'admin.pakets.*','paket.view','gift',7,1,'2026-07-01 07:07:02','2026-07-01 07:08:00');
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_06_23_164001_create_permission_tables',2),(5,'2026_06_23_173622_create_categories_table',3),(6,'2026_06_23_175150_add_type_to_categories_table',4),(7,'2026_06_23_182125_create_teams_table',5),(8,'2026_06_23_183018_create_layanans_table',6),(9,'2026_06_23_183619_create_menus_table',7),(10,'2026_06_23_184654_add_permission_to_menus_table',8),(11,'2026_06_24_172201_create_keunggulans_table',9),(12,'2026_06_24_184753_add_profile_fields_to_users_table',10),(13,'2026_06_27_100704_create_site_settings_table',11),(16,'2026_06_28_110114_create_banners_table',12),(17,'2026_06_29_135044_create_heroes_table',13),(18,'2026_06_29_152056_create_kliens_table',14),(19,'2026_06_29_164224_create_faqs_table',15),(20,'2026_07_01_134419_add_deskripsi_sosmed_marketplace_to_site_settings_table',16),(21,'2026_07_01_140700_create_pakets_table',17),(22,'2026_06_27_122305_create_posts_table',11),(23,'2026_07_03_154732_add_soft_deletes_to_posts_table',18);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(5,'App\\Models\\User',2),(4,'App\\Models\\User',3),(1,'App\\Models\\User',4),(5,'App\\Models\\User',4),(1,'App\\Models\\User',5),(1,'App\\Models\\User',6);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pakets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pakets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_paket` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_paket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_paket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar_paket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pakets` WRITE;
/*!40000 ALTER TABLE `pakets` DISABLE KEYS */;
/*!40000 ALTER TABLE `pakets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('tiodwisatrio7@gmail.com','$2y$12$o9n08gxMqD4Y8SorLCaxheyogX2qK3yIb9A5KdMMXFYD3NgxRJhG.','2026-06-27 04:29:24');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'category.view','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(2,'category.create','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(3,'category.edit','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(4,'category.delete','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(17,'user.view','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(18,'user.create','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(19,'user.edit','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(20,'user.delete','web','2026-06-23 10:57:24','2026-06-23 10:57:24'),(23,'team.view','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(24,'team.create','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(25,'team.edit','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(26,'team.delete','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(27,'layanan.view','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(28,'layanan.create','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(29,'layanan.edit','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(30,'layanan.delete','web','2026-06-23 11:44:22','2026-06-23 11:44:22'),(32,'role.view','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(33,'role.create','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(34,'role.edit','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(35,'role.delete','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(36,'menu.view','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(37,'menu.create','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(38,'menu.edit','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(39,'menu.delete','web','2026-06-23 11:59:16','2026-06-23 11:59:16'),(40,'keunggulan.view','web','2026-06-24 10:43:19','2026-06-24 10:43:19'),(41,'keunggulan.create','web','2026-06-24 10:43:19','2026-06-24 10:43:19'),(42,'keunggulan.edit','web','2026-06-24 10:43:19','2026-06-24 10:43:19'),(43,'keunggulan.delete','web','2026-06-24 10:43:19','2026-06-24 10:43:19'),(44,'site-setting.view','web','2026-06-27 03:16:41','2026-06-27 03:16:41'),(45,'site-setting.edit','web','2026-06-27 03:16:41','2026-06-27 03:16:41'),(46,'generator.view','web','2026-06-28 03:47:46','2026-06-28 03:47:46'),(47,'generator.create','web','2026-06-28 03:47:46','2026-06-28 03:47:46'),(56,'banner.view','web','2026-06-28 04:01:17','2026-06-28 04:01:17'),(57,'banner.create','web','2026-06-28 04:01:17','2026-06-28 04:01:17'),(58,'banner.edit','web','2026-06-28 04:01:17','2026-06-28 04:01:17'),(59,'banner.delete','web','2026-06-28 04:01:17','2026-06-28 04:01:17'),(60,'hero.view','web','2026-06-29 06:50:46','2026-06-29 06:50:46'),(61,'hero.create','web','2026-06-29 06:50:46','2026-06-29 06:50:46'),(62,'hero.edit','web','2026-06-29 06:50:46','2026-06-29 06:50:46'),(63,'hero.delete','web','2026-06-29 06:50:46','2026-06-29 06:50:46'),(64,'klien.view','web','2026-06-29 08:20:58','2026-06-29 08:20:58'),(65,'klien.create','web','2026-06-29 08:20:58','2026-06-29 08:20:58'),(66,'klien.edit','web','2026-06-29 08:20:58','2026-06-29 08:20:58'),(67,'klien.delete','web','2026-06-29 08:20:58','2026-06-29 08:20:58'),(68,'faq.view','web','2026-06-29 09:42:26','2026-06-29 09:42:26'),(69,'faq.create','web','2026-06-29 09:42:26','2026-06-29 09:42:26'),(70,'faq.edit','web','2026-06-29 09:42:26','2026-06-29 09:42:26'),(71,'faq.delete','web','2026-06-29 09:42:26','2026-06-29 09:42:26'),(72,'post.view','web','2026-07-01 07:02:42','2026-07-01 07:02:42'),(73,'post.create','web','2026-07-01 07:02:42','2026-07-01 07:02:42'),(74,'post.edit','web','2026-07-01 07:02:42','2026-07-01 07:02:42'),(75,'post.delete','web','2026-07-01 07:02:42','2026-07-01 07:02:42'),(76,'paket.view','web','2026-07-01 07:07:02','2026-07-01 07:07:02'),(77,'paket.create','web','2026-07-01 07:07:02','2026-07-01 07:07:02'),(78,'paket.edit','web','2026-07-01 07:07:02','2026-07-01 07:07:02'),(79,'paket.delete','web','2026-07-01 07:07:02','2026-07-01 07:07:02');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,2,'Postingan','postingan','<p><strong>TES EDITOR King</strong>\n</p>\n\n<p>Langkah - langkah membuat berita:</p>\n<ol>\n<li>Buat berita</li>\n<li>Bagikan berita</li>\n<li>Kirim ke orang lain</li>\n</ol>\n<p>Teknologi untuk menjadi web developer:</p>\n<ul>\n<li>HTML, CSS, Javascript, PHP</li>\n<li>ReactJS, Svelte, Vue</li>\n<li>Next JS, NodeJS, Laravel</li>\n</ul>',NULL,1,'Yono','2026-06-28 03:42:21','2026-07-03 06:29:21',NULL);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(17,1),(18,1),(19,1),(20,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(1,4),(2,4),(3,4),(4,4),(17,4),(18,4),(19,4),(20,4),(1,5),(2,5),(3,5),(4,5),(23,5),(24,5),(25,5),(26,5),(27,5),(40,5),(41,5),(42,5),(43,5),(44,5),(45,5),(46,5),(47,5),(56,5),(57,5),(58,5),(59,5),(60,5),(61,5),(62,5),(63,5),(64,5),(65,5),(66,5),(67,5),(68,5),(69,5),(70,5),(71,5),(72,5),(73,5),(74,5),(75,5),(76,5),(77,5),(78,5),(79,5);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'developer','web','2026-06-23 10:25:40','2026-06-23 10:25:40'),(4,'superadmin','web','2026-06-23 11:06:46','2026-06-23 11:06:46'),(5,'admin','web','2026-06-23 11:07:08','2026-06-23 11:07:08');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('57QSF7HvnbROyQSAIOwT3qUBh3nwhdXyzqLqyPra',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5 Safari/605.1.15','eyJfdG9rZW4iOiJCblVOZ3R1OUNCRUpUejF4NVNBcTVuZVozWG5OcEFiMk9rVmtTN2xnIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDAiLCJyb3V0ZSI6bnVsbH0sInVybCI6eyJpbnRlbmRlZCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hZG1pblwvZGFzaGJvYXJkIn19',1783093142),('9kRuSmliRlL9wCgstFDa3Wr6JRED9sKk58DdYqqW',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJJQnowaHBRMjVZejNPVFByTXhkR3pUVm01akQ2S0NrTWR2cWxKY3h2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9sYXlhbmFuIiwicm91dGUiOiJsYXlhbmFuLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1783092134),('CXHCvtOqYFhr2Qk6zK0Sd7vFi3okEBk0UdWeBvcI',5,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NSwiX3Rva2VuIjoiQTUzSG1QbTFZQTNKM0cxcnlqVzUwbEtmbG9JWHdQTHkxQnVPcDNueSIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2FkbWluXC9rZXVuZ2d1bGFuc1wvY3JlYXRlIiwicm91dGUiOiJhZG1pbi5rZXVuZ2d1bGFucy5jcmVhdGUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1783090506),('DJOMjCRPsoxxEQkdAtk03wtstQ8N186WxvDmTi2J',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJYQXdCU2FyeVhYT1U3QVQyR2JEQ0FqMUZaQ3lOQzRBeVBrV1VlUmR4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9rb250YWsiLCJyb3V0ZSI6ImtvbnRhayJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1783087284),('sZd1PvTrzRSSvE1QH8xWVUr7o9Pr3rkAedHtodo1',6,'127.0.0.1','Symfony','eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NiwiX3Rva2VuIjoiS0pnblFzaFVqYzlBY2JETW96ZmtoS0JFd2FyTTdmb1QzTTN1bzNkNiIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1783090515),('WPxzqsXN1g6abM3lEwji4sL7AqtaodmzVEaYmTlz',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI3WlI5cjhBSkZMY1ZYSWp2VjY2N21vOU5TYjNTbTlrRDByckpxVXNEIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9oZXJvZXMiLCJyb3V0ZSI6ImFkbWluLmhlcm9lcy5pbmRleCJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1783093431);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `app_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_whatsapp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_atas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_bawah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iframe_map` text COLLATE utf8mb4_unicode_ci,
  `instagram_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shopee_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shopee_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokopedia_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokopedia_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blibli_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blibli_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'Crafthink Web','Crafthink Web adalah sebuah agency yang membantu bisnis Anda tidak hanya terlihat profesional, tapi juga berdampak pada branding dan legalitas bisnis Anda secara resmi.','Jl Laksda Adisucipto No 3, Yogyalarta','088972061745','088972061745','tiodwisatrio27@gmail.com','site-settings/kAahfocuULKA3828DRC7RHeGwuqEyR9VCXsEq4Vk.jpg','site-settings/TLh4Z1Z77Ho0RFmNPf96yFCtSgjWK5z3qXrTBrfi.jpg','site-settings/wljSQJpyj9NOVQ2NvfUATDJqB55AWZfA04dzl3LA.jpg','site-settings/fbVhxOWyV88Lcor1RgSnAUpVYkaCHbfgAGZvDbVS.jpg','https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1976.454232470929!2d110.40264003876783!3d-7.79951534065742!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a575ddde6c091%3A0x89d72c5fdecbbc18!2sJogja%20Expo%20Center%2C%20Jl.%20Raya%20Janti%20Jl.%20Wonocatur%2C%20Wonocatur%2C%20Banguntapan%2C%20Kec.%20Banguntapan%2C%20Kabupaten%20Bantul%2C%20Daerah%20Istimewa%20Yogyakarta%2055198!5e0!3m2!1sid!2sid!4v1783087306521!5m2!1sid!2sid','Tio Dwi Satrio','https://instagram.com/tiodwisatrio_','Tio Dwi','https://facebook.com/tiodwisatrio','@tiodwisatrio_','https://tiktok.com/@__summerfling','Tio Dwi Satrio','https://youtube.com/@tiodwisatrio',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-27 03:08:47','2026-07-03 07:31:49');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category_id` bigint unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_category_id_foreign` (`category_id`),
  CONSTRAINT `teams_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Yono','Designer Muahal King',1,NULL,'testestes',1,'2026-06-23 11:28:32','2026-06-23 11:28:32',NULL);
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Tio Dwi Satrio','tiodwisatrio27@gmail.com','','avatars/p2eKZOD1gFx4SILKZr1wcYbAgKoozBoClWIMV5tW.webp',1,'2026-06-23 10:25:40','$2y$12$QuJL/Ur6fti8dMRMF692hOjZbve6G../9Ze.S3jkI39cPOUHMXpTq',NULL,'2026-06-23 10:25:40','2026-06-24 11:56:11'),(2,'Admin','admin@gmail.com',NULL,NULL,1,'2026-06-24 11:21:54','$2y$12$2NXfO.v.L51eBpnsVIs/l.UuLuQ5tSjIu7SdsTxi7FCTZe/7txpd.',NULL,'2026-06-23 11:13:33','2026-07-03 06:05:31'),(3,'Super Admin','superadmin@gmail.com',NULL,NULL,1,NULL,'$2y$12$fNohTVphYySh9E622xwPLuK7tkLVtFQ/Gu7F5QtZcvIygB1UTvzD6',NULL,'2026-06-23 11:13:54','2026-06-23 11:13:54'),(4,'Tio Admin','tiodwisatrio7@gmail.com','088972061745',NULL,1,NULL,'$2y$12$eLtYgUrhTTneiwMtqCfuzuig/gb7Ja.MDc74//bgH42C2h25PR1fS',NULL,'2026-06-27 02:50:37','2026-07-03 07:50:50'),(5,'Rick Rippin','teagan.ziemann@example.org',NULL,NULL,1,'2026-07-03 07:55:06','$2y$12$qokZSSa1JHem5ERy9KNrG.W4BzttZXjHdmm6ej/2zade.jQ.sldLq','7u4fMVu9tE','2026-07-03 07:55:06','2026-07-03 07:55:06'),(6,'Mr. Chadd Hartmann MD','mathew06@example.net',NULL,NULL,1,'2026-07-03 07:55:14','$2y$12$JdGOjW9XN3KXGBARfCfaAej6JPMIfW5vHy9ciel8c/Cd09xtSA9R6','QDHoZPctb9','2026-07-03 07:55:15','2026-07-03 07:55:15');
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

