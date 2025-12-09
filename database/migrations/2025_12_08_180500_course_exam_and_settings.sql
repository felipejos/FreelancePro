-- ============================================
-- Migration: course_exam_and_settings
-- Data: 2025-12-08 18:05:00
-- ============================================

-- UP: criar company_settings e estruturas de prova de módulo de curso

-- Tabela de configurações por empresa
CREATE TABLE IF NOT EXISTS `company_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_company_setting` (`company_id`,`setting_key`),
  FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limite/lock em matrículas de curso
ALTER TABLE `course_enrollments` 
  ADD COLUMN `is_locked` BOOLEAN DEFAULT FALSE AFTER `status`;

-- Respostas por módulo (curso)
CREATE TABLE IF NOT EXISTS `course_answers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `module_id` INT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `selected_option` CHAR(1) NOT NULL,
  `is_correct` BOOLEAN NOT NULL,
  `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `course_questions`(`id`) ON DELETE CASCADE,
  INDEX `idx_enrollment_module` (`enrollment_id`, `module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resultado/controle de tentativas por módulo (curso)
CREATE TABLE IF NOT EXISTS `course_module_results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `module_id` INT UNSIGNED NOT NULL,
  `attempts` INT DEFAULT 0,
  `score` DECIMAL(5,2) NULL,
  `passed` BOOLEAN NULL,
  `locked` BOOLEAN DEFAULT FALSE,
  `last_attempt_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_enrollment_module` (`enrollment_id`, `module_id`),
  FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- IMPORTANTE: Execute manualmente e depois rode:
-- php migrate.php mark 2025_12_08_180500_course_exam_and_settings.sql
-- ============================================
