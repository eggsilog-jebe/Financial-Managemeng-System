-- ========================================================
-- Hospital Financial Management System (FMS)
-- Clean Table Schemas (DDL Only - No Data)
-- Generated: 2026-08-25 06:54:17
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for table `accounts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `normal_balance` enum('DEBIT','CREDIT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_code_unique` (`code`),
  KEY `accounts_category_index` (`category`),
  KEY `accounts_department_index` (`department`),
  KEY `accounts_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bank_accounts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bank_accounts`;
CREATE TABLE `bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gl_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PHP',
  `balance` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` enum('Active','Inactive','Frozen') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_accounts_account_number_unique` (`account_number`),
  KEY `bank_accounts_gl_code_index` (`gl_code`),
  KEY `bank_accounts_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bank_deposits`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bank_deposits`;
CREATE TABLE `bank_deposits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `deposit_reference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_account_id` bigint unsigned NOT NULL,
  `cashier_shift_id` bigint unsigned DEFAULT NULL,
  `deposit_date` date NOT NULL,
  `cash_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `check_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_deposited` decimal(15,4) NOT NULL,
  `bank_reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validated_by_teller` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('PREPARED','IN_TRANSIT','DEPOSITED','RECONCILED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PREPARED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_deposits_deposit_reference_unique` (`deposit_reference`),
  KEY `bank_deposits_bank_account_id_foreign` (`bank_account_id`),
  KEY `bank_deposits_cashier_shift_id_foreign` (`cashier_shift_id`),
  KEY `bank_deposits_deposit_date_index` (`deposit_date`),
  KEY `bank_deposits_status_index` (`status`),
  CONSTRAINT `bank_deposits_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bank_deposits_cashier_shift_id_foreign` FOREIGN KEY (`cashier_shift_id`) REFERENCES `cashier_shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bank_reconciliations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bank_reconciliations`;
CREATE TABLE `bank_reconciliations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bank_account_id` bigint unsigned NOT NULL,
  `statement_date` date NOT NULL,
  `statement_balance` decimal(15,4) NOT NULL,
  `book_balance` decimal(15,4) NOT NULL,
  `variance` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Reconciled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_reconciliations_bank_account_id_foreign` (`bank_account_id`),
  KEY `bank_reconciliations_statement_date_index` (`statement_date`),
  KEY `bank_reconciliations_status_index` (`status`),
  CONSTRAINT `bank_reconciliations_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bank_statement_lines`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bank_statement_lines`;
CREATE TABLE `bank_statement_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bank_reconciliation_id` bigint unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `match_status` enum('MATCHED','UNMATCHED','OUTSTANDING_CHECK','DEPOSIT_IN_TRANSIT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNMATCHED',
  `matched_journal_line_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_statement_lines_bank_reconciliation_id_foreign` (`bank_reconciliation_id`),
  KEY `bank_statement_lines_matched_journal_line_id_foreign` (`matched_journal_line_id`),
  KEY `bank_statement_lines_transaction_date_index` (`transaction_date`),
  KEY `bank_statement_lines_match_status_index` (`match_status`),
  CONSTRAINT `bank_statement_lines_bank_reconciliation_id_foreign` FOREIGN KEY (`bank_reconciliation_id`) REFERENCES `bank_reconciliations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bank_statement_lines_matched_journal_line_id_foreign` FOREIGN KEY (`matched_journal_line_id`) REFERENCES `journal_entry_lines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bill_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bill_items`;
CREATE TABLE `bill_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_bill_id` bigint unsigned NOT NULL,
  `item_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_type` enum('GOODS_INVENTORY','SERVICES_MAINTENANCE','DOCTOR_PROFESSIONAL_FEE','CAPEX_EQUIPMENT','UTILITIES') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GOODS_INVENTORY',
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(15,4) NOT NULL,
  `gross_amount` decimal(15,4) NOT NULL,
  `atc_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WI158',
  `ewt_rate` decimal(5,4) NOT NULL DEFAULT '0.0100',
  `ewt_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `net_payable` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_items_purchase_bill_id_foreign` (`purchase_bill_id`),
  KEY `bill_items_item_code_index` (`item_code`),
  KEY `bill_items_expense_type_index` (`expense_type`),
  CONSTRAINT `bill_items_purchase_bill_id_foreign` FOREIGN KEY (`purchase_bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bir_2307_certificates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bir_2307_certificates`;
CREATE TABLE `bir_2307_certificates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certificate_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_bill_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `doctor_id` bigint unsigned DEFAULT NULL,
  `period_from` date NOT NULL,
  `period_to` date NOT NULL,
  `payee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payee_tin` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `atc_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_base_amount` decimal(15,4) NOT NULL,
  `tax_rate` decimal(5,4) NOT NULL,
  `tax_withheld` decimal(15,4) NOT NULL,
  `form_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GENERATED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bir_2307_certificates_certificate_number_unique` (`certificate_number`),
  KEY `bir_2307_certificates_purchase_bill_id_foreign` (`purchase_bill_id`),
  KEY `bir_2307_certificates_vendor_id_foreign` (`vendor_id`),
  KEY `bir_2307_certificates_doctor_id_foreign` (`doctor_id`),
  KEY `bir_2307_certificates_period_from_index` (`period_from`),
  KEY `bir_2307_certificates_period_to_index` (`period_to`),
  KEY `bir_2307_certificates_atc_code_index` (`atc_code`),
  KEY `bir_2307_certificates_form_status_index` (`form_status`),
  CONSTRAINT `bir_2307_certificates_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctor_profiles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bir_2307_certificates_purchase_bill_id_foreign` FOREIGN KEY (`purchase_bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bir_2307_certificates_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `budget_allocations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `budget_allocations`;
CREATE TABLE `budget_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fiscal_year` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allocated_amount` decimal(15,4) NOT NULL,
  `spent_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `remaining_balance` decimal(15,4) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Approved',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_allocations_department_index` (`department`),
  KEY `budget_allocations_fiscal_year_index` (`fiscal_year`),
  KEY `budget_allocations_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `budget_encumbrances`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `budget_encumbrances`;
CREATE TABLE `budget_encumbrances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `budget_allocation_id` bigint unsigned NOT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `encumbered_amount` decimal(15,4) NOT NULL,
  `liquidated_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` enum('COMMITTED','LIQUIDATED','RELEASED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMMITTED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_encumbrances_budget_allocation_id_foreign` (`budget_allocation_id`),
  KEY `budget_encumbrances_reference_type_index` (`reference_type`),
  KEY `budget_encumbrances_reference_number_index` (`reference_number`),
  KEY `budget_encumbrances_status_index` (`status`),
  CONSTRAINT `budget_encumbrances_budget_allocation_id_foreign` FOREIGN KEY (`budget_allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cas_audit_trails`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cas_audit_trails`;
CREATE TABLE `cas_audit_trails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_uuid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auditable_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE','POST','REVERSE','LOCK') COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `record_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `previous_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cas_audit_trails_event_uuid_unique` (`event_uuid`),
  KEY `cas_audit_trails_user_id_foreign` (`user_id`),
  KEY `cas_audit_trails_auditable_type_index` (`auditable_type`),
  KEY `cas_audit_trails_auditable_id_index` (`auditable_id`),
  KEY `cas_audit_trails_action_index` (`action`),
  KEY `cas_audit_trails_record_hash_index` (`record_hash`),
  KEY `cas_audit_trails_previous_hash_index` (`previous_hash`),
  KEY `cas_audit_trails_created_at_index` (`created_at`),
  CONSTRAINT `cas_audit_trails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cashier_shifts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cashier_shifts`;
CREATE TABLE `cashier_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `terminal_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POS-MAIN-01',
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `opening_cash_float` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `expected_cash` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `actual_cash_counted` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `cash_variance` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_digital_collections` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_collections` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` enum('OPEN','BALANCING','CLOSED','RECONCILED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cashier_shifts_shift_code_unique` (`shift_code`),
  KEY `cashier_shifts_cashier_id_foreign` (`cashier_id`),
  KEY `cashier_shifts_opened_at_index` (`opened_at`),
  KEY `cashier_shifts_closed_at_index` (`closed_at`),
  KEY `cashier_shifts_status_index` (`status`),
  CONSTRAINT `cashier_shifts_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `check_registers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `check_registers`;
CREATE TABLE `check_registers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `disbursement_voucher_id` bigint unsigned NOT NULL,
  `bank_account_id` bigint unsigned NOT NULL,
  `check_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_date` date NOT NULL,
  `payee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `status` enum('ISSUED','PRINTED','RELEASED','CLEARED','STALE','VOID') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ISSUED',
  `cleared_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `check_registers_check_number_unique` (`check_number`),
  KEY `check_registers_disbursement_voucher_id_foreign` (`disbursement_voucher_id`),
  KEY `check_registers_bank_account_id_foreign` (`bank_account_id`),
  KEY `check_registers_check_date_index` (`check_date`),
  KEY `check_registers_status_index` (`status`),
  CONSTRAINT `check_registers_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `check_registers_disbursement_voucher_id_foreign` FOREIGN KEY (`disbursement_voucher_id`) REFERENCES `disbursement_vouchers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `credit_notes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `credit_notes`;
CREATE TABLE `credit_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `credit_note_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `patient_account_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('DRAFT','APPROVED','APPLIED','VOID') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'APPROVED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credit_notes_credit_note_number_unique` (`credit_note_number`),
  KEY `credit_notes_invoice_id_foreign` (`invoice_id`),
  KEY `credit_notes_patient_account_id_foreign` (`patient_account_id`),
  KEY `credit_notes_issue_date_index` (`issue_date`),
  KEY `credit_notes_status_index` (`status`),
  CONSTRAINT `credit_notes_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `credit_notes_patient_account_id_foreign` FOREIGN KEY (`patient_account_id`) REFERENCES `patient_accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `disbursement_vouchers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `disbursement_vouchers`;
CREATE TABLE `disbursement_vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voucher_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_bill_id` bigint unsigned DEFAULT NULL,
  `payroll_run_id` bigint unsigned DEFAULT NULL,
  `bank_account_id` bigint unsigned NOT NULL,
  `voucher_date` date NOT NULL,
  `payee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_amount` decimal(15,4) NOT NULL,
  `withheld_tax_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `net_disbursed_amount` decimal(15,4) NOT NULL,
  `payment_method` enum('CHECK','PESONET_EFT','INSTAPAY','PETTY_CASH','TELEGRAPHIC_TRANSFER') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PESONET_EFT',
  `check_or_eft_ref` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('DRAFT','AUDITED','APPROVED','RELEASED','CLEARED','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'APPROVED',
  `approved_by` bigint unsigned DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `disbursement_vouchers_voucher_number_unique` (`voucher_number`),
  KEY `disbursement_vouchers_purchase_bill_id_foreign` (`purchase_bill_id`),
  KEY `disbursement_vouchers_payroll_run_id_foreign` (`payroll_run_id`),
  KEY `disbursement_vouchers_bank_account_id_foreign` (`bank_account_id`),
  KEY `disbursement_vouchers_approved_by_foreign` (`approved_by`),
  KEY `disbursement_vouchers_voucher_date_index` (`voucher_date`),
  KEY `disbursement_vouchers_payment_method_index` (`payment_method`),
  KEY `disbursement_vouchers_status_index` (`status`),
  CONSTRAINT `disbursement_vouchers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `disbursement_vouchers_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `disbursement_vouchers_payroll_run_id_foreign` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `disbursement_vouchers_purchase_bill_id_foreign` FOREIGN KEY (`purchase_bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `doctor_profiles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `doctor_profiles`;
CREATE TABLE `doctor_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doctor_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tin` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ewt_rate_type` enum('INDIVIDUAL_BELOW_3M','INDIVIDUAL_ABOVE_3M','CORPORATE') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INDIVIDUAL_BELOW_3M',
  `default_ewt_rate` decimal(5,4) NOT NULL DEFAULT '0.1000',
  `has_sworn_declaration` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctor_profiles_doctor_code_unique` (`doctor_code`),
  KEY `doctor_profiles_tin_index` (`tin`),
  KEY `doctor_profiles_ewt_rate_type_index` (`ewt_rate_type`),
  KEY `doctor_profiles_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
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

-- --------------------------------------------------------
-- Table structure for table `fiscal_periods`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `fiscal_periods`;
CREATE TABLE `fiscal_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fiscal_year` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_number` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('OPEN','CLOSING_IN_PROGRESS','LOCKED','AUDITED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `closed_by` bigint unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_periods_period_code_unique` (`period_code`),
  KEY `fiscal_periods_closed_by_foreign` (`closed_by`),
  KEY `fiscal_periods_fiscal_year_index` (`fiscal_year`),
  KEY `fiscal_periods_start_date_index` (`start_date`),
  KEY `fiscal_periods_end_date_index` (`end_date`),
  KEY `fiscal_periods_status_index` (`status`),
  CONSTRAINT `fiscal_periods_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `fund_transfers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `fund_transfers`;
CREATE TABLE `fund_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_account` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_account` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `transfer_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfer_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Completed & Posted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fund_transfers_reference_number_unique` (`reference_number`),
  KEY `fund_transfers_transfer_date_index` (`transfer_date`),
  KEY `fund_transfers_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hmo_claims`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `hmo_claims`;
CREATE TABLE `hmo_claims` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `hmo_provider` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loa_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_limit` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `claimed_amount` decimal(15,4) NOT NULL,
  `settled_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` enum('PENDING_BILLING','SUBMITTED','APPROVED','SETTLED','DISPUTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING_BILLING',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hmo_claims_invoice_id_foreign` (`invoice_id`),
  KEY `hmo_claims_hmo_provider_index` (`hmo_provider`),
  KEY `hmo_claims_status_index` (`status`),
  CONSTRAINT `hmo_claims_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `invoice_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `item_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revenue_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CLINICAL',
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(15,4) NOT NULL,
  `gross_amount` decimal(15,4) NOT NULL,
  `is_vatable` tinyint(1) NOT NULL DEFAULT '1',
  `is_senior_pwd_eligible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_item_code_index` (`item_code`),
  KEY `invoice_items_department_index` (`department`),
  KEY `invoice_items_revenue_category_index` (`revenue_category`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `invoices`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_account_id` bigint unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount` decimal(15,4) NOT NULL,
  `insurance_covered` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `patient_payable` decimal(15,4) NOT NULL,
  `status` enum('UNPAID','PARTIAL','SETTLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNPAID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_patient_account_id_foreign` (`patient_account_id`),
  KEY `invoices_invoice_date_index` (`invoice_date`),
  KEY `invoices_status_index` (`status`),
  CONSTRAINT `invoices_patient_account_id_foreign` FOREIGN KEY (`patient_account_id`) REFERENCES `patient_accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
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

-- --------------------------------------------------------
-- Table structure for table `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
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

-- --------------------------------------------------------
-- Table structure for table `journal_entries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `journal_entries`;
CREATE TABLE `journal_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('GENERAL','ADJUSTING','CLOSING') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GENERAL',
  `status` enum('DRAFT','POSTED','REVERSED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `reversed_by_entry_id` bigint unsigned DEFAULT NULL,
  `posted_by` bigint unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_entries_reference_number_unique` (`reference_number`),
  KEY `journal_entries_reversed_by_entry_id_foreign` (`reversed_by_entry_id`),
  KEY `journal_entries_posted_by_foreign` (`posted_by`),
  KEY `journal_entries_entry_date_index` (`entry_date`),
  KEY `journal_entries_type_index` (`type`),
  KEY `journal_entries_status_index` (`status`),
  CONSTRAINT `journal_entries_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entries_reversed_by_entry_id_foreign` FOREIGN KEY (`reversed_by_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `journal_entry_lines`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `journal_entry_lines`;
CREATE TABLE `journal_entry_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `debit` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `credit` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_lines_account_id_foreign` (`account_id`),
  KEY `journal_entry_lines_journal_entry_id_account_id_index` (`journal_entry_id`,`account_id`),
  CONSTRAINT `journal_entry_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `journal_entry_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `official_receipts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `official_receipts`;
CREATE TABLE `official_receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `or_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned DEFAULT NULL,
  `patient_account_id` bigint unsigned NOT NULL,
  `or_date` date NOT NULL,
  `payor_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payor_tin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vatable_sales` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `vat_exempt_sales` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `zero_rated_sales` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `vat_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_amount_collected` decimal(15,4) NOT NULL,
  `status` enum('VALID','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VALID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `official_receipts_or_number_unique` (`or_number`),
  KEY `official_receipts_payment_id_foreign` (`payment_id`),
  KEY `official_receipts_invoice_id_foreign` (`invoice_id`),
  KEY `official_receipts_patient_account_id_foreign` (`patient_account_id`),
  KEY `official_receipts_or_date_index` (`or_date`),
  KEY `official_receipts_status_index` (`status`),
  CONSTRAINT `official_receipts_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `official_receipts_patient_account_id_foreign` FOREIGN KEY (`patient_account_id`) REFERENCES `patient_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `official_receipts_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `patient_accounts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `patient_accounts`;
CREATE TABLE `patient_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admission_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Inpatient',
  `hmo_provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_billed` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `current_balance` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_accounts_patient_id_number_unique` (`patient_id_number`),
  KEY `patient_accounts_admission_type_index` (`admission_type`),
  KEY `patient_accounts_hmo_provider_index` (`hmo_provider`),
  KEY `patient_accounts_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payment_receipts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payment_receipts`;
CREATE TABLE `payment_receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_paid` decimal(15,4) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_date` date NOT NULL,
  `cashier_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_receipts_receipt_number_unique` (`receipt_number`),
  KEY `payment_receipts_payment_method_index` (`payment_method`),
  KEY `payment_receipts_receipt_date_index` (`receipt_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payment_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payment_requests`;
CREATE TABLE `payment_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('PENDING','APPROVED','DISBURSED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_requests_request_number_unique` (`request_number`),
  KEY `payment_requests_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_reference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_id` bigint unsigned DEFAULT NULL,
  `patient_account_id` bigint unsigned NOT NULL,
  `cashier_shift_id` bigint unsigned DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `payment_method` enum('CASH','CREDIT_CARD','DEBIT_CARD','QR_PH','GCASH','MAYA','CHECK','ONLINE_BANK') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CASH',
  `transaction_channel_ref` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` enum('PATIENT_COPAY','ADMISSION_DEPOSIT','HMO_SETTLEMENT','PHILHEALTH_SETTLEMENT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PATIENT_COPAY',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_reference_unique` (`payment_reference`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_patient_account_id_foreign` (`patient_account_id`),
  KEY `payments_cashier_shift_id_foreign` (`cashier_shift_id`),
  KEY `payments_payment_date_index` (`payment_date`),
  KEY `payments_payment_method_index` (`payment_method`),
  KEY `payments_payment_type_index` (`payment_type`),
  CONSTRAINT `payments_cashier_shift_id_foreign` FOREIGN KEY (`cashier_shift_id`) REFERENCES `cashier_shifts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_patient_account_id_foreign` FOREIGN KEY (`patient_account_id`) REFERENCES `patient_accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payroll_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payroll_items`;
CREATE TABLE `payroll_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_run_id` bigint unsigned NOT NULL,
  `employee_id_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sss_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `philhealth_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagibig_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `basic_salary` decimal(15,4) NOT NULL,
  `overtime_pay` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `allowances` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `gross_pay` decimal(15,4) NOT NULL,
  `sss_employee_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `sss_employer_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `philhealth_employee_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `philhealth_employer_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `pagibig_employee_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `pagibig_employer_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `withholding_tax` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `net_pay` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_items_payroll_run_id_foreign` (`payroll_run_id`),
  KEY `payroll_items_employee_id_number_index` (`employee_id_number`),
  KEY `payroll_items_department_index` (`department`),
  CONSTRAINT `payroll_items_payroll_run_id_foreign` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payroll_runs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payroll_runs`;
CREATE TABLE `payroll_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_run_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cutoff_start` date NOT NULL,
  `cutoff_end` date NOT NULL,
  `payout_date` date NOT NULL,
  `employee_count` int NOT NULL DEFAULT '0',
  `total_gross_pay` decimal(15,4) NOT NULL,
  `total_sss_employee` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_sss_employer` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_philhealth_employee` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_philhealth_employer` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_pagibig_employee` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_pagibig_employer` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_withholding_tax_1601c` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_statutory_deductions` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_net_pay` decimal(15,4) NOT NULL,
  `status` enum('DRAFT','AUDITED','APPROVED','DISBURSED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'APPROVED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_runs_payroll_run_number_unique` (`payroll_run_number`),
  KEY `payroll_runs_cutoff_start_index` (`cutoff_start`),
  KEY `payroll_runs_cutoff_end_index` (`cutoff_end`),
  KEY `payroll_runs_payout_date_index` (`payout_date`),
  KEY `payroll_runs_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `philhealth_claims`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `philhealth_claims`;
CREATE TABLE `philhealth_claims` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `claim_series_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_pin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patient_pin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EMPLOYED',
  `primary_icd_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_case_rate_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_case_rate_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `secondary_case_rate_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_case_rate_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_case_rate_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `hospital_fee_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `professional_fee_share` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `claim_status` enum('DRAFT','TRANSMITTED','IN_PROCESS','APPROVED','PAID','DENIED','RTH') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `transmitted_at` date DEFAULT NULL,
  `settled_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `philhealth_claims_claim_series_number_unique` (`claim_series_number`),
  KEY `philhealth_claims_invoice_id_foreign` (`invoice_id`),
  KEY `philhealth_claims_claim_status_index` (`claim_status`),
  CONSTRAINT `philhealth_claims_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `purchase_bills`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `purchase_bills`;
CREATE TABLE `purchase_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(15,4) NOT NULL,
  `paid_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` enum('UNPAID','PARTIAL','PAID','OVERDUE') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNPAID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_bills_bill_number_unique` (`bill_number`),
  KEY `purchase_bills_vendor_id_foreign` (`vendor_id`),
  KEY `purchase_bills_bill_date_index` (`bill_date`),
  KEY `purchase_bills_due_date_index` (`due_date`),
  KEY `purchase_bills_status_index` (`status`),
  CONSTRAINT `purchase_bills_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
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

-- --------------------------------------------------------
-- Table structure for table `statutory_discounts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `statutory_discounts`;
CREATE TABLE `statutory_discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `discount_type` enum('SENIOR_CITIZEN','PWD','EMPLOYEE','CHARITY') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SENIOR_CITIZEN',
  `id_card_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_exempt_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `discount_rate` decimal(5,4) NOT NULL DEFAULT '0.2000',
  `discount_amount` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `statutory_discounts_invoice_id_foreign` (`invoice_id`),
  KEY `statutory_discounts_discount_type_index` (`discount_type`),
  CONSTRAINT `statutory_discounts_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tax_certificates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tax_certificates`;
CREATE TABLE `tax_certificates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cert_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payee_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payee_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'doctor',
  `tin` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `atc_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_income` decimal(15,4) NOT NULL,
  `tax_withheld` decimal(15,4) NOT NULL,
  `form_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2307',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_certificates_cert_number_unique` (`cert_number`),
  KEY `tax_certificates_payee_type_index` (`payee_type`),
  KEY `tax_certificates_form_type_index` (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tax_returns`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tax_returns`;
CREATE TABLE `tax_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_covered` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_due` decimal(15,4) NOT NULL,
  `status` enum('DRAFT','FILED','PAID') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `filing_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_returns_return_number_unique` (`return_number`),
  KEY `tax_returns_form_type_index` (`form_type`),
  KEY `tax_returns_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tax_rules`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tax_rules`;
CREATE TABLE `tax_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tax_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `atc_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `scope` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_rules_tax_code_unique` (`tax_code`),
  KEY `tax_rules_atc_code_index` (`atc_code`),
  KEY `tax_rules_category_index` (`category`),
  KEY `tax_rules_cat_type_index` (`cat_type`),
  KEY `tax_rules_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `three_way_matches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `three_way_matches`;
CREATE TABLE `three_way_matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_bill_id` bigint unsigned NOT NULL,
  `po_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grn_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `po_amount` decimal(15,4) NOT NULL,
  `grn_amount` decimal(15,4) NOT NULL,
  `invoice_amount` decimal(15,4) NOT NULL,
  `price_variance` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `quantity_variance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `match_status` enum('MATCHED','PRICE_MISMATCH','QTY_MISMATCH','OVER_BILLED','PENDING') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MATCHED',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `three_way_matches_purchase_bill_id_foreign` (`purchase_bill_id`),
  KEY `three_way_matches_approved_by_foreign` (`approved_by`),
  KEY `three_way_matches_po_number_index` (`po_number`),
  KEY `three_way_matches_grn_number_index` (`grn_number`),
  KEY `three_way_matches_vendor_invoice_number_index` (`vendor_invoice_number`),
  KEY `three_way_matches_match_status_index` (`match_status`),
  CONSTRAINT `three_way_matches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `three_way_matches_purchase_bill_id_foreign` FOREIGN KEY (`purchase_bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'StaffAccountant',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `vendors`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_code_unique` (`code`),
  KEY `vendors_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
