-- Migration: Criar tabela de notificações
-- Execute: mysql -u root -p freelancepro_dev < database/migrations/2024_12_26_create_notifications_table.sql

CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL COMMENT 'proposal_new, proposal_accepted, proposal_rejected, payment_confirmed, pending_review, etc',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `reference_id` INT UNSIGNED NULL COMMENT 'ID de referência (projeto, proposta, etc)',
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar migration
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2024_12_26_create_notifications_table', 1);
