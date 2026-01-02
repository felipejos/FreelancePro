-- Migration: Criar tabela de aceite de termos de uso
-- Execute: mysql -u root -p freelancepro_dev < database/migrations/2024_12_26_create_terms_acceptance_table.sql

CREATE TABLE IF NOT EXISTS `terms_acceptances` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `terms_version` VARCHAR(20) NOT NULL DEFAULT '1.0',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `accepted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_terms_version` (`terms_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar coluna terms_accepted em user_profiles
ALTER TABLE `user_profiles` ADD COLUMN `terms_accepted_at` TIMESTAMP NULL AFTER `last_login`;

-- Registrar migration
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2024_12_26_create_terms_acceptance_table', 1);
