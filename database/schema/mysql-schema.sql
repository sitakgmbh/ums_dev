/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `abteilungen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abteilungen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `unternehmenseinheit_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abteilungen_name_unique` (`name`),
  KEY `abteilungen_unternehmenseinheit_id_foreign` (`unternehmenseinheit_id`),
  KEY `abteilungen_enabled_index` (`enabled`),
  CONSTRAINT `abteilungen_unternehmenseinheit_id_foreign` FOREIGN KEY (`unternehmenseinheit_id`) REFERENCES `unternehmenseinheiten` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ad_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ad_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(256) NOT NULL,
  `guid` char(36) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_existing` tinyint(1) NOT NULL DEFAULT 1,
  `password_never_expires` tinyint(1) NOT NULL DEFAULT 0,
  `account_expiration_date` timestamp NULL DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `last_bad_password_attempt` timestamp NULL DEFAULT NULL,
  `last_logon_date` timestamp NULL DEFAULT NULL,
  `password_last_set` timestamp NULL DEFAULT NULL,
  `logon_count` int(11) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `home_directory` varchar(255) DEFAULT NULL,
  `home_page` varchar(255) DEFAULT NULL,
  `home_phone` varchar(255) DEFAULT NULL,
  `initials` varchar(255) DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `office_phone` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `profile_path` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `manager_dn` varchar(255) DEFAULT NULL,
  `distinguished_name` varchar(255) DEFAULT NULL,
  `user_principal_name` varchar(255) DEFAULT NULL,
  `proxy_addresses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`proxy_addresses`)),
  `member_of` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`member_of`)),
  `extensionattribute1` varchar(255) DEFAULT NULL,
  `extensionattribute2` varchar(255) DEFAULT NULL,
  `extensionattribute3` varchar(255) DEFAULT NULL,
  `extensionattribute4` varchar(255) DEFAULT NULL,
  `extensionattribute5` varchar(255) DEFAULT NULL,
  `extensionattribute6` varchar(255) DEFAULT NULL,
  `extensionattribute7` varchar(255) DEFAULT NULL,
  `extensionattribute8` varchar(255) DEFAULT NULL,
  `extensionattribute9` varchar(255) DEFAULT NULL,
  `extensionattribute10` varchar(255) DEFAULT NULL,
  `extensionattribute11` varchar(255) DEFAULT NULL,
  `extensionattribute12` varchar(255) DEFAULT NULL,
  `extensionattribute13` varchar(255) DEFAULT NULL,
  `extensionattribute14` varchar(255) DEFAULT NULL,
  `extensionattribute15` varchar(255) DEFAULT NULL,
  `funktion_id` bigint(20) unsigned DEFAULT NULL,
  `abteilung_id` bigint(20) unsigned DEFAULT NULL,
  `arbeitsort_id` bigint(20) unsigned DEFAULT NULL,
  `unternehmenseinheit_id` bigint(20) unsigned DEFAULT NULL,
  `anrede_id` bigint(20) unsigned DEFAULT NULL,
  `titel_id` bigint(20) unsigned DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `profile_photo_base64` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_users_sid_unique` (`sid`),
  KEY `ad_users_funktion_id_foreign` (`funktion_id`),
  KEY `ad_users_abteilung_id_foreign` (`abteilung_id`),
  KEY `ad_users_arbeitsort_id_foreign` (`arbeitsort_id`),
  KEY `ad_users_unternehmenseinheit_id_foreign` (`unternehmenseinheit_id`),
  KEY `ad_users_anrede_id_foreign` (`anrede_id`),
  KEY `ad_users_titel_id_foreign` (`titel_id`),
  KEY `ad_users_guid_index` (`guid`),
  CONSTRAINT `ad_users_abteilung_id_foreign` FOREIGN KEY (`abteilung_id`) REFERENCES `abteilungen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_users_anrede_id_foreign` FOREIGN KEY (`anrede_id`) REFERENCES `anreden` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_users_arbeitsort_id_foreign` FOREIGN KEY (`arbeitsort_id`) REFERENCES `arbeitsorte` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_users_funktion_id_foreign` FOREIGN KEY (`funktion_id`) REFERENCES `funktionen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_users_titel_id_foreign` FOREIGN KEY (`titel_id`) REFERENCES `titel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_users_unternehmenseinheit_id_foreign` FOREIGN KEY (`unternehmenseinheit_id`) REFERENCES `unternehmenseinheiten` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `anreden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anreden` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `anreden_name_unique` (`name`),
  KEY `anreden_enabled_index` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `arbeitsorte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arbeitsorte` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `arbeitsorte_name_unique` (`name`),
  KEY `arbeitsorte_enabled_index` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `austritte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `austritte` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `vertragsende` date NOT NULL,
  `ad_user_id` bigint(20) unsigned DEFAULT NULL,
  `status_pep` tinyint(1) NOT NULL DEFAULT 1,
  `status_kis` tinyint(1) NOT NULL DEFAULT 1,
  `status_streamline` tinyint(1) NOT NULL DEFAULT 1,
  `status_tel` tinyint(1) NOT NULL DEFAULT 1,
  `status_alarmierung` tinyint(1) NOT NULL DEFAULT 1,
  `status_logimen` tinyint(1) NOT NULL DEFAULT 1,
  `ticket_nr` varchar(255) DEFAULT NULL,
  `archiviert` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `austritte_owner_id_foreign` (`owner_id`),
  KEY `austritte_ad_user_id_foreign` (`ad_user_id`),
  CONSTRAINT `austritte_ad_user_id_foreign` FOREIGN KEY (`ad_user_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `austritte_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eroeffnungen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eroeffnungen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `vorname` varchar(255) NOT NULL,
  `nachname` varchar(255) NOT NULL,
  `vertragsbeginn` date NOT NULL,
  `wiedereintritt` tinyint(1) NOT NULL DEFAULT 0,
  `antragsteller_id` bigint(20) unsigned DEFAULT NULL,
  `bezugsperson_id` bigint(20) unsigned DEFAULT NULL,
  `vorlage_benutzer_id` bigint(20) unsigned DEFAULT NULL,
  `neue_konstellation` tinyint(1) NOT NULL DEFAULT 0,
  `filter_mitarbeiter` tinyint(1) NOT NULL DEFAULT 0,
  `anrede_id` bigint(20) unsigned DEFAULT NULL,
  `titel_id` bigint(20) unsigned DEFAULT NULL,
  `arbeitsort_id` bigint(20) unsigned DEFAULT NULL,
  `unternehmenseinheit_id` bigint(20) unsigned DEFAULT NULL,
  `abteilung_id` bigint(20) unsigned DEFAULT NULL,
  `abteilung2_id` bigint(20) unsigned DEFAULT NULL,
  `funktion_id` bigint(20) unsigned DEFAULT NULL,
  `sap_rolle_id` bigint(20) unsigned DEFAULT NULL,
  `benutzername` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mailendung` varchar(255) DEFAULT NULL,
  `ad_gruppen` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ad_gruppen`)),
  `passwort` varchar(255) DEFAULT NULL,
  `tel_nr` varchar(255) DEFAULT NULL,
  `tel_auswahl` varchar(255) DEFAULT NULL,
  `tel_tischtel` tinyint(1) NOT NULL DEFAULT 0,
  `tel_mobiltel` tinyint(1) NOT NULL DEFAULT 0,
  `tel_ucstd` tinyint(1) NOT NULL DEFAULT 0,
  `tel_alarmierung` tinyint(1) NOT NULL DEFAULT 0,
  `tel_headset` varchar(255) DEFAULT NULL,
  `is_lei` tinyint(1) NOT NULL DEFAULT 0,
  `key_waldhaus` tinyint(1) NOT NULL DEFAULT 0,
  `key_beverin` tinyint(1) NOT NULL DEFAULT 0,
  `key_rothenbr` tinyint(1) NOT NULL DEFAULT 0,
  `key_wh_badge` tinyint(1) NOT NULL DEFAULT 0,
  `key_wh_schluessel` tinyint(1) NOT NULL DEFAULT 0,
  `key_be_badge` tinyint(1) NOT NULL DEFAULT 0,
  `key_be_schluessel` tinyint(1) NOT NULL DEFAULT 0,
  `key_rb_badge` tinyint(1) NOT NULL DEFAULT 0,
  `key_rb_schluessel` tinyint(1) NOT NULL DEFAULT 0,
  `berufskleider` tinyint(1) NOT NULL DEFAULT 0,
  `garderobe` tinyint(1) NOT NULL DEFAULT 0,
  `raumbeschriftung` varchar(255) DEFAULT NULL,
  `status_ad` tinyint(4) NOT NULL DEFAULT 1,
  `status_tel` tinyint(4) NOT NULL DEFAULT 0,
  `status_pep` tinyint(4) NOT NULL DEFAULT 1,
  `status_kis` tinyint(4) NOT NULL DEFAULT 0,
  `status_sap` tinyint(4) NOT NULL DEFAULT 0,
  `status_auftrag` tinyint(4) NOT NULL DEFAULT 0,
  `status_info` tinyint(4) NOT NULL DEFAULT 1,
  `vorab_lizenzierung` tinyint(1) NOT NULL DEFAULT 0,
  `kalender_berechtigungen` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kalender_berechtigungen`)),
  `kommentar` text DEFAULT NULL,
  `ticket_nr` varchar(255) DEFAULT NULL,
  `archiviert` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eroeffnungen_owner_id_foreign` (`owner_id`),
  KEY `eroeffnungen_antragsteller_id_foreign` (`antragsteller_id`),
  KEY `eroeffnungen_bezugsperson_id_foreign` (`bezugsperson_id`),
  KEY `eroeffnungen_vorlage_benutzer_id_foreign` (`vorlage_benutzer_id`),
  KEY `eroeffnungen_anrede_id_foreign` (`anrede_id`),
  KEY `eroeffnungen_titel_id_foreign` (`titel_id`),
  KEY `eroeffnungen_arbeitsort_id_foreign` (`arbeitsort_id`),
  KEY `eroeffnungen_unternehmenseinheit_id_foreign` (`unternehmenseinheit_id`),
  KEY `eroeffnungen_abteilung_id_foreign` (`abteilung_id`),
  KEY `eroeffnungen_abteilung2_id_foreign` (`abteilung2_id`),
  KEY `eroeffnungen_funktion_id_foreign` (`funktion_id`),
  KEY `eroeffnungen_sap_rolle_id_foreign` (`sap_rolle_id`),
  CONSTRAINT `eroeffnungen_abteilung2_id_foreign` FOREIGN KEY (`abteilung2_id`) REFERENCES `abteilungen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_abteilung_id_foreign` FOREIGN KEY (`abteilung_id`) REFERENCES `abteilungen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_anrede_id_foreign` FOREIGN KEY (`anrede_id`) REFERENCES `anreden` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_antragsteller_id_foreign` FOREIGN KEY (`antragsteller_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_arbeitsort_id_foreign` FOREIGN KEY (`arbeitsort_id`) REFERENCES `arbeitsorte` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_bezugsperson_id_foreign` FOREIGN KEY (`bezugsperson_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_funktion_id_foreign` FOREIGN KEY (`funktion_id`) REFERENCES `funktionen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_sap_rolle_id_foreign` FOREIGN KEY (`sap_rolle_id`) REFERENCES `sap_rollen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_titel_id_foreign` FOREIGN KEY (`titel_id`) REFERENCES `titel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_unternehmenseinheit_id_foreign` FOREIGN KEY (`unternehmenseinheit_id`) REFERENCES `unternehmenseinheiten` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eroeffnungen_vorlage_benutzer_id_foreign` FOREIGN KEY (`vorlage_benutzer_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `funktionen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `funktionen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funktionen_name_unique` (`name`),
  KEY `funktionen_enabled_index` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `konstellationen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `konstellationen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `arbeitsort_id` bigint(20) unsigned NOT NULL,
  `unternehmenseinheit_id` bigint(20) unsigned NOT NULL,
  `abteilung_id` bigint(20) unsigned NOT NULL,
  `funktion_id` bigint(20) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `konstellationen_unique` (`arbeitsort_id`,`unternehmenseinheit_id`,`abteilung_id`,`funktion_id`),
  KEY `konstellationen_unternehmenseinheit_id_foreign` (`unternehmenseinheit_id`),
  KEY `konstellationen_abteilung_id_foreign` (`abteilung_id`),
  KEY `konstellationen_funktion_id_foreign` (`funktion_id`),
  KEY `konstellationen_enabled_index` (`enabled`),
  CONSTRAINT `konstellationen_abteilung_id_foreign` FOREIGN KEY (`abteilung_id`) REFERENCES `abteilungen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `konstellationen_arbeitsort_id_foreign` FOREIGN KEY (`arbeitsort_id`) REFERENCES `arbeitsorte` (`id`) ON DELETE CASCADE,
  CONSTRAINT `konstellationen_funktion_id_foreign` FOREIGN KEY (`funktion_id`) REFERENCES `funktionen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `konstellationen_unternehmenseinheit_id_foreign` FOREIGN KEY (`unternehmenseinheit_id`) REFERENCES `unternehmenseinheiten` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `level` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mutationen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mutationen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `vertragsbeginn` date NOT NULL,
  `antragsteller_id` bigint(20) unsigned DEFAULT NULL,
  `ad_user_id` bigint(20) unsigned DEFAULT NULL,
  `mailendung` varchar(255) DEFAULT NULL,
  `vorlage_benutzer_id` bigint(20) unsigned DEFAULT NULL,
  `ad_gruppen` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ad_gruppen`)),
  `neue_konstellation` tinyint(1) NOT NULL DEFAULT 0,
  `filter_mitarbeiter` tinyint(1) NOT NULL DEFAULT 0,
  `vorname` varchar(255) DEFAULT NULL,
  `nachname` varchar(255) DEFAULT NULL,
  `anrede_id` bigint(20) unsigned DEFAULT NULL,
  `titel_id` bigint(20) unsigned DEFAULT NULL,
  `arbeitsort_id` bigint(20) unsigned DEFAULT NULL,
  `unternehmenseinheit_id` bigint(20) unsigned DEFAULT NULL,
  `abteilung_id` bigint(20) unsigned DEFAULT NULL,
  `abteilung2_id` bigint(20) unsigned DEFAULT NULL,
  `funktion_id` bigint(20) unsigned DEFAULT NULL,
  `tel_nr` varchar(255) DEFAULT NULL,
  `tel_auswahl` varchar(255) DEFAULT NULL,
  `tel_tischtel` tinyint(1) NOT NULL DEFAULT 0,
  `tel_mobiltel` tinyint(1) NOT NULL DEFAULT 0,
  `tel_ucstd` tinyint(1) NOT NULL DEFAULT 0,
  `tel_alarmierung` tinyint(1) NOT NULL DEFAULT 0,
  `tel_headset` varchar(255) DEFAULT NULL,
  `is_lei` tinyint(1) NOT NULL DEFAULT 0,
  `key_waldhaus` tinyint(1) NOT NULL DEFAULT 0,
  `key_beverin` tinyint(1) NOT NULL DEFAULT 0,
  `key_rothenbr` tinyint(1) NOT NULL DEFAULT 0,
  `key_wh_badge` tinyint(1) NOT NULL DEFAULT 0,
  `key_wh_schluessel` tinyint(1) NOT NULL DEFAULT 0,
  `key_be_badge` tinyint(1) NOT NULL DEFAULT 0,
  `key_be_schluessel` tinyint(1) NOT NULL DEFAULT 0,
  `key_rb_badge` tinyint(1) NOT NULL DEFAULT 0,
  `key_rb_schluessel` tinyint(1) NOT NULL DEFAULT 0,
  `berufskleider` tinyint(1) NOT NULL DEFAULT 0,
  `garderobe` tinyint(1) NOT NULL DEFAULT 0,
  `buerowechsel` tinyint(1) NOT NULL DEFAULT 0,
  `sap_rolle_id` bigint(20) unsigned DEFAULT NULL,
  `sap_delete` tinyint(1) NOT NULL DEFAULT 0,
  `komm_lei` text DEFAULT NULL,
  `komm_berufskleider` text DEFAULT NULL,
  `komm_garderobe` text DEFAULT NULL,
  `komm_key` text DEFAULT NULL,
  `komm_buerowechsel` text DEFAULT NULL,
  `status_ad` tinyint(4) NOT NULL DEFAULT 0,
  `status_mail` tinyint(4) NOT NULL DEFAULT 0,
  `status_tel` tinyint(4) NOT NULL DEFAULT 0,
  `status_kis` tinyint(4) NOT NULL DEFAULT 0,
  `status_pep` tinyint(4) NOT NULL DEFAULT 0,
  `status_sap` tinyint(4) NOT NULL DEFAULT 0,
  `status_auftrag` tinyint(4) NOT NULL DEFAULT 0,
  `status_info` tinyint(4) NOT NULL DEFAULT 1,
  `vorab_lizenzierung` tinyint(1) NOT NULL DEFAULT 0,
  `kalender_berechtigungen` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kalender_berechtigungen`)),
  `kommentar` text DEFAULT NULL,
  `ticket_nr` varchar(255) DEFAULT NULL,
  `archiviert` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mutationen_owner_id_foreign` (`owner_id`),
  KEY `mutationen_antragsteller_id_foreign` (`antragsteller_id`),
  KEY `mutationen_ad_user_id_foreign` (`ad_user_id`),
  KEY `mutationen_vorlage_benutzer_id_foreign` (`vorlage_benutzer_id`),
  KEY `mutationen_anrede_id_foreign` (`anrede_id`),
  KEY `mutationen_titel_id_foreign` (`titel_id`),
  KEY `mutationen_arbeitsort_id_foreign` (`arbeitsort_id`),
  KEY `mutationen_unternehmenseinheit_id_foreign` (`unternehmenseinheit_id`),
  KEY `mutationen_abteilung_id_foreign` (`abteilung_id`),
  KEY `mutationen_abteilung2_id_foreign` (`abteilung2_id`),
  KEY `mutationen_funktion_id_foreign` (`funktion_id`),
  KEY `mutationen_sap_rolle_id_foreign` (`sap_rolle_id`),
  CONSTRAINT `mutationen_abteilung2_id_foreign` FOREIGN KEY (`abteilung2_id`) REFERENCES `abteilungen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_abteilung_id_foreign` FOREIGN KEY (`abteilung_id`) REFERENCES `abteilungen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_ad_user_id_foreign` FOREIGN KEY (`ad_user_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_anrede_id_foreign` FOREIGN KEY (`anrede_id`) REFERENCES `anreden` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_antragsteller_id_foreign` FOREIGN KEY (`antragsteller_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_arbeitsort_id_foreign` FOREIGN KEY (`arbeitsort_id`) REFERENCES `arbeitsorte` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_funktion_id_foreign` FOREIGN KEY (`funktion_id`) REFERENCES `funktionen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_sap_rolle_id_foreign` FOREIGN KEY (`sap_rolle_id`) REFERENCES `sap_rollen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_titel_id_foreign` FOREIGN KEY (`titel_id`) REFERENCES `titel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_unternehmenseinheit_id_foreign` FOREIGN KEY (`unternehmenseinheit_id`) REFERENCES `unternehmenseinheiten` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutationen_vorlage_benutzer_id_foreign` FOREIGN KEY (`vorlage_benutzer_id`) REFERENCES `ad_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sap_rollen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sap_rollen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sap_rollen_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `titel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `titel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titel_name_unique` (`name`),
  KEY `titel_enabled_index` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unternehmenseinheiten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unternehmenseinheiten` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unternehmenseinheiten_name_unique` (`name`),
  KEY `unternehmenseinheiten_enabled_index` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `auth_type` varchar(255) NOT NULL DEFAULT 'local',
  `ad_sid` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT NULL,
  `settings` longtext DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_ad_sid_unique` (`ad_sid`),
  KEY `users_username_index` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0000_create_konstellationen_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0005_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0010_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'0015_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'0020_create_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'0025_create_ad_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'0030_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'0035_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'0040_create_sap_rollen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'0100_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'0101_create_eroeffnungen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'0102_create_mutationen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'0103_create_austritte_table',1);
