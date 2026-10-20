-- ========================================================
-- SerWizo Clean Consolidated Production Database Dump
-- Target Database: smartmul_serwizo_backend
-- Generated on: 2026-09-26 08:23:49
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Table Structures
-- --------------------------------------------------------

-- Table structure for `app_notifications`
DROP TABLE IF EXISTS `app_notifications`;
CREATE TABLE IF NOT EXISTS `app_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `type` VARCHAR(255) NOT NULL DEFAULT 'general',
  `data` LONGTEXT NULL DEFAULT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `areas`
DROP TABLE IF EXISTS `areas`;
CREATE TABLE IF NOT EXISTS `areas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `city_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `zone` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `audit_logs`
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `module` VARCHAR(255) NOT NULL,
  `record_id` VARCHAR(255) NULL DEFAULT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `old_values` TEXT NULL DEFAULT NULL,
  `new_values` TEXT NULL DEFAULT NULL,
  `ip_address` VARCHAR(255) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `brand_ledgers`
DROP TABLE IF EXISTS `brand_ledgers`;
CREATE TABLE IF NOT EXISTS `brand_ledgers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_number` VARCHAR(255) NOT NULL,
  `brand_id` BIGINT UNSIGNED NOT NULL,
  `service_request_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `transaction_type` VARCHAR(255) NOT NULL,
  `debit` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `credit` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `running_balance` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `description` VARCHAR(255) NOT NULL,
  `reference_number` VARCHAR(255) NULL DEFAULT NULL,
  `created_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_reversed` TINYINT(1) NOT NULL DEFAULT '0',
  `reversed_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `reversal_transaction_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `reversal_reason` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `brand_payments`
DROP TABLE IF EXISTS `brand_payments`;
CREATE TABLE IF NOT EXISTS `brand_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_number` VARCHAR(255) NOT NULL,
  `brand_id` BIGINT UNSIGNED NOT NULL,
  `brand_ledger_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_method` VARCHAR(255) NOT NULL,
  `reference_number` VARCHAR(255) NULL DEFAULT NULL,
  `bank_name` VARCHAR(255) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `brands`
DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `contact_person` VARCHAR(255) NULL DEFAULT NULL,
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `logo_path` VARCHAR(255) NULL DEFAULT NULL,
  `default_installation_rate` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `cache`
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NOT NULL,
  `expiration` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `cache_locks`
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `categories`
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `icon` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `cities`
DROP TABLE IF EXISTS `cities`;
CREATE TABLE IF NOT EXISTS `cities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `province` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `customer_addresses`
DROP TABLE IF EXISTS `customer_addresses`;
CREATE TABLE IF NOT EXISTS `customer_addresses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `city_id` BIGINT UNSIGNED NOT NULL,
  `area_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT 'Primary',
  `address_line` TEXT NOT NULL,
  `location_notes` VARCHAR(255) NULL DEFAULT NULL,
  `latitude` DECIMAL(12, 2) NULL DEFAULT NULL,
  `longitude` DECIMAL(12, 2) NULL DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `customers`
DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_code` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `alt_phone` VARCHAR(255) NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `city_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `failed_jobs`
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` VARCHAR(255) NOT NULL,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` TEXT NOT NULL,
  `failed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `job_batches`
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` BIGINT UNSIGNED NOT NULL,
  `pending_jobs` BIGINT UNSIGNED NOT NULL,
  `failed_jobs` BIGINT UNSIGNED NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` TEXT NULL DEFAULT NULL,
  `cancelled_at` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` BIGINT UNSIGNED NOT NULL,
  `finished_at` BIGINT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `job_proofs`
DROP TABLE IF EXISTS `job_proofs`;
CREATE TABLE IF NOT EXISTS `job_proofs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_request_id` BIGINT UNSIGNED NOT NULL,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `proof_type` VARCHAR(255) NOT NULL DEFAULT 'completion',
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `latitude` DECIMAL(12, 2) NULL DEFAULT NULL,
  `longitude` DECIMAL(12, 2) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `jobs`
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` BIGINT UNSIGNED NOT NULL,
  `reserved_at` BIGINT UNSIGNED NULL DEFAULT NULL,
  `available_at` BIGINT UNSIGNED NOT NULL,
  `created_at` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `ledger_transactions`
DROP TABLE IF EXISTS `ledger_transactions`;
CREATE TABLE IF NOT EXISTS `ledger_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_number` VARCHAR(255) NOT NULL,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `service_request_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `transaction_type` VARCHAR(255) NOT NULL,
  `debit` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `credit` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `running_balance` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `description` VARCHAR(255) NOT NULL,
  `reference_number` VARCHAR(255) NULL DEFAULT NULL,
  `created_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_reversed` TINYINT(1) NOT NULL DEFAULT '0',
  `reversed_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `reversal_transaction_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `reversal_reason` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `migrations`
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `personal_access_tokens`
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` TEXT NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `abilities` TEXT NULL DEFAULT NULL,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `service_job_prices`
DROP TABLE IF EXISTS `service_job_prices`;
CREATE TABLE IF NOT EXISTS `service_job_prices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_job_id` BIGINT UNSIGNED NOT NULL,
  `city_id` BIGINT UNSIGNED NOT NULL,
  `customer_price` DECIMAL(12, 2) NOT NULL,
  `worker_payment` DECIMAL(12, 2) NOT NULL,
  `commission` DECIMAL(12, 2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `service_jobs`
DROP TABLE IF EXISTS `service_jobs`;
CREATE TABLE IF NOT EXISTS `service_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `sub_category_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `estimated_duration_minutes` BIGINT UNSIGNED NOT NULL DEFAULT '60',
  `default_customer_price` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `default_worker_payment` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `default_commission` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `service_request_status_history`
DROP TABLE IF EXISTS `service_request_status_history`;
CREATE TABLE IF NOT EXISTS `service_request_status_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_request_id` BIGINT UNSIGNED NOT NULL,
  `from_status` VARCHAR(255) NULL DEFAULT NULL,
  `to_status` VARCHAR(255) NOT NULL,
  `changed_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `worker_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `service_requests`
DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sr_number` VARCHAR(255) NOT NULL,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `city_id` BIGINT UNSIGNED NOT NULL,
  `area_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `sub_category_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `service_job_id` BIGINT UNSIGNED NOT NULL,
  `service_address` TEXT NOT NULL,
  `location_notes` VARCHAR(255) NULL DEFAULT NULL,
  `latitude` DECIMAL(12, 2) NULL DEFAULT NULL,
  `longitude` DECIMAL(12, 2) NULL DEFAULT NULL,
  `customer_complaint` TEXT NULL DEFAULT NULL,
  `special_instructions` TEXT NULL DEFAULT NULL,
  `preferred_date` DATE NULL DEFAULT NULL,
  `preferred_time` VARCHAR(255) NULL DEFAULT NULL,
  `request_source` VARCHAR(255) NOT NULL DEFAULT 'phone',
  `status` VARCHAR(255) NOT NULL DEFAULT 'new',
  `current_worker_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `customer_price` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `worker_payment` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `commission_amount` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `is_ledger_posted` TINYINT(1) NOT NULL DEFAULT '0',
  `cancellation_reason` TEXT NULL DEFAULT NULL,
  `created_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `assigned_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `verified_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `is_free_installation` TINYINT(1) NOT NULL DEFAULT '0',
  `is_brand_job` TINYINT(1) NOT NULL DEFAULT '0',
  `brand_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `model_number` VARCHAR(255) NULL DEFAULT NULL,
  `serial_number` VARCHAR(255) NULL DEFAULT NULL,
  `quantity` BIGINT UNSIGNED NOT NULL DEFAULT '1',
  `brand_rate` DECIMAL(12, 2) NOT NULL DEFAULT '0',
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `duration_minutes` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_brand_ledger_posted` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `sessions`
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(255) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `settings`
DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NULL DEFAULT NULL,
  `group` VARCHAR(255) NOT NULL DEFAULT 'general',
  `type` VARCHAR(255) NOT NULL DEFAULT 'string',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `sub_categories`
DROP TABLE IF EXISTS `sub_categories`;
CREATE TABLE IF NOT EXISTS `sub_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `icon` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `role` VARCHAR(255) NOT NULL DEFAULT 'operations',
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `last_login_ip` VARCHAR(255) NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `brand_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_assignment_history`
DROP TABLE IF EXISTS `worker_assignment_history`;
CREATE TABLE IF NOT EXISTS `worker_assignment_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_request_id` BIGINT UNSIGNED NOT NULL,
  `previous_worker_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `new_worker_id` BIGINT UNSIGNED NOT NULL,
  `reason` TEXT NULL DEFAULT NULL,
  `changed_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_assignments`
DROP TABLE IF EXISTS `worker_assignments`;
CREATE TABLE IF NOT EXISTS `worker_assignments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_request_id` BIGINT UNSIGNED NOT NULL,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `assigned_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT NULL DEFAULT NULL,
  `assigned_at` TIMESTAMP NULL DEFAULT NULL,
  `responded_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_bank_details`
DROP TABLE IF EXISTS `worker_bank_details`;
CREATE TABLE IF NOT EXISTS `worker_bank_details` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `bank_name` VARCHAR(255) NULL DEFAULT NULL,
  `account_title` VARCHAR(255) NULL DEFAULT NULL,
  `account_number` VARCHAR(255) NULL DEFAULT NULL,
  `iban` VARCHAR(255) NULL DEFAULT NULL,
  `verification_status` VARCHAR(255) NOT NULL DEFAULT 'not_verified',
  `verified_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_categories`
DROP TABLE IF EXISTS `worker_categories`;
CREATE TABLE IF NOT EXISTS `worker_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `sub_category_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_documents`
DROP TABLE IF EXISTS `worker_documents`;
CREATE TABLE IF NOT EXISTS `worker_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `document_type` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(255) NULL DEFAULT NULL,
  `mime_type` VARCHAR(255) NULL DEFAULT NULL,
  `file_size_bytes` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT '0',
  `verified_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_payments`
DROP TABLE IF EXISTS `worker_payments`;
CREATE TABLE IF NOT EXISTS `worker_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_number` VARCHAR(255) NOT NULL,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `ledger_transaction_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_method` VARCHAR(255) NOT NULL DEFAULT 'bank_transfer',
  `reference_number` VARCHAR(255) NULL DEFAULT NULL,
  `bank_name` VARCHAR(255) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_references`
DROP TABLE IF EXISTS `worker_references`;
CREATE TABLE IF NOT EXISTS `worker_references` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `relationship` VARCHAR(255) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `worker_service_areas`
DROP TABLE IF EXISTS `worker_service_areas`;
CREATE TABLE IF NOT EXISTS `worker_service_areas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `city_id` BIGINT UNSIGNED NOT NULL,
  `area_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `workers`
DROP TABLE IF EXISTS `workers`;
CREATE TABLE IF NOT EXISTS `workers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_code` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `cnic_number` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `date_of_birth` DATE NULL DEFAULT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `primary_city_id` BIGINT UNSIGNED NOT NULL,
  `primary_category_id` VARCHAR(255) NULL DEFAULT NULL,
  `primary_sub_category_id` VARCHAR(255) NULL DEFAULT NULL,
  `experience_years` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `skill_level` VARCHAR(255) NOT NULL DEFAULT 'intermediate',
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `availability` VARCHAR(255) NOT NULL DEFAULT 'available',
  `password` VARCHAR(255) NOT NULL,
  `device_token` VARCHAR(255) NULL DEFAULT NULL,
  `photo_path` VARCHAR(255) NULL DEFAULT NULL,
  `last_active_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Data Inserts
-- --------------------------------------------------------

-- Dumping data for table `app_notifications` (2 rows)
INSERT INTO `app_notifications` (`id`, `notifiable_type`, `notifiable_id`, `title`, `body`, `type`, `data`, `read_at`, `created_at`, `updated_at`) VALUES (1, 'App\\Models\\Worker', 6, 'New Job Assigned', 'You have been assigned job SR-260924-0002 in Lahore.', 'job_assigned', '{\"service_request_id\":4,\"sr_number\":\"SR-260924-0002\",\"customer_address\":\"144-Pak Park, Near C Block Marghazar Colony, Multan Road\",\"job_title\":\"Free Installation LED TV Xiaomi 55\\\"\",\"wage\":\"1200.00\"}', '2026-09-24 17:00:34', '2026-09-24 15:43:48', '2026-09-24 17:00:34');
INSERT INTO `app_notifications` (`id`, `notifiable_type`, `notifiable_id`, `title`, `body`, `type`, `data`, `read_at`, `created_at`, `updated_at`) VALUES (2, 'App\\Models\\Worker', 7, 'New Job Assigned', 'You have been assigned job SR-260925-0003 in Lahore.', 'job_assigned', '{\"service_request_id\":14,\"sr_number\":\"SR-260925-0003\",\"customer_address\":\"144-Pak Park, Near C Block Marghazar Colony, Multan Road\",\"job_title\":\"Ceiling Fan Installation & Capacitor Replacement\",\"wage\":\"950.00\"}', NULL, '2026-09-25 17:18:55', '2026-09-25 17:18:55');

-- Dumping data for table `areas` (42 rows)
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (1, 1, 'DHA Phase 1-6', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (2, 1, 'Gulberg', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (3, 1, 'Model Town', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (4, 1, 'Johar Town', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (5, 1, 'Bahria Town', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (6, 1, 'Cantt', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (7, 1, 'WAPDA Town', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (8, 1, 'Faisal Town', 'Urban', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (9, 2, 'Clifton', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (10, 2, 'DHA Phase 1-8', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (11, 2, 'Gulshan-e-Iqbal', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (12, 2, 'North Nazimabad', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (13, 2, 'PECHS', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (14, 2, 'Bahria Town Karachi', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (15, 3, 'F-6', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (16, 3, 'F-7', 'Urban', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (17, 3, 'F-10', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (18, 3, 'G-11', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (19, 3, 'E-11', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (20, 3, 'Bahria Enclave', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (21, 3, 'DHA Islamabad', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (22, 4, 'Saddar', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (23, 4, 'Bahria Town Rawalpindi', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (24, 4, 'Westridge', 'Urban', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (25, 4, 'Satellite Town', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (26, 4, 'Chaklala', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (27, 5, 'Civil Lines', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (28, 5, 'Madina Town', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (29, 5, 'D Ground Peoples Colony', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (30, 5, 'Kohinoor City', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (31, 6, 'Cantt', 'Urban', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (32, 6, 'Gulgasht Colony', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (33, 6, 'Bosan Road', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (34, 6, 'Model Town', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (35, 7, 'DC Colony', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (36, 7, 'Model Town', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (37, 7, 'Satellite Town', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (38, 7, 'Wapda Town', 'Urban', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (39, 8, 'Hayatabad', 'Urban', 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (40, 8, 'University Town', 'Urban', 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (41, 8, 'Cantt', 'Urban', 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `areas` (`id`, `city_id`, `name`, `zone`, `status`, `created_at`, `updated_at`) VALUES (42, 8, 'Warsak Road', 'Urban', 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');

-- Dumping data for table `audit_logs` (51 rows)
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (1, 1, 'login', 'auth', '1', 'User logged in: admin@serwizo.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 14:14:37');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (2, 1, 'created', 'workers', '6', 'Worker registered: Iqbal Qasim (SW-W-1006)', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 14:19:03');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (3, 1, 'updated', 'workers', '6', 'Updated worker profile for Iqbal Qasim', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 14:53:24');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (4, 1, 'logout', 'auth', '1', 'User logged out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:28:12');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (5, 2, 'login', 'auth', '2', 'User logged in: operations@serwizo.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:31:25');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (6, 2, 'logout', 'auth', '2', 'User logged out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:32:52');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (7, 3, 'login', 'auth', '3', 'User logged in: accounts@serwizo.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:33:03');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (8, 3, 'logout', 'auth', '3', 'User logged out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:34:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (9, 1, 'login', 'auth', '1', 'User logged in: admin@serwizo.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:34:15');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (10, 1, 'created', 'categories', '31', 'Created subcategory: LED TV', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:37:16');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (11, 1, 'created', 'jobs', '11', 'Created job service: Free Installation LED TV Xiaomi 55\" (Price: Rs. 2000.00, Commission: Rs. 800.00)', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:39:40');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (12, 1, 'created', 'service_requests', '4', 'Created SR-260924-0002 for Naveed Qureshi (Free Installation LED TV Xiaomi 55\")', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:41:54');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (13, 1, 'assigned', 'service_requests', '4', 'Assigned SR-260924-0002 to Iqbal Qasim', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 15:43:48');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (14, 1, 'updated', 'workers', '5', 'Updated worker profile for Bilal Ahmed', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 16:20:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (15, 1, 'updated', 'workers', '1', 'Updated worker profile for Muhammad Rashid', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 16:20:33');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (16, 1, 'login', 'auth', '1', 'User logged in: admin@serwizo.com', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 16:57:26');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (17, 1, 'updated', 'workers', '6', 'Updated worker profile for Iqbal Qasim', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 16:59:41');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (18, 1, 'status_changed', 'service_requests', '4', 'Status changed from assigned to in_progress for SR-260924-0002', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 18:30:58');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (19, 1, 'updated', 'workers', '4', 'Updated worker profile for Farhan Qureshi', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 18:36:27');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (20, NULL, 'created', 'ledger', '2', 'Posted job earning of Rs. 1200 for Iqbal Qasim (SR-260924-0002)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:03:25');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (21, NULL, 'created', 'brand_ledger', '2', 'Posted brand billing of Rs. 1974 for Xiaomi (SR-260924-0002)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:03:25');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (22, NULL, 'status_changed', 'service_requests', '4', 'Status changed from in_progress to completed for SR-260924-0002', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:03:25');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (23, NULL, 'created', 'ledger', '3', 'Posted job earning of Rs. 1760 for Iqbal Qasim (SR-260924-0003)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:10:52');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (24, NULL, 'status_changed', 'service_requests', '5', 'Status changed from in_progress to completed for SR-260924-0003', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:10:52');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (25, 1, 'updated', 'workers', '5', 'Updated worker profile for Bilal Ahmed', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 19:12:34');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (26, 1, 'updated', 'workers', '5', 'Updated worker profile for Bilal Ahmed', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 19:12:35');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (27, NULL, 'created', 'ledger', '4', 'Posted job earning of Rs. 2080 for Bilal Ahmed (SR-260924-0005)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:23:58');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (28, NULL, 'status_changed', 'service_requests', '7', 'Status changed from in_progress to completed for SR-260924-0005', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:23:58');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (29, NULL, 'created', 'ledger', '5', 'Posted job earning of Rs. 2000 for Bilal Ahmed (SR-260924-0004)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:24:42');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (30, NULL, 'status_changed', 'service_requests', '6', 'Status changed from in_progress to completed for SR-260924-0004', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:24:42');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (31, 1, 'updated', 'workers', '3', 'Updated worker profile for Khurram Shehzad', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 19:32:43');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (32, 1, 'updated', 'workers', '3', 'Updated worker profile for Khurram Shehzad', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 19:33:35');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (33, NULL, 'created', 'ledger', '6', 'Posted job earning of Rs. 1760 for Khurram Shehzad (SR-260924-0008)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:36:43');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (34, NULL, 'status_changed', 'service_requests', '10', 'Status changed from in_progress to completed for SR-260924-0008', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 19:36:43');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (35, 1, 'deleted', 'service_requests', '8', 'Deleted service request SR-260924-0006 with full financial reversal. Reason: Deleted by Super Admin', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 19:59:04');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (36, 1, 'deleted', 'service_requests', '9', 'Deleted service request SR-260924-0007 with full financial reversal. Reason: Deleted by Super Admin', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 19:59:15');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (37, NULL, 'created', 'ledger', '7', 'Posted job earning of Rs. 2000 for Khurram Shehzad (SR-260924-0009)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 20:01:59');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (38, NULL, 'status_changed', 'service_requests', '11', 'Status changed from in_progress to completed for SR-260924-0009', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 20:01:59');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (39, 1, 'updated', 'workers', '2', 'Updated worker profile for Tariq Mehmood', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-24 20:03:38');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (40, NULL, 'created', 'ledger', '8', 'Posted job earning of Rs. 2400 for Tariq Mehmood (SR-260902-0002)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 20:04:52');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (41, NULL, 'status_changed', 'service_requests', '2', 'Status changed from in_progress to completed for SR-260902-0002', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-24 20:04:52');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (42, 1, 'login', 'auth', '1', 'User logged in: admin@serwizo.com', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:07:35');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (43, 1, 'created', 'service_requests', '12', 'Created SR-260925-0001 for Tariq (Ceiling Fan Installation & Capacitor Replacement)', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:12:34');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (44, 1, 'created', 'service_requests', '13', 'Created SR-260925-0002 for Tariq (Main Distribution Board (DB) Troubleshooting)', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:14:13');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (45, 1, 'created', 'workers', '7', 'Worker registered: Aftab Iqbal (SW-W-1007)', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:16:23');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (46, 1, 'updated', 'workers', '7', 'Updated worker profile for Aftab Iqbal', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:17:58');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (47, 1, 'created', 'service_requests', '14', 'Created SR-260925-0003 for Naveed Qureshi (Ceiling Fan Installation & Capacitor Replacement)', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:18:44');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (48, 1, 'assigned', 'service_requests', '14', 'Assigned SR-260925-0003 to Aftab Iqbal', NULL, NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', '2026-09-25 17:18:55');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (49, NULL, 'status_changed', 'service_requests', '14', 'Status changed from assigned to in_progress for SR-260925-0003', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-25 17:19:27');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (50, NULL, 'created', 'ledger', '9', 'Posted job earning of Rs. 950 for Aftab Iqbal (SR-260925-0003)', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-25 17:20:57');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (51, NULL, 'status_changed', 'service_requests', '14', 'Status changed from in_progress to completed for SR-260925-0003', NULL, NULL, '192.168.100.73', 'Dalvik/2.1.0 (Linux; U; Android 11; CPH2349 Build/RP1A.200720.011)', '2026-09-25 17:20:57');

-- Dumping data for table `brand_ledgers` (2 rows)
INSERT INTO `brand_ledgers` (`id`, `transaction_number`, `brand_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (1, 'BTX-2026-00001', 1, 3, 'job_billing', 2500, 0, 2500, 'Installation Billing: SR-260902-0004 (Model: HS-INV-1.5T-COOL)', 'SR-260902-0004', 1, 0, NULL, NULL, NULL, '2026-09-23 12:00:00', '2026-09-24 14:02:48');
INSERT INTO `brand_ledgers` (`id`, `transaction_number`, `brand_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (2, 'BTX-2026-00002', 4, 4, 'job_billing', 1974, 0, 1974, 'Installation Billing: SR-260924-0002', 'SR-260924-0002', NULL, 0, NULL, NULL, NULL, '2026-09-24 19:03:25', '2026-09-24 19:03:25');

-- Dumping data for table `brands` (7 rows)
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 'Hisense', 'HISENSE', 'Imran Khan', '03008881122', 'service@hisense.pk', NULL, 2500, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 'Gree', 'GREE', 'Adnan Siddiqui', '03009993344', 'warranty@gree.pk', NULL, 2800, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 'Kenwood', 'KENWOOD', 'Tariq Javed', '03007775566', 'support@kenwood.pk', NULL, 2600, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 'Xiaomi', 'XIAOMI', 'Smart Tech Team', '03006664433', 'service@xiaomi.pk', NULL, 2200, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 'Midea', 'MIDEA', 'Operations Head', '03005553322', 'info@midea.pk', NULL, 2400, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 'EcoStar', 'ECOSTAR', 'Customer Care', '03004442211', 'claims@ecostar.pk', NULL, 2300, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);
INSERT INTO `brands` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `logo_path`, `default_installation_rate`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 'TCL', 'TCL', 'Warranty Desk', '03003331100', 'service@tcl.pk', NULL, 2500, 'active', NULL, '2026-09-24 14:02:46', '2026-09-24 14:02:46', NULL);

-- Dumping data for table `categories` (6 rows)
INSERT INTO `categories` (`id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (1, 'Appliances', 'appliances', 'Home appliances maintenance, installation and repair', 'fa-plug', 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `categories` (`id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (2, 'Electrician', 'electrician', 'Electrical wiring, fixtures, circuits and troubleshooting', 'fa-bolt', 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `categories` (`id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (3, 'Plumbing', 'plumbing', 'Sanitary fitting, pipeline repairs, pumps and drain unblocking', 'fa-wrench', 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `categories` (`id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (4, 'Cleaning', 'cleaning', 'Professional deep cleaning and fumigation services', 'fa-broom', 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `categories` (`id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (5, 'Car Wash & Detailing', 'car-wash-detailing', 'Doorstep waterless wash, polish and interior detailing', 'fa-car', 'active', '2026-09-24 14:02:33', '2026-09-24 14:02:33');
INSERT INTO `categories` (`id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (6, 'Solar', 'solar', 'Solar panels installation, inverter configuration and cleaning', 'fa-sun', 'active', '2026-09-24 14:02:34', '2026-09-24 14:02:34');

-- Dumping data for table `cities` (8 rows)
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (1, 'Lahore', 'Punjab', 'active', '2026-09-24 14:02:25', '2026-09-24 14:02:25');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (2, 'Karachi', 'Sindh', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (3, 'Islamabad', 'Federal', 'active', '2026-09-24 14:02:26', '2026-09-24 14:02:26');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (4, 'Rawalpindi', 'Punjab', 'active', '2026-09-24 14:02:27', '2026-09-24 14:02:27');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (5, 'Faisalabad', 'Punjab', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (6, 'Multan', 'Punjab', 'active', '2026-09-24 14:02:28', '2026-09-24 14:02:28');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (7, 'Gujranwala', 'Punjab', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');
INSERT INTO `cities` (`id`, `name`, `province`, `status`, `created_at`, `updated_at`) VALUES (8, 'Peshawar', 'KPK', 'active', '2026-09-24 14:02:29', '2026-09-24 14:02:29');

-- Dumping data for table `customer_addresses` (6 rows)
INSERT INTO `customer_addresses` (`id`, `customer_id`, `city_id`, `area_id`, `title`, `address_line`, `location_notes`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES (1, 1, 1, NULL, 'Home', 'House 88-B, Sector Z, Phase 3, DHA, Lahore', NULL, NULL, NULL, 1, '2026-09-24 14:02:44', '2026-09-24 14:02:44');
INSERT INTO `customer_addresses` (`id`, `customer_id`, `city_id`, `area_id`, `title`, `address_line`, `location_notes`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES (2, 2, 1, NULL, 'Home', 'Flat 4B, Gulberg Heights, Main Boulevard Gulberg, Lahore', NULL, NULL, NULL, 1, '2026-09-24 14:02:44', '2026-09-24 14:02:44');
INSERT INTO `customer_addresses` (`id`, `customer_id`, `city_id`, `area_id`, `title`, `address_line`, `location_notes`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES (3, 3, 2, NULL, 'Home', 'Bungalow 12, 26th Street, Phase 5 DHA, Karachi', NULL, NULL, NULL, 1, '2026-09-24 14:02:44', '2026-09-24 14:02:44');
INSERT INTO `customer_addresses` (`id`, `customer_id`, `city_id`, `area_id`, `title`, `address_line`, `location_notes`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES (4, 4, 3, NULL, 'Home', 'House 140, Street 22, F-10/2, Islamabad', NULL, NULL, NULL, 1, '2026-09-24 14:02:45', '2026-09-24 14:02:45');
INSERT INTO `customer_addresses` (`id`, `customer_id`, `city_id`, `area_id`, `title`, `address_line`, `location_notes`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES (5, 5, 1, NULL, 'Primary', '144-Pak Park, Near C Block Marghazar Colony, Multan Road', 'asdf', NULL, NULL, 1, '2026-09-24 15:41:54', '2026-09-24 15:41:54');
INSERT INTO `customer_addresses` (`id`, `customer_id`, `city_id`, `area_id`, `title`, `address_line`, `location_notes`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES (6, 10, 1, NULL, 'Primary', '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, NULL, NULL, 1, '2026-09-25 17:12:34', '2026-09-25 17:12:34');

-- Dumping data for table `customers` (10 rows)
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 'SW-C-1001', 'Dr. Kamran Malik', '03001239876', NULL, 'kamran.malik@gmail.com', 1, 'House 88-B, Sector Z, Phase 3, DHA, Lahore', NULL, '2026-09-24 14:02:44', '2026-09-24 14:02:44', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 'SW-C-1002', 'Syeda Fatima Zahra', '03215556644', NULL, 'fatima.zahra@hotmail.com', 1, 'Flat 4B, Gulberg Heights, Main Boulevard Gulberg, Lahore', NULL, '2026-09-24 14:02:44', '2026-09-24 14:02:44', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 'SW-C-1003', 'Muhammad Ali Khan', '03334447788', NULL, 'ali.khan@corporation.pk', 2, 'Bungalow 12, 26th Street, Phase 5 DHA, Karachi', NULL, '2026-09-24 14:02:44', '2026-09-24 14:02:44', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 'SW-C-1004', 'Zeeshan Baig', '03456789012', NULL, 'zeeshan.baig@outlook.com', 3, 'House 140, Street 22, F-10/2, Islamabad', NULL, '2026-09-24 14:02:44', '2026-09-24 14:02:44', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 'SW-C-1005', 'Naveed Qureshi', '03204410450', NULL, 'naveed@serwizo.com', 1, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, '2026-09-24 15:41:54', '2026-09-24 15:41:54', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 'SW-C-1006', 'Abc khan', '03001000000', NULL, NULL, 3, 'H1', NULL, '2026-09-24 19:13:34', '2026-09-24 19:13:34', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 'SW-C-1007', 'Mohsin Hasan Khan', '03001111111', NULL, NULL, 3, 'J5', NULL, '2026-09-24 19:21:04', '2026-09-24 19:21:04', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (8, 'SW-C-1008', 'Ijaz butt', '035555555', NULL, NULL, 1, 'Abv', NULL, '2026-09-24 19:34:28', '2026-09-24 19:34:28', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (9, 'SW-C-1009', 'Nofal', '035656565', NULL, NULL, 1, 'Jjh', NULL, '2026-09-24 19:41:59', '2026-09-24 19:41:59', NULL);
INSERT INTO `customers` (`id`, `customer_code`, `name`, `phone`, `alt_phone`, `email`, `city_id`, `address`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (10, 'SW-C-1010', 'Tariq', '03265546546', NULL, NULL, 1, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, '2026-09-25 17:12:34', '2026-09-25 17:12:34', NULL);

-- Dumping data for table `ledger_transactions` (9 rows)
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (1, 'TX-2026-00001', 1, 1, 'job_earning', 0, 2000, 2000, 'Earning for SR-260901-0001 - AC General Service (Split / Inverter)', 'SR-260901-0001', 1, 0, NULL, NULL, NULL, '2026-09-23 14:00:00', '2026-09-24 14:02:45');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (2, 'TX-2026-00002', 6, 4, 'job_earning', 0, 1200, 1200, 'Earning for SR-260924-0002 - Free Installation LED TV Xiaomi 55\"', 'SR-260924-0002', NULL, 0, NULL, NULL, NULL, '2026-09-24 19:03:25', '2026-09-24 19:03:25');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (3, 'TX-2026-00003', 6, 5, 'job_earning', 0, 1760, 2960, 'Earning for SR-260924-0003 - AC General Service (Split / Inverter)', 'SR-260924-0003', NULL, 0, NULL, NULL, NULL, '2026-09-24 19:10:52', '2026-09-24 19:10:52');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (4, 'TX-2026-00004', 5, 7, 'job_earning', 0, 2080, 2080, 'Earning for SR-260924-0005 - Solar Panels Deep Cleaning (Up to 10kW system)', 'SR-260924-0005', NULL, 0, NULL, NULL, NULL, '2026-09-24 19:23:58', '2026-09-24 19:23:58');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (5, 'TX-2026-00005', 5, 6, 'job_earning', 0, 2000, 4080, 'Earning for SR-260924-0004 - Solar Panels Deep Cleaning (Up to 10kW system)', 'SR-260924-0004', NULL, 0, NULL, NULL, NULL, '2026-09-24 19:24:42', '2026-09-24 19:24:42');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (6, 'TX-2026-00006', 3, 10, 'job_earning', 0, 1760, 1760, 'Earning for SR-260924-0008 - PPRC / CPVC Concealed Pipe Leakage Repair', 'SR-260924-0008', NULL, 0, NULL, NULL, NULL, '2026-09-24 19:36:43', '2026-09-24 19:36:43');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (7, 'TX-2026-00007', 3, 11, 'job_earning', 0, 2000, 3760, 'Earning for SR-260924-0009 - PPRC / CPVC Concealed Pipe Leakage Repair', 'SR-260924-0009', NULL, 0, NULL, NULL, NULL, '2026-09-24 20:01:59', '2026-09-24 20:01:59');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (8, 'TX-2026-00008', 2, 2, 'job_earning', 0, 2400, 2400, 'Earning for SR-260902-0002 - Main Distribution Board (DB) Troubleshooting', 'SR-260902-0002', NULL, 0, NULL, NULL, NULL, '2026-09-24 20:04:52', '2026-09-24 20:04:52');
INSERT INTO `ledger_transactions` (`id`, `transaction_number`, `worker_id`, `service_request_id`, `transaction_type`, `debit`, `credit`, `running_balance`, `description`, `reference_number`, `created_by_user_id`, `is_reversed`, `reversed_by_user_id`, `reversal_transaction_id`, `reversal_reason`, `created_at`, `updated_at`) VALUES (9, 'TX-2026-00009', 7, 14, 'job_earning', 0, 950, 950, 'Earning for SR-260925-0003 - Ceiling Fan Installation & Capacitor Replacement', 'SR-260925-0003', NULL, 0, NULL, NULL, NULL, '2026-09-25 17:20:57', '2026-09-25 17:20:57');

-- Dumping data for table `migrations` (16 rows)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4, '2026_09_21_195320_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5, '2026_09_22_000001_update_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6, '2026_09_22_000002_create_cities_and_areas_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7, '2026_09_22_000003_create_categories_and_sub_categories_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8, '2026_09_22_000004_create_service_jobs_and_prices_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9, '2026_09_22_000005_create_workers_and_related_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10, '2026_09_22_000006_create_customers_and_addresses_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11, '2026_09_22_000007_create_service_requests_and_workflows_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12, '2026_09_22_000008_create_worker_ledgers_and_payments_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13, '2026_09_22_000009_create_notifications_and_audit_logs_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14, '2026_09_24_000001_create_brands_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15, '2026_09_24_000002_add_brand_and_installation_fields_to_service_requests_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16, '2026_09_24_000003_create_brand_ledgers_and_payments_tables', 1);

-- Dumping data for table `personal_access_tokens` (5 rows)
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (1, 'App\\Models\\Worker', 6, 'worker-app-token', '20add7ee6987338e890e198cf0543ebe50ffdca80092af7955fdf4b1ea3e5078', '[\"*\"]', '2026-09-24 17:50:46', NULL, '2026-09-24 16:59:53', '2026-09-24 17:50:46');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (4, 'App\\Models\\Worker', 6, 'worker-app-token', 'd8a0f9673b89418eb9aaee0d9658395408980dba09ff81f837fc65cba191eac1', '[\"*\"]', '2026-09-24 18:45:09', NULL, '2026-09-24 18:41:45', '2026-09-24 18:45:09');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (6, 'App\\Models\\Worker', 5, 'worker-app-token', 'addea9500ca13399e1598d948ccd46ef7afcb6781d8d3daf5a9d226ef8c0e677', '[\"*\"]', '2026-09-24 19:25:01', NULL, '2026-09-24 19:12:43', '2026-09-24 19:25:01');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (7, 'App\\Models\\Worker', 3, 'worker-app-token', '31079448535da09fc5ce9f4fd7968673244ca68f88489958b545e852eb35328c', '[\"*\"]', '2026-09-24 19:50:13', NULL, '2026-09-24 19:33:40', '2026-09-24 19:50:13');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (10, 'App\\Models\\Worker', 7, 'worker-app-token', '836f0f44c4945051ab2ff764b41f6227af71614e43c6648394f9fdef018e733f', '[\"*\"]', '2026-09-25 17:21:45', NULL, '2026-09-25 17:18:06', '2026-09-25 17:21:45');

-- Dumping data for table `service_jobs` (11 rows)
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (1, 1, 1, 'AC General Service (Split / Inverter)', 'APP-AC-01', 'Complete indoor/outdoor high pressure wash, filter cleaning, and gas check.', 60, 2500, 2000, 500, 'active', '2026-09-24 14:02:35', '2026-09-24 14:02:35');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (2, 1, 1, 'AC Gas Refill (R410/R32/R22)', 'APP-AC-02', 'Leakage testing, vacuuming, and complete refrigerant gas charging.', 90, 7500, 6000, 1500, 'active', '2026-09-24 14:02:35', '2026-09-24 14:02:35');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (3, 1, 2, 'Refrigerator Gas Refill & Cooling Repair', 'APP-REF-01', 'Compressor inspection, thermostat check, capillary unblocking, and gas refill.', 120, 6000, 4800, 1200, 'active', '2026-09-24 14:02:35', '2026-09-24 14:02:35');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (4, 1, 3, 'Automatic Washing Machine Repair & Service', 'APP-WM-01', 'Drain valve, motor belt, PCB card diagnostic, and spin tub service.', 90, 3500, 2800, 700, 'active', '2026-09-24 14:02:35', '2026-09-24 14:02:35');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (5, 2, 10, 'Ceiling Fan Installation & Capacitor Replacement', 'ELEC-FAN-01', 'Ceiling bracket mounting, wiring connection, balancing, and speed test.', 45, 1200, 950, 250, 'active', '2026-09-24 14:02:35', '2026-09-24 14:02:35');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (6, 2, 9, 'Main Distribution Board (DB) Troubleshooting', 'ELEC-DB-01', 'Tripping breaker identification, load distribution, and earthing inspection.', 90, 3000, 2400, 600, 'active', '2026-09-24 14:02:36', '2026-09-24 14:02:36');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (7, 3, 13, 'PPRC / CPVC Concealed Pipe Leakage Repair', 'PLUMB-LEAK-01', 'Wall opening, hot plate joint welding, and pressure testing.', 120, 3500, 2800, 700, 'active', '2026-09-24 14:02:36', '2026-09-24 14:02:36');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (8, 3, 15, 'Water Motor / Donkey Pump Repair & Overhaul', 'PLUMB-PUMP-01', 'Bearing replacement, seal fixing, winding check, and priming.', 90, 2800, 2200, 600, 'active', '2026-09-24 14:02:36', '2026-09-24 14:02:36');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (9, 4, 19, '7-Seater Sofa Deep Shampoo & Extraction', 'CLN-SOFA-01', 'Industrial foam shampooing, spot treatment, and high-power vacuum extraction.', 120, 4500, 3600, 900, 'active', '2026-09-24 14:02:36', '2026-09-24 14:02:36');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (10, 6, 27, 'Solar Panels Deep Cleaning (Up to 10kW system)', 'SOLAR-CLN-01', 'De-ionized water washing, soft microfiber brushing, and efficiency check.', 90, 3000, 2400, 600, 'active', '2026-09-24 14:02:36', '2026-09-24 14:02:36');
INSERT INTO `service_jobs` (`id`, `category_id`, `sub_category_id`, `title`, `code`, `description`, `estimated_duration_minutes`, `default_customer_price`, `default_worker_payment`, `default_commission`, `status`, `created_at`, `updated_at`) VALUES (11, 1, 31, 'Free Installation LED TV Xiaomi 55\"', 'APP-TV-001', 'Free Installation', 60, 2000, 1200, 800, 'active', '2026-09-24 15:39:40', '2026-09-24 15:39:40');

-- Dumping data for table `service_request_status_history` (28 rows)
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (1, 1, NULL, 'new', 2, NULL, 'Customer called to book service.', '2026-09-23 08:00:00');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (2, 1, 'new', 'assigned', 2, NULL, 'Assigned to Muhammad Rashid', '2026-09-23 09:00:00');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (3, 1, 'assigned', 'completed', NULL, 1, 'Job completed and verified with customer.', '2026-09-23 13:00:00');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (4, 1, 'completed', 'verified', 1, NULL, 'Operations QA call completed. 5-star customer rating.', '2026-09-23 14:00:00');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (5, 2, 'new', 'in_progress', NULL, 2, 'Worker arrived at location and diagnosing breaker box.', '2026-09-24 14:00:00');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (6, 4, NULL, 'new', 1, NULL, 'Service Request created via whatsapp', '2026-09-24 15:41:54');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (7, 4, 'assigned', 'assigned', 1, NULL, 'Assigned to worker Iqbal Qasim (SW-W-1006)', '2026-09-24 15:43:48');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (8, 4, 'assigned', 'in_progress', 1, NULL, '645', '2026-09-24 18:30:58');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (9, 4, 'in_progress', 'completed', NULL, 6, 'Job completed via mobile app.', '2026-09-24 19:03:25');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (10, 5, NULL, 'in_progress', NULL, 6, 'Job initiated on-site by worker Iqbal Qasim', '2026-09-24 19:04:52');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (11, 5, 'in_progress', 'completed', NULL, 6, 'Job completed via mobile app.', '2026-09-24 19:10:52');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (12, 6, NULL, 'in_progress', NULL, 5, 'Job initiated on-site by worker Bilal Ahmed', '2026-09-24 19:13:34');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (13, 7, NULL, 'in_progress', NULL, 5, 'Job initiated on-site by worker Bilal Ahmed', '2026-09-24 19:21:04');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (14, 8, NULL, 'in_progress', NULL, 5, 'Job initiated on-site by worker Bilal Ahmed', '2026-09-24 19:21:10');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (15, 9, NULL, 'in_progress', NULL, 5, 'Job initiated on-site by worker Bilal Ahmed', '2026-09-24 19:21:18');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (16, 7, 'in_progress', 'completed', NULL, 5, 'Job completed via mobile app.', '2026-09-24 19:23:58');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (17, 6, 'in_progress', 'completed', NULL, 5, 'Job completed via mobile app.', '2026-09-24 19:24:42');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (18, 10, NULL, 'in_progress', NULL, 3, 'Job initiated on-site by worker Khurram Shehzad', '2026-09-24 19:34:28');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (19, 10, 'in_progress', 'completed', NULL, 3, 'Job completed via mobile app.', '2026-09-24 19:36:43');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (20, 11, NULL, 'in_progress', NULL, 3, 'Job initiated on-site by worker Khurram Shehzad', '2026-09-24 19:42:00');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (21, 11, 'in_progress', 'completed', NULL, 3, 'Job completed via mobile app.', '2026-09-24 20:01:59');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (22, 2, 'in_progress', 'completed', NULL, 2, 'Job completed via mobile app.', '2026-09-24 20:04:52');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (23, 12, NULL, 'new', 1, NULL, 'Service Request created via website', '2026-09-25 17:12:34');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (24, 13, NULL, 'new', 1, NULL, 'Service Request created via phone', '2026-09-25 17:14:13');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (25, 14, NULL, 'new', 1, NULL, 'Service Request created via phone', '2026-09-25 17:18:44');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (26, 14, 'assigned', 'assigned', 1, NULL, 'Assigned to worker Aftab Iqbal (SW-W-1007)', '2026-09-25 17:18:55');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (27, 14, 'assigned', 'in_progress', NULL, 7, 'Worker started the job.', '2026-09-25 17:19:27');
INSERT INTO `service_request_status_history` (`id`, `service_request_id`, `from_status`, `to_status`, `changed_by_user_id`, `worker_id`, `notes`, `created_at`) VALUES (28, 14, 'in_progress', 'completed', NULL, 7, 'Job completed via mobile app.', '2026-09-25 17:20:57');

-- Dumping data for table `service_requests` (14 rows)
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (1, 'SR-260901-0001', 1, 1, NULL, 1, 1, 1, 'House 88-B, Sector Z, Phase 3, DHA, Lahore', NULL, NULL, NULL, 'AC indoor unit making rattling noise and low cooling.', 'Bring extension cord and ladder.', '2026-09-23 00:00:00', '11:00 AM - 01:00 PM', 'phone', 'verified', 1, 2500, 2000, 500, 1, NULL, 2, '2026-09-23 09:00:00', '2026-09-23 13:00:00', '2026-09-23 14:00:00', 1, '2026-09-23 08:00:00', '2026-09-24 14:02:45', NULL, 0, 0, NULL, NULL, NULL, 1, 0, NULL, NULL, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (2, 'SR-260902-0002', 2, 1, NULL, 2, 9, 6, 'Flat 4B, Gulberg Heights, Main Boulevard Gulberg, Lahore', NULL, NULL, NULL, 'Main breaker tripping frequently when AC is turned on.', NULL, '2026-09-24 00:00:00', '02:00 PM - 04:00 PM', 'whatsapp', 'completed', 2, 3000, 2400, 600, 1, NULL, 2, '2026-09-24 10:00:00', '2026-09-24 20:04:52', NULL, NULL, '2026-09-24 09:00:00', '2026-09-24 20:04:52', NULL, 0, 0, NULL, NULL, NULL, 1, 0, NULL, 604, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (3, 'SR-260902-0004', 1, 1, NULL, 1, 1, 1, 'House 88-B, Sector Z, Phase 3, DHA, Lahore', NULL, NULL, NULL, 'Brand New AC Free Installation', NULL, NULL, NULL, 'brand', 'verified', 1, 0, 2000, 500, 1, NULL, 2, '2026-09-23 10:00:00', '2026-09-23 11:45:00', '2026-09-23 12:00:00', 1, '2026-09-23 09:00:00', '2026-09-24 14:02:48', NULL, 1, 1, 1, 'HS-INV-1.5T-COOL', 'HS9876543210', 1, 2500, '2026-09-23 10:30:00', 75, 1);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (4, 'SR-260924-0002', 5, 1, NULL, 1, 31, 11, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', 'asdf', NULL, NULL, 'dsdfsadfasdf', 'asdf', '2026-09-25 00:00:00', '10:00 AM - 12:00 PM', 'whatsapp', 'completed', 6, 0, 1200, 774, 1, NULL, 1, '2026-09-24 15:43:48', '2026-09-24 19:03:25', NULL, NULL, '2026-09-24 15:41:54', '2026-09-24 19:03:25', NULL, 1, 1, 4, NULL, NULL, 1, 1974, '2026-09-24 18:30:58', 32, 1);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (5, 'SR-260924-0003', 5, 1, NULL, 1, NULL, 1, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'completed', 6, 0, 1760, 440, 1, NULL, NULL, '2026-09-24 19:04:51', '2026-09-24 19:10:52', NULL, NULL, '2026-09-24 19:04:51', '2026-09-24 19:10:52', NULL, 1, 1, NULL, 'JGVGG', 'XI-SN-987410', 1, 2200, '2026-09-24 19:04:51', 6, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (6, 'SR-260924-0004', 6, 3, NULL, 6, NULL, 10, 'H1', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'completed', 5, 0, 2000, 500, 1, NULL, NULL, '2026-09-24 19:13:34', '2026-09-24 19:24:42', NULL, NULL, '2026-09-24 19:13:34', '2026-09-24 19:24:42', NULL, 1, 1, NULL, 'JGVGG', 'JNHY67', 1, 2500, '2026-09-24 19:13:34', 11, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (7, 'SR-260924-0005', 7, 3, NULL, 6, NULL, 10, 'J5', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'completed', 5, 0, 2080, 520, 1, NULL, NULL, '2026-09-24 19:21:04', '2026-09-24 19:23:58', NULL, NULL, '2026-09-24 19:21:04', '2026-09-24 19:23:58', NULL, 1, 1, NULL, 'JGVHJJ', 'HGGGGHH', 1, 2600, '2026-09-24 19:21:04', 2, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (8, 'SR-260924-0006', 7, 3, NULL, 6, NULL, 10, 'J5', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'in_progress', 5, 0, 2080, 520, 0, NULL, NULL, '2026-09-24 19:21:10', NULL, NULL, NULL, '2026-09-24 19:21:10', '2026-09-24 19:59:04', '2026-09-24 19:59:04', 1, 1, NULL, 'JGVHJJ', 'HGGGGHH', 1, 2600, '2026-09-24 19:21:10', NULL, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (9, 'SR-260924-0007', 7, 3, NULL, 6, NULL, 10, 'J5', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'in_progress', 5, 0, 2080, 520, 0, NULL, NULL, '2026-09-24 19:21:17', NULL, NULL, NULL, '2026-09-24 19:21:17', '2026-09-24 19:59:15', '2026-09-24 19:59:15', 1, 1, NULL, 'JGVHJJ', 'HGGGGHH', 1, 2600, '2026-09-24 19:21:17', NULL, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (10, 'SR-260924-0008', 8, 1, NULL, 3, NULL, 7, 'Abv', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'completed', 3, 0, 1760, 440, 1, NULL, NULL, '2026-09-24 19:34:28', '2026-09-24 19:36:43', NULL, NULL, '2026-09-24 19:34:28', '2026-09-24 19:36:43', NULL, 1, 1, NULL, 'HGGG', 'NBBH', 1, 2200, '2026-09-24 19:34:28', 2, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (11, 'SR-260924-0009', 9, 1, NULL, 3, NULL, 7, 'Jjh', NULL, NULL, NULL, 'Brand Free Installation', NULL, NULL, NULL, 'app', 'completed', 3, 0, 2000, 500, 1, NULL, NULL, '2026-09-24 19:41:59', '2026-09-24 20:01:59', NULL, NULL, '2026-09-24 19:41:59', '2026-09-24 20:01:59', NULL, 1, 1, NULL, 'GGG', 'VVV', 1, 2500, '2026-09-24 19:41:59', 20, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (12, 'SR-260925-0001', 10, 1, NULL, 2, 10, 5, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, NULL, NULL, NULL, NULL, '2026-09-25 00:00:00', '10:00 AM - 12:00 PM', 'website', 'new', NULL, 1200, 950, 250, 0, NULL, 1, NULL, NULL, NULL, NULL, '2026-09-25 17:12:34', '2026-09-25 17:12:34', NULL, 0, 0, NULL, NULL, NULL, 1, 0, NULL, NULL, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (13, 'SR-260925-0002', 10, 1, NULL, 2, 9, 6, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, NULL, NULL, NULL, NULL, '2026-09-25 00:00:00', '10:00 AM - 12:00 PM', 'phone', 'new', NULL, 3000, 2400, 600, 0, NULL, 1, NULL, NULL, NULL, NULL, '2026-09-25 17:14:13', '2026-09-25 17:14:13', NULL, 0, 0, NULL, NULL, NULL, 1, 0, NULL, NULL, 0);
INSERT INTO `service_requests` (`id`, `sr_number`, `customer_id`, `city_id`, `area_id`, `category_id`, `sub_category_id`, `service_job_id`, `service_address`, `location_notes`, `latitude`, `longitude`, `customer_complaint`, `special_instructions`, `preferred_date`, `preferred_time`, `request_source`, `status`, `current_worker_id`, `customer_price`, `worker_payment`, `commission_amount`, `is_ledger_posted`, `cancellation_reason`, `created_by_user_id`, `assigned_at`, `completed_at`, `verified_at`, `verified_by_user_id`, `created_at`, `updated_at`, `deleted_at`, `is_free_installation`, `is_brand_job`, `brand_id`, `model_number`, `serial_number`, `quantity`, `brand_rate`, `started_at`, `duration_minutes`, `is_brand_ledger_posted`) VALUES (14, 'SR-260925-0003', 5, 1, NULL, 2, 10, 5, '144-Pak Park, Near C Block Marghazar Colony, Multan Road', NULL, NULL, NULL, NULL, NULL, '2026-09-25 00:00:00', '10:00 AM - 12:00 PM', 'phone', 'completed', 7, 1200, 950, 250, 1, NULL, 1, '2026-09-25 17:18:55', '2026-09-25 17:20:57', NULL, NULL, '2026-09-25 17:18:44', '2026-09-25 17:20:57', NULL, 0, 0, NULL, NULL, NULL, 1, 0, '2026-09-25 17:19:27', 1, 0);

-- Dumping data for table `sessions` (3 rows)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('DhpqNbrvlSsthlZre76iDBYnje41vU4cZCMbAH1M', NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', 'eyJfdG9rZW4iOiJ0d24zV0ZxVjl5RVo4aUhyY2Y2Wk1Rd2VHSkR1UXRPTmNua2tNWTNsIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTkyLjE2OC4xMDAuMTAwOjgwMDAifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzE5Mi4xNjguMTAwLjEwMDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1790356036);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('2fzRx8LaeT5lsEaOn71G2FAfPIH2FIdLhqcEvFA1', 1, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', 'eyJfdG9rZW4iOiJCUk1YclVRaW56WTZFbE1SZHNkWHlzaUUxZllWMzlNNUVWVmZsajQyIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzE5Mi4xNjguMTAwLjEwMDo4MDAwXC9zZXJ2aWNlLXJlcXVlc3RzXC9jcmVhdGUiLCJyb3V0ZSI6InNlcnZpY2UtcmVxdWVzdHMuY3JlYXRlIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9', 1790357155);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('SllwA9z6l6u97dhcy5eCOg1oGPEKN3qLimEzPe0e', NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 OPR/135.0.0.0', 'eyJfdG9rZW4iOiJNU0JRbDU1WG9aVWRmZm9ZcjFyQUhrcmlqYjRBNGV5bXg5eTdaMVdWIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTkyLjE2OC4xMDAuMTAwOjgwMDAifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzE5Mi4xNjguMTAwLjEwMDo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1790366221);

-- Dumping data for table `settings` (6 rows)
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (1, 'app_name', 'SerWizo Service Management', 'general', 'string', '2026-09-24 14:02:24', '2026-09-24 14:02:24');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (2, 'currency', 'PKR', 'general', 'string', '2026-09-24 14:02:24', '2026-09-24 14:02:24');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (3, 'currency_symbol', 'Rs.', 'general', 'string', '2026-09-24 14:02:24', '2026-09-24 14:02:24');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (4, 'support_phone', '042-111-SERWIZO', 'general', 'string', '2026-09-24 14:02:24', '2026-09-24 14:02:24');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (5, 'support_email', 'support@serwizo.com', 'general', 'string', '2026-09-24 14:02:24', '2026-09-24 14:02:24');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (6, 'auto_post_ledger_on_verified', '1', 'financial', 'boolean', '2026-09-24 14:02:25', '2026-09-24 14:02:25');

-- Dumping data for table `sub_categories` (31 rows)
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (1, 1, 'AC', 'ac', 'AC services under Appliances', NULL, 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (2, 1, 'Refrigerator', 'refrigerator', 'Refrigerator services under Appliances', NULL, 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (3, 1, 'Washing Machine', 'washing-machine', 'Washing Machine services under Appliances', NULL, 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (4, 1, 'Microwave', 'microwave', 'Microwave services under Appliances', NULL, 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (5, 1, 'Deep Freezer', 'deep-freezer', 'Deep Freezer services under Appliances', NULL, 'active', '2026-09-24 14:02:30', '2026-09-24 14:02:30');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (6, 1, 'Water Dispenser', 'water-dispenser', 'Water Dispenser services under Appliances', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (7, 1, 'Geyser', 'geyser', 'Geyser services under Appliances', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (8, 2, 'Wiring & Installation', 'wiring-installation', 'Wiring & Installation services under Electrician', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (9, 2, 'Breaker & DB Box', 'breaker-db-box', 'Breaker & DB Box services under Electrician', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (10, 2, 'Ceiling & Exhaust Fans', 'ceiling-exhaust-fans', 'Ceiling & Exhaust Fans services under Electrician', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (11, 2, 'UPS & Generator Wiring', 'ups-generator-wiring', 'UPS & Generator Wiring services under Electrician', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (12, 2, 'Lighting Fixtures', 'lighting-fixtures', 'Lighting Fixtures services under Electrician', NULL, 'active', '2026-09-24 14:02:31', '2026-09-24 14:02:31');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (13, 3, 'Pipe Leakage', 'pipe-leakage', 'Pipe Leakage services under Plumbing', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (14, 3, 'Sanitary Fittings', 'sanitary-fittings', 'Sanitary Fittings services under Plumbing', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (15, 3, 'Water Motor & Pumps', 'water-motor-pumps', 'Water Motor & Pumps services under Plumbing', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (16, 3, 'Geyser Installation', 'geyser-installation', 'Geyser Installation services under Plumbing', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (17, 3, 'Drainage Solutions', 'drainage-solutions', 'Drainage Solutions services under Plumbing', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (18, 4, 'Full Home Deep Cleaning', 'full-home-deep-cleaning', 'Full Home Deep Cleaning services under Cleaning', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (19, 4, 'Sofa & Carpet Shampooing', 'sofa-carpet-shampooing', 'Sofa & Carpet Shampooing services under Cleaning', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (20, 4, 'Water Tank Cleaning', 'water-tank-cleaning', 'Water Tank Cleaning services under Cleaning', NULL, 'active', '2026-09-24 14:02:32', '2026-09-24 14:02:32');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (21, 4, 'Kitchen & Washroom Deep Clean', 'kitchen-washroom-deep-clean', 'Kitchen & Washroom Deep Clean services under Cleaning', NULL, 'active', '2026-09-24 14:02:33', '2026-09-24 14:02:33');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (22, 4, 'Fumigation & Pest Control', 'fumigation-pest-control', 'Fumigation & Pest Control services under Cleaning', NULL, 'active', '2026-09-24 14:02:33', '2026-09-24 14:02:33');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (23, 5, 'Doorstep Car Wash', 'doorstep-car-wash', 'Doorstep Car Wash services under Car Wash & Detailing', NULL, 'active', '2026-09-24 14:02:33', '2026-09-24 14:02:33');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (24, 5, 'Interior Deep Detailing', 'interior-deep-detailing', 'Interior Deep Detailing services under Car Wash & Detailing', NULL, 'active', '2026-09-24 14:02:33', '2026-09-24 14:02:33');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (25, 5, 'Ceramic Polish & Wax', 'ceramic-polish-wax', 'Ceramic Polish & Wax services under Car Wash & Detailing', NULL, 'active', '2026-09-24 14:02:34', '2026-09-24 14:02:34');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (26, 5, 'Engine Bay Cleaning', 'engine-bay-cleaning', 'Engine Bay Cleaning services under Car Wash & Detailing', NULL, 'active', '2026-09-24 14:02:34', '2026-09-24 14:02:34');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (27, 6, 'Solar Panel Cleaning', 'solar-panel-cleaning', 'Solar Panel Cleaning services under Solar', NULL, 'active', '2026-09-24 14:02:34', '2026-09-24 14:02:34');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (28, 6, 'Inverter Configuration', 'inverter-configuration', 'Inverter Configuration services under Solar', NULL, 'active', '2026-09-24 14:02:34', '2026-09-24 14:02:34');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (29, 6, 'Structure & Wiring Check', 'structure-wiring-check', 'Structure & Wiring Check services under Solar', NULL, 'active', '2026-09-24 14:02:34', '2026-09-24 14:02:34');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (30, 6, 'Complete Solar Installation', 'complete-solar-installation', 'Complete Solar Installation services under Solar', NULL, 'active', '2026-09-24 14:02:35', '2026-09-24 14:02:35');
INSERT INTO `sub_categories` (`id`, `category_id`, `title`, `slug`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES (31, 1, 'LED TV', 'led-tv', 'LED TV', NULL, 'active', '2026-09-24 15:37:15', '2026-09-24 15:37:15');

-- Dumping data for table `users` (5 rows)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`, `is_active`, `last_login_at`, `last_login_ip`, `deleted_at`, `brand_id`) VALUES (1, 'Super Admin', 'admin@serwizo.com', NULL, '$2y$12$3t1pgvjrPvBaf7zYZ6wHpO4uROS./d/C1Y9BU0I5J52ha.tAVxdTa', 'rGLQJxcAeJxVRpVYF6tL7OQlV3XvA0jwrV3I3xquqUEHZUx6PfEB3lF7nPhC', '2026-09-24 14:02:22', '2026-09-25 17:07:34', 'super_admin', '03001234567', 1, '2026-09-25 17:07:34', '192.168.100.100', NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`, `is_active`, `last_login_at`, `last_login_ip`, `deleted_at`, `brand_id`) VALUES (2, 'Operations Manager', 'operations@serwizo.com', NULL, '$2y$12$uqmPrhlihp71Qa4P1MZhNOuywdzgfYvl6FL0wbNHjmlAs7tTOXV56', 'VUIHm8PICSHZtIP0GJDGPsTRHKBpg3kkdFh9NVoe5fHRCuIA57IlZ57SpayQ', '2026-09-24 14:02:23', '2026-09-24 15:31:25', 'operations', '03007654321', 1, '2026-09-24 15:31:25', '127.0.0.1', NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`, `is_active`, `last_login_at`, `last_login_ip`, `deleted_at`, `brand_id`) VALUES (3, 'Accounts Officer', 'accounts@serwizo.com', NULL, '$2y$12$gicsXtHHlEVQHU9E.8Z4/uVZgy2ucMlNMvJLsKu0xHbSqR8YXXcUW', 'HrzqITAJfSZ4I6wi3jACA0OuQJa3DcuFNJnfA0ZteHDCzBh76cC4sOOJKnFG', '2026-09-24 14:02:24', '2026-09-24 15:33:03', 'accounts', '03119876543', 1, '2026-09-24 15:33:03', '127.0.0.1', NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`, `is_active`, `last_login_at`, `last_login_ip`, `deleted_at`, `brand_id`) VALUES (4, 'Hisense Service Portal', 'brand@hisense.com', NULL, '$2y$12$3ku23z7.K7DvHAC1eqB46uUAK2mQfgYfnFxRQo7b.b84X2VxPgqjm', NULL, '2026-09-24 14:02:47', '2026-09-24 14:02:47', 'brand', '03008881122', 1, NULL, NULL, NULL, 1);
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`, `is_active`, `last_login_at`, `last_login_ip`, `deleted_at`, `brand_id`) VALUES (5, 'Gree Warranty Portal', 'brand@gree.com', NULL, '$2y$12$YonRy2MUmgUcioPhJu/Rhua8lZQeKM0TwayLGf.diN/5c97vMdbuO', NULL, '2026-09-24 14:02:48', '2026-09-24 14:02:48', 'brand', '03009993344', 1, NULL, NULL, NULL, 2);

-- Dumping data for table `worker_assignments` (11 rows)
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (1, 1, 1, 2, 'completed', NULL, '2026-09-23 09:00:00', '2026-09-23 09:15:00', '2026-09-24 14:02:45', '2026-09-24 14:02:45');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (2, 2, 2, 2, 'completed', NULL, '2026-09-24 10:00:00', '2026-09-24 10:05:00', '2026-09-24 14:02:46', '2026-09-24 20:04:52');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (3, 4, 6, 1, 'completed', NULL, '2026-09-24 15:43:48', NULL, '2026-09-24 15:43:48', '2026-09-24 19:03:25');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (4, 5, 6, NULL, 'completed', NULL, '2026-09-24 19:04:51', '2026-09-24 19:04:51', '2026-09-24 19:04:52', '2026-09-24 19:10:52');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (5, 6, 5, NULL, 'completed', NULL, '2026-09-24 19:13:34', '2026-09-24 19:13:34', '2026-09-24 19:13:34', '2026-09-24 19:24:42');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (6, 7, 5, NULL, 'completed', NULL, '2026-09-24 19:21:04', '2026-09-24 19:21:04', '2026-09-24 19:21:04', '2026-09-24 19:23:58');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (7, 8, 5, NULL, 'cancelled', NULL, '2026-09-24 19:21:10', '2026-09-24 19:21:10', '2026-09-24 19:21:10', '2026-09-24 19:59:04');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (8, 9, 5, NULL, 'cancelled', NULL, '2026-09-24 19:21:17', '2026-09-24 19:21:17', '2026-09-24 19:21:18', '2026-09-24 19:59:15');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (9, 10, 3, NULL, 'completed', NULL, '2026-09-24 19:34:28', '2026-09-24 19:34:28', '2026-09-24 19:34:28', '2026-09-24 19:36:43');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (10, 11, 3, NULL, 'completed', NULL, '2026-09-24 19:41:59', '2026-09-24 19:41:59', '2026-09-24 19:41:59', '2026-09-24 20:01:59');
INSERT INTO `worker_assignments` (`id`, `service_request_id`, `worker_id`, `assigned_by_user_id`, `status`, `rejection_reason`, `assigned_at`, `responded_at`, `created_at`, `updated_at`) VALUES (11, 14, 7, 1, 'completed', NULL, '2026-09-25 17:18:55', NULL, '2026-09-25 17:18:55', '2026-09-25 17:20:57');

-- Dumping data for table `worker_bank_details` (6 rows)
INSERT INTO `worker_bank_details` (`id`, `worker_id`, `bank_name`, `account_title`, `account_number`, `iban`, `verification_status`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (1, 1, 'Meezan Bank Ltd', 'Muhammad Rashid', '01020304050607', 'PK64MEZN0001020304050607', 'verified', 1, '2026-09-24 14:02:38', '2026-09-24 14:02:38', '2026-09-24 14:02:38');
INSERT INTO `worker_bank_details` (`id`, `worker_id`, `bank_name`, `account_title`, `account_number`, `iban`, `verification_status`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (2, 2, 'Habib Bank Limited', 'Tariq Mehmood', '12345678901234', 'PK36HABB0012345678901234', 'verified', 1, '2026-09-24 14:02:40', '2026-09-24 14:02:40', '2026-09-24 14:02:40');
INSERT INTO `worker_bank_details` (`id`, `worker_id`, `bank_name`, `account_title`, `account_number`, `iban`, `verification_status`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (3, 3, 'Bank Alfalah', 'Khurram Shehzad', '554433221100', 'PK12ALFH0000554433221100', 'verified', 1, '2026-09-24 14:02:41', '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_bank_details` (`id`, `worker_id`, `bank_name`, `account_title`, `account_number`, `iban`, `verification_status`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (4, 4, 'MCB Bank', 'Farhan Qureshi', '098765432112', 'PK88MUCB0000098765432112', 'not_verified', NULL, NULL, '2026-09-24 14:02:42', '2026-09-24 14:02:42');
INSERT INTO `worker_bank_details` (`id`, `worker_id`, `bank_name`, `account_title`, `account_number`, `iban`, `verification_status`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (5, 5, 'Allied Bank Ltd', 'Bilal Ahmed', '887766554433', 'PK44ABPA0088776655443322', 'verified', 1, '2026-09-24 14:02:43', '2026-09-24 14:02:43', '2026-09-24 14:02:43');
INSERT INTO `worker_bank_details` (`id`, `worker_id`, `bank_name`, `account_title`, `account_number`, `iban`, `verification_status`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (6, 6, 'ABL', 'Iabql Qasim', '5465465465465', NULL, 'not_verified', NULL, NULL, '2026-09-24 14:19:03', '2026-09-24 14:19:03');

-- Dumping data for table `worker_categories` (7 rows)
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (1, 1, 1, 1, 1, '2026-09-24 14:02:37', '2026-09-24 14:02:37');
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (2, 2, 2, 8, 1, '2026-09-24 14:02:39', '2026-09-24 14:02:39');
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (3, 3, 3, 13, 1, '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (4, 4, 1, 1, 1, '2026-09-24 14:02:42', '2026-09-24 14:02:42');
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (5, 5, 6, 27, 1, '2026-09-24 14:02:43', '2026-09-24 14:02:43');
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (6, 6, 1, NULL, 1, '2026-09-24 14:19:03', '2026-09-24 14:19:03');
INSERT INTO `worker_categories` (`id`, `worker_id`, `category_id`, `sub_category_id`, `is_primary`, `created_at`, `updated_at`) VALUES (7, 7, 2, NULL, 1, '2026-09-25 17:16:23', '2026-09-25 17:16:23');

-- Dumping data for table `worker_documents` (10 rows)
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (1, 1, 'cnic_front', 'uploads/workers/cnic/sample_cnic_front.jpg', 'cnic_front.jpg', 'image/jpeg', 245000, 1, 1, '2026-09-24 14:02:38', '2026-09-24 14:02:38', '2026-09-24 14:02:38');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (2, 1, 'cnic_back', 'uploads/workers/cnic/sample_cnic_back.jpg', 'cnic_back.jpg', 'image/jpeg', 230000, 1, 1, '2026-09-24 14:02:38', '2026-09-24 14:02:38', '2026-09-24 14:02:38');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (3, 2, 'cnic_front', 'uploads/workers/cnic/sample_cnic_front.jpg', 'cnic_front.jpg', 'image/jpeg', 245000, 1, 1, '2026-09-24 14:02:40', '2026-09-24 14:02:40', '2026-09-24 14:02:40');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (4, 2, 'cnic_back', 'uploads/workers/cnic/sample_cnic_back.jpg', 'cnic_back.jpg', 'image/jpeg', 230000, 1, 1, '2026-09-24 14:02:40', '2026-09-24 14:02:40', '2026-09-24 14:02:40');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (5, 3, 'cnic_front', 'uploads/workers/cnic/sample_cnic_front.jpg', 'cnic_front.jpg', 'image/jpeg', 245000, 1, 1, '2026-09-24 14:02:41', '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (6, 3, 'cnic_back', 'uploads/workers/cnic/sample_cnic_back.jpg', 'cnic_back.jpg', 'image/jpeg', 230000, 1, 1, '2026-09-24 14:02:41', '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (7, 4, 'cnic_front', 'uploads/workers/cnic/sample_cnic_front.jpg', 'cnic_front.jpg', 'image/jpeg', 245000, 1, 1, '2026-09-24 14:02:42', '2026-09-24 14:02:42', '2026-09-24 14:02:42');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (8, 4, 'cnic_back', 'uploads/workers/cnic/sample_cnic_back.jpg', 'cnic_back.jpg', 'image/jpeg', 230000, 1, 1, '2026-09-24 14:02:42', '2026-09-24 14:02:42', '2026-09-24 14:02:42');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (9, 5, 'cnic_front', 'uploads/workers/cnic/sample_cnic_front.jpg', 'cnic_front.jpg', 'image/jpeg', 245000, 1, 1, '2026-09-24 14:02:44', '2026-09-24 14:02:44', '2026-09-24 14:02:44');
INSERT INTO `worker_documents` (`id`, `worker_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size_bytes`, `is_verified`, `verified_by_user_id`, `verified_at`, `created_at`, `updated_at`) VALUES (10, 5, 'cnic_back', 'uploads/workers/cnic/sample_cnic_back.jpg', 'cnic_back.jpg', 'image/jpeg', 230000, 1, 1, '2026-09-24 14:02:44', '2026-09-24 14:02:44', '2026-09-24 14:02:44');

-- Dumping data for table `worker_references` (6 rows)
INSERT INTO `worker_references` (`id`, `worker_id`, `name`, `phone`, `relationship`, `notes`, `created_at`, `updated_at`) VALUES (1, 1, 'Haji Tariq', '03001112233', 'Previous Employer - Tariq Electronics', NULL, '2026-09-24 14:02:38', '2026-09-24 14:02:38');
INSERT INTO `worker_references` (`id`, `worker_id`, `name`, `phone`, `relationship`, `notes`, `created_at`, `updated_at`) VALUES (2, 1, 'Usman Ali', '03214445566', 'Colleague', NULL, '2026-09-24 14:02:38', '2026-09-24 14:02:38');
INSERT INTO `worker_references` (`id`, `worker_id`, `name`, `phone`, `relationship`, `notes`, `created_at`, `updated_at`) VALUES (3, 2, 'Engr. Salman', '03335556677', 'Site Supervisor - Al-Raziq Builders', NULL, '2026-09-24 14:02:40', '2026-09-24 14:02:40');
INSERT INTO `worker_references` (`id`, `worker_id`, `name`, `phone`, `relationship`, `notes`, `created_at`, `updated_at`) VALUES (4, 3, 'Master Aslam', '03009988776', 'Senior Plumber', NULL, '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_references` (`id`, `worker_id`, `name`, `phone`, `relationship`, `notes`, `created_at`, `updated_at`) VALUES (5, 4, 'Sohail Khan', '03212233445', 'Uncle', NULL, '2026-09-24 14:02:42', '2026-09-24 14:02:42');
INSERT INTO `worker_references` (`id`, `worker_id`, `name`, `phone`, `relationship`, `notes`, `created_at`, `updated_at`) VALUES (6, 5, 'Imran Shah', '03004455667', 'Shop Owner', NULL, '2026-09-24 14:02:43', '2026-09-24 14:02:43');

-- Dumping data for table `worker_service_areas` (12 rows)
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (1, 1, 1, NULL, 1, '2026-09-24 14:02:37', '2026-09-24 14:02:37');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (2, 1, 7, NULL, 0, '2026-09-24 14:02:38', '2026-09-24 14:02:38');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (3, 2, 1, NULL, 1, '2026-09-24 14:02:39', '2026-09-24 14:02:39');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (4, 2, 7, NULL, 0, '2026-09-24 14:02:39', '2026-09-24 14:02:39');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (5, 3, 1, NULL, 1, '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (6, 3, 7, NULL, 0, '2026-09-24 14:02:41', '2026-09-24 14:02:41');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (7, 4, 2, NULL, 1, '2026-09-24 14:02:42', '2026-09-24 14:02:42');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (8, 5, 3, NULL, 1, '2026-09-24 14:02:43', '2026-09-24 14:02:43');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (9, 6, 1, NULL, 1, '2026-09-24 14:19:03', '2026-09-24 14:19:03');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (10, 7, 1, NULL, 1, '2026-09-25 17:16:23', '2026-09-25 17:16:23');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (11, 7, 5, NULL, 0, '2026-09-25 17:16:23', '2026-09-25 17:16:23');
INSERT INTO `worker_service_areas` (`id`, `worker_id`, `city_id`, `area_id`, `is_primary`, `created_at`, `updated_at`) VALUES (12, 7, 7, NULL, 0, '2026-09-25 17:16:23', '2026-09-25 17:16:23');

-- Dumping data for table `workers` (7 rows)
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 'SW-W-1001', 'Muhammad Rashid', '35201-1234567-1', '03011234567', 'rashid.worker@serwizo.com', '1990-05-15 00:00:00', 'House 14, St 3, Model Town, Lahore', 1, NULL, NULL, 8, 'expert', 'active', 'available', '$2y$12$89SXZ2tzmQmEH.S.y0pCze2e6/FLQag5DO5OtETYdo4UFibqGi7QC', NULL, NULL, '2026-09-24 14:02:37', '2026-09-24 14:02:37', '2026-09-24 16:20:33', NULL);
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 'SW-W-1002', 'Tariq Mehmood', '35202-7654321-3', '03029876543', 'tariq.electrician@serwizo.com', '1988-10-20 00:00:00', 'Plot 45, Sector B, Bahria Town, Lahore', 1, NULL, NULL, 10, 'expert', 'active', 'available', '$2y$12$cevy11lhlwGp2anEImc1Ges1DOpdyTdZ.bEMwZKsxApQVGAkq.bhm', NULL, NULL, '2026-09-24 20:03:45', '2026-09-24 14:02:39', '2026-09-24 20:03:45', NULL);
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 'SW-W-1003', 'Khurram Shehzad', '35201-9988776-5', '03035551234', 'khurram.plumber@serwizo.com', '1995-03-12 00:00:00', 'St 8, Main Bazar, Ichhra, Lahore', 1, NULL, NULL, 5, 'experienced', 'active', 'available', '$2y$12$BT0shp6SBm3zIerC9v.hWe.M.2IP78DTceZJPUuEWZ3W5UlPBtNYa', NULL, NULL, '2026-09-24 20:00:47', '2026-09-24 14:02:41', '2026-09-24 20:00:47', NULL);
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 'SW-W-1004', 'Farhan Qureshi', '42101-5544332-1', '03123344556', 'farhan.karachi@serwizo.com', '1992-07-25 00:00:00', 'Flat 302, Gulshan Block 4, Karachi', 2, NULL, NULL, 6, 'experienced', 'active', 'available', '$2y$12$JmabioOhuKuWkOupvsoUluXb91K4hZWU7OnH.VGHsH6L.1j5BVJc.', NULL, NULL, '2026-09-24 18:36:37', '2026-09-24 14:02:42', '2026-09-24 18:36:37', NULL);
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 'SW-W-1005', 'Bilal Ahmed', '61101-1122334-9', '03345566778', 'bilal.isb@serwizo.com', '1996-11-05 00:00:00', 'House 12, G-11/3, Islamabad', 3, NULL, NULL, 4, 'intermediate', 'active', 'available', '$2y$12$21ToK0yFllZhXasOIEGqF.fixi6EIx2qdoD7KuKDPGRtKSz1Pc21q', NULL, NULL, '2026-09-24 19:12:43', '2026-09-24 14:02:43', '2026-09-24 19:12:43', NULL);
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 'SW-W-1006', 'Iqbal Qasim', '35202-45654654-7', '03204456655', 'worker@worker.com', '2007-02-01 00:00:00', '144-Pak Park, Near C Block Marghazar Colony, Multan Road', 1, NULL, NULL, 3, 'intermediate', 'active', 'available', '$2y$12$e7kmxFAg31MWtWUv9qvKUueBl9bTX4Ef7NYTg0gXAAOjPNhNVw7iu', NULL, 'uploads/workers/photos/y2g0wyppOQIgDjVAaagK2xpMGxQ83UblunTnnX7K.png', '2026-09-24 19:02:30', '2026-09-24 14:19:03', '2026-09-24 19:02:30', NULL);
INSERT INTO `workers` (`id`, `worker_code`, `name`, `cnic_number`, `phone`, `email`, `date_of_birth`, `address`, `primary_city_id`, `primary_category_id`, `primary_sub_category_id`, `experience_years`, `skill_level`, `status`, `availability`, `password`, `device_token`, `photo_path`, `last_active_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 'SW-W-1007', 'Aftab Iqbal', '35201-1234557-1', '03345566554', 'worker@worker.com', '2005-02-01 00:00:00', '144-Pak Park, Near C Block Marghazar Colony, Multan Road', 1, NULL, NULL, 5, 'experienced', 'active', 'available', '$2y$12$ziVKCtiYU7p2OLzB/Ff.XehzDui6S4n/Ci4mxhEuKMuuO9vqjmEEy', NULL, NULL, '2026-09-25 17:18:05', '2026-09-25 17:16:22', '2026-09-25 17:18:05', NULL);

SET FOREIGN_KEY_CHECKS = 1;
