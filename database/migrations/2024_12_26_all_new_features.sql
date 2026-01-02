-- ============================================
-- FreelancePro - Migrations para novas funcionalidades
-- Execute após o schema.sql inicial
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABELA: notifications (sistema de notificações)
-- ============================================
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

-- ============================================
-- TABELA: content_violations (monitoramento IA)
-- ============================================
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

-- ============================================
-- TABELA: terms_acceptances (aceite de termos)
-- ============================================
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

-- ============================================
-- ALTERAÇÕES EM TABELAS EXISTENTES
-- ============================================

-- Adicionar coluna terms_accepted_at em user_profiles (se não existir)
-- ALTER TABLE `user_profiles` ADD COLUMN IF NOT EXISTS `terms_accepted_at` TIMESTAMP NULL AFTER `last_login`;

-- Registrar migrations
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES 
    ('2024_12_26_create_notifications_table', 2),
    ('2024_12_26_create_content_violations_table', 2),
    ('2024_12_26_create_terms_acceptance_table', 2);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- FIM DAS MIGRATIONS
-- ============================================
