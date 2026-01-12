-- CreateTable
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(191) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role_id` BIGINT UNSIGNED NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `users_username_key`(`username`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `borrowers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `password` VARCHAR(255) NULL,
    `phone` VARCHAR(32) NULL,
    `address` VARCHAR(255) NULL,
    `sex` VARCHAR(191) NULL,
    `occupation` VARCHAR(255) NULL,
    `birthday` DATE NULL,
    `monthly_income` DECIMAL(12, 2) NULL,
    `civil_status` VARCHAR(64) NULL,
    `reference_no` VARCHAR(128) NULL,
    `status` ENUM('active', 'inactive', 'delinquent', 'closed', 'blacklisted') NOT NULL DEFAULT 'active',
    `is_archived` BOOLEAN NOT NULL DEFAULT false,
    `archived_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,
    `deleted_at` DATETIME(3) NULL,

    INDEX `borrowers_phone_idx`(`phone`),
    INDEX `borrowers_reference_no_idx`(`reference_no`),
    INDEX `borrowers_status_idx`(`status`),
    INDEX `borrowers_is_archived_idx`(`is_archived`),
    INDEX `borrowers_full_name_email_idx`(`full_name`, `email`),
    UNIQUE INDEX `borrowers_email_key`(`email`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `loans` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference` VARCHAR(50) NOT NULL,
    `borrower_id` BIGINT UNSIGNED NULL,
    `disbursement_account_id` BIGINT UNSIGNED NULL,
    `borrower_name` VARCHAR(160) NOT NULL,
    `principal_amount` DECIMAL(14, 2) NOT NULL,
    `interest_rate` DECIMAL(7, 4) NOT NULL DEFAULT 0.0000,
    `application_date` DATE NOT NULL,
    `maturity_date` DATE NULL,
    `release_date` DATE NULL,
    `status` ENUM('new_application', 'under_review', 'approved', 'for_release', 'disbursed', 'closed', 'rejected', 'cancelled', 'restructured') NOT NULL DEFAULT 'new_application',
    `total_disbursed` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `total_paid` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `total_penalties` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `penalty_grace_days` INTEGER UNSIGNED NOT NULL DEFAULT 0,
    `penalty_daily_rate` DECIMAL(7, 6) NOT NULL DEFAULT 0.001000,
    `is_active` BOOLEAN NOT NULL DEFAULT true,
    `remarks` VARCHAR(255) NULL,
    `application_latitude` DECIMAL(10, 8) NULL,
    `application_longitude` DECIMAL(11, 8) NULL,
    `application_location_address` VARCHAR(255) NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `loans_reference_key`(`reference`),
    INDEX `loans_application_date_idx`(`application_date`),
    INDEX `loans_release_date_idx`(`release_date`),
    INDEX `loans_maturity_date_idx`(`maturity_date`),
    INDEX `loans_status_idx`(`status`),
    INDEX `loans_is_active_idx`(`is_active`),
    INDEX `loans_status_is_active_idx`(`status`, `is_active`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `guarantors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `loan_id` BIGINT UNSIGNED NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `civil_status` VARCHAR(64) NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `guarantors_loan_id_key`(`loan_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `repayments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `loan_id` BIGINT UNSIGNED NOT NULL,
    `due_date` DATE NOT NULL,
    `amount_due` DECIMAL(14, 2) NOT NULL,
    `amount_paid` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `paid_at` DATETIME NULL,
    `penalty_applied` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `note` VARCHAR(255) NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    INDEX `repayments_loan_id_due_date_idx`(`loan_id`, `due_date`),
    INDEX `repayments_due_date_idx`(`due_date`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `bank_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(32) NULL,
    `name` VARCHAR(128) NOT NULL,
    `timezone` VARCHAR(64) NOT NULL DEFAULT 'Asia/Manila',
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `bank_accounts_code_key`(`code`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `bank_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bank_account_id` BIGINT UNSIGNED NOT NULL,
    `account_id` BIGINT UNSIGNED NULL,
    `loan_id` BIGINT UNSIGNED NULL,
    `borrower_id` BIGINT UNSIGNED NULL,
    `ref_code` VARCHAR(32) NULL,
    `kind` ENUM('bank', 'journal') NOT NULL DEFAULT 'bank',
    `tx_date` DATE NOT NULL,
    `contact_display` VARCHAR(191) NULL,
    `description` VARCHAR(255) NULL,
    `spent` DECIMAL(14, 2) NULL,
    `received` DECIMAL(14, 2) NULL,
    `reconcile_status` ENUM('pending', 'ok', 'match') NULL DEFAULT 'pending',
    `ledger_contact` VARCHAR(191) NULL,
    `account_name` VARCHAR(191) NULL,
    `remarks` VARCHAR(255) NULL,
    `tx_class` VARCHAR(191) NULL,
    `source` VARCHAR(64) NULL,
    `status` ENUM('pending', 'posted', 'excluded') NOT NULL DEFAULT 'pending',
    `posted_at` DATETIME(3) NULL,
    `is_transfer` BOOLEAN NOT NULL DEFAULT false,
    `bank_text` VARCHAR(255) NULL,
    `match_id` BIGINT UNSIGNED NULL,
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    INDEX `bank_transactions_tx_date_idx`(`tx_date`),
    INDEX `bank_transactions_kind_idx`(`kind`),
    INDEX `bank_transactions_reconcile_status_idx`(`reconcile_status`),
    INDEX `bank_transactions_status_idx`(`status`),
    INDEX `bank_transactions_posted_at_idx`(`posted_at`),
    INDEX `bank_transactions_is_transfer_idx`(`is_transfer`),
    INDEX `bank_transactions_match_id_idx`(`match_id`),
    FULLTEXT INDEX `bank_transactions_contact_display_description_ledger_contact_idx`(`contact_display`, `description`, `ledger_contact`, `account_name`, `remarks`, `tx_class`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `bank_statements` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bank_account_id` BIGINT UNSIGNED NOT NULL,
    `statement_end_date` DATE NOT NULL,
    `ending_balance` DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
    `source_name` VARCHAR(255) NULL,
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    INDEX `bank_statements_bank_account_id_idx`(`bank_account_id`),
    INDEX `bank_statements_statement_end_date_idx`(`statement_end_date`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `slug` VARCHAR(64) NOT NULL,
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `roles_slug_key`(`slug`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128) NOT NULL,
    `key` VARCHAR(64) NOT NULL,
    `group` VARCHAR(64) NULL,
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `permissions_key_key`(`key`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `role_permission` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `allowed` BOOLEAN NOT NULL DEFAULT false,
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`role_id`, `permission_id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `chart_of_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(32) NOT NULL,
    `name` VARCHAR(128) NOT NULL,
    `description` VARCHAR(512) NULL,
    `report` VARCHAR(191) NOT NULL,
    `group_account` VARCHAR(191) NOT NULL,
    `normal_balance` ENUM('Debit', 'Credit') NULL,
    `debit_effect` ENUM('Increase', 'Decrease') NULL,
    `credit_effect` ENUM('Increase', 'Decrease') NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT true,
    `sort_order` INTEGER UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NULL,
    `updated_at` DATETIME(3) NULL,

    UNIQUE INDEX `chart_of_accounts_code_key`(`code`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `documents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `borrower_id` BIGINT UNSIGNED NULL,
    `loan_id` BIGINT UNSIGNED NULL,
    `document_type` ENUM('PRIMARY_ID', 'SECONDARY_ID', 'AGREEMENT', 'RECEIPT', 'SIGNATURE', 'PHOTO_2X2', 'OTHER') NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_url` VARCHAR(512) NOT NULL,
    `file_size` BIGINT UNSIGNED NULL,
    `mime_type` VARCHAR(100) NULL,
    `uploaded_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    INDEX `documents_borrower_id_idx`(`borrower_id`),
    INDEX `documents_loan_id_idx`(`loan_id`),
    INDEX `documents_document_type_idx`(`document_type`),
    INDEX `documents_uploaded_at_idx`(`uploaded_at`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `loan_id` BIGINT UNSIGNED NOT NULL,
    `borrower_id` BIGINT UNSIGNED NOT NULL,
    `repayment_id` BIGINT UNSIGNED NULL,
    `receipt_document_id` BIGINT UNSIGNED NULL,
    `amount` DECIMAL(14, 2) NOT NULL,
    `penalty_amount` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `remarks` TEXT NULL,
    `rejection_reason` TEXT NULL,
    `approved_by_user_id` BIGINT UNSIGNED NULL,
    `paid_at` DATETIME NULL,
    `approved_at` DATETIME NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    INDEX `payments_loan_id_status_idx`(`loan_id`, `status`),
    INDEX `payments_borrower_id_status_idx`(`borrower_id`, `status`),
    INDEX `payments_status_idx`(`status`),
    INDEX `payments_paid_at_idx`(`paid_at`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `borrower_id` BIGINT UNSIGNED NOT NULL,
    `loan_id` BIGINT UNSIGNED NULL,
    `type` ENUM('info', 'reminder', 'approval', 'payment_received', 'payment_due', 'loan_status_change') NOT NULL DEFAULT 'info',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN NOT NULL DEFAULT false,
    `read_at` DATETIME NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    INDEX `notifications_borrower_id_idx`(`borrower_id`),
    INDEX `notifications_loan_id_idx`(`loan_id`),
    INDEX `notifications_is_read_idx`(`is_read`),
    INDEX `notifications_created_at_idx`(`created_at`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `support_messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `borrower_id` BIGINT UNSIGNED NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('pending', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
    `admin_response` TEXT NULL,
    `responded_by_user_id` BIGINT UNSIGNED NULL,
    `responded_at` DATETIME NULL,
    `created_at` DATETIME(3) NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updated_at` DATETIME(3) NULL,

    INDEX `support_messages_borrower_id_idx`(`borrower_id`),
    INDEX `support_messages_status_idx`(`status`),
    INDEX `support_messages_created_at_idx`(`created_at`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- AddForeignKey
ALTER TABLE `users` ADD CONSTRAINT `users_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `loans` ADD CONSTRAINT `loans_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `guarantors` ADD CONSTRAINT `guarantors_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `repayments` ADD CONSTRAINT `repayments_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `bank_transactions` ADD CONSTRAINT `bank_transactions_bank_account_id_fkey` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `bank_transactions` ADD CONSTRAINT `bank_transactions_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `bank_statements` ADD CONSTRAINT `bank_statements_bank_account_id_fkey` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `role_permission` ADD CONSTRAINT `role_permission_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `role_permission` ADD CONSTRAINT `role_permission_permission_id_fkey` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `documents` ADD CONSTRAINT `documents_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `documents` ADD CONSTRAINT `documents_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `payments` ADD CONSTRAINT `payments_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `payments` ADD CONSTRAINT `payments_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `payments` ADD CONSTRAINT `payments_repayment_id_fkey` FOREIGN KEY (`repayment_id`) REFERENCES `repayments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `payments` ADD CONSTRAINT `payments_receipt_document_id_fkey` FOREIGN KEY (`receipt_document_id`) REFERENCES `documents`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_loan_id_fkey` FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `support_messages` ADD CONSTRAINT `support_messages_borrower_id_fkey` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `support_messages` ADD CONSTRAINT `support_messages_responded_by_user_id_fkey` FOREIGN KEY (`responded_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

