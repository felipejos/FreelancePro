-- Migration: Criar tabela de violações de conteúdo (monitoramento IA)
-- Execute: mysql -u root -p freelancepro_dev < database/migrations/2024_12_26_create_content_violations_table.sql

CREATE TABLE IF NOT EXISTS `content_violations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `context` VARCHAR(100) NOT NULL COMMENT 'message, proposal, project, etc',
    `content` TEXT NOT NULL COMMENT 'Conteúdo original',
    `violations_json` JSON NULL COMMENT 'Detalhes das violações detectadas',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` TIMESTAMP NULL,
    `action_taken` VARCHAR(50) NULL COMMENT 'approved, warning, block, ban',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar migration
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2024_12_26_create_content_violations_table', 1);
