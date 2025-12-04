-- ============================================
-- FreelancePro - Schema Inicial do Banco de Dados
-- Execute este arquivo APENAS UMA VEZ para criar as tabelas
-- Depois use migrations para alterações
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABELA: migrations (controle de migrações)
-- ============================================
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: user_profiles (usuários do sistema)
-- ============================================
CREATE TABLE IF NOT EXISTS `user_profiles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `user_type` ENUM('admin', 'company', 'professional', 'employee') NOT NULL DEFAULT 'company',
    `phone` VARCHAR(20) NULL,
    `cpf` VARCHAR(14) NULL,
    `birth_date` DATE NULL,
    `avatar` VARCHAR(255) NULL,
    `company_id` INT UNSIGNED NULL COMMENT 'Referência para empresa (funcionários)',
    `status` ENUM('active', 'inactive', 'pending', 'blocked') NOT NULL DEFAULT 'pending',
    `email_verified_at` TIMESTAMP NULL,
    `remember_token` VARCHAR(100) NULL,
    `reset_token` VARCHAR(255) NULL,
    `reset_token_expires` TIMESTAMP NULL,
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_type` (`user_type`),
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: user_addresses (endereços dos usuários)
-- ============================================
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `number` VARCHAR(20) NOT NULL,
    `complement` VARCHAR(100) NULL,
    `neighborhood` VARCHAR(100) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(2) NOT NULL,
    `zip_code` VARCHAR(10) NOT NULL,
    `is_default` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: company_subscriptions (assinaturas)
-- ============================================
CREATE TABLE IF NOT EXISTS `company_subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'inactive', 'cancelled', 'pending', 'overdue') NOT NULL DEFAULT 'pending',
    `assas_subscription_id` VARCHAR(255) NULL,
    `assas_customer_id` VARCHAR(255) NULL,
    `current_period_start` DATE NULL,
    `current_period_end` DATE NULL,
    `cancelled_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: subscription_plans (planos disponíveis)
-- ============================================
CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `billing_cycle` ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly',
    `features` JSON NULL,
    `max_employees` INT NULL,
    `max_playbooks` INT NULL,
    `max_courses` INT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: company_playbooks (playbooks/treinamentos)
-- ============================================
CREATE TABLE IF NOT EXISTS `company_playbooks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `content_html` LONGTEXT NULL,
    `source_type` ENUM('text', 'file', 'audio') NOT NULL DEFAULT 'text',
    `source_file` VARCHAR(255) NULL,
    `rules_policies` JSON NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `is_paid` BOOLEAN DEFAULT FALSE,
    `payment_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: playbook_questions (questionários)
-- ============================================
CREATE TABLE IF NOT EXISTS `playbook_questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `playbook_id` INT UNSIGNED NOT NULL,
    `question_number` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `option_a` VARCHAR(500) NOT NULL,
    `option_b` VARCHAR(500) NOT NULL,
    `option_c` VARCHAR(500) NOT NULL,
    `option_d` VARCHAR(500) NOT NULL,
    `correct_option` CHAR(1) NOT NULL,
    `explanation` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`playbook_id`) REFERENCES `company_playbooks`(`id`) ON DELETE CASCADE,
    INDEX `idx_playbook_id` (`playbook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: playbook_assignments (atribuições)
-- ============================================
CREATE TABLE IF NOT EXISTS `playbook_assignments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `playbook_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NOT NULL,
    `assigned_by` INT UNSIGNED NOT NULL,
    `due_date` DATE NULL,
    `status` ENUM('pending', 'in_progress', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `score` DECIMAL(5,2) NULL,
    `passed` BOOLEAN NULL,
    `attempts` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`playbook_id`) REFERENCES `company_playbooks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_playbook_id` (`playbook_id`),
    INDEX `idx_employee_id` (`employee_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: playbook_answers (respostas)
-- ============================================
CREATE TABLE IF NOT EXISTS `playbook_answers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NOT NULL,
    `selected_option` CHAR(1) NOT NULL,
    `is_correct` BOOLEAN NOT NULL,
    `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`assignment_id`) REFERENCES `playbook_assignments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `playbook_questions`(`id`) ON DELETE CASCADE,
    INDEX `idx_assignment_id` (`assignment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: courses (cursos)
-- ============================================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `thumbnail` VARCHAR(255) NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `is_paid` BOOLEAN DEFAULT FALSE,
    `total_modules` INT DEFAULT 0,
    `total_lessons` INT DEFAULT 0,
    `estimated_hours` DECIMAL(5,2) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: course_modules (módulos do curso)
-- ============================================
CREATE TABLE IF NOT EXISTS `course_modules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `order_number` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    INDEX `idx_course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: course_lessons (aulas)
-- ============================================
CREATE TABLE IF NOT EXISTS `course_lessons` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content_html` LONGTEXT NULL,
    `video_url` VARCHAR(500) NULL,
    `order_number` INT NOT NULL DEFAULT 1,
    `duration_minutes` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE,
    INDEX `idx_module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: course_questions (questões do curso)
-- ============================================
CREATE TABLE IF NOT EXISTS `course_questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module_id` INT UNSIGNED NOT NULL,
    `question_number` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `option_a` VARCHAR(500) NOT NULL,
    `option_b` VARCHAR(500) NOT NULL,
    `option_c` VARCHAR(500) NOT NULL,
    `option_d` VARCHAR(500) NOT NULL,
    `correct_option` CHAR(1) NOT NULL,
    `explanation` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE,
    INDEX `idx_module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: course_enrollments (matrículas)
-- ============================================
CREATE TABLE IF NOT EXISTS `course_enrollments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NOT NULL,
    `enrolled_by` INT UNSIGNED NOT NULL,
    `status` ENUM('enrolled', 'in_progress', 'completed', 'dropped') NOT NULL DEFAULT 'enrolled',
    `progress_percentage` DECIMAL(5,2) DEFAULT 0,
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `final_score` DECIMAL(5,2) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`enrolled_by`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: course_progress (progresso nas aulas)
-- ============================================
CREATE TABLE IF NOT EXISTS `course_progress` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT UNSIGNED NOT NULL,
    `lesson_id` INT UNSIGNED NOT NULL,
    `status` ENUM('not_started', 'in_progress', 'completed') NOT NULL DEFAULT 'not_started',
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_enrollment_lesson` (`enrollment_id`, `lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: employees (funcionários - dados extras)
-- ============================================
CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `company_id` INT UNSIGNED NOT NULL,
    `department` VARCHAR(100) NULL,
    `position` VARCHAR(100) NULL,
    `hire_date` DATE NULL,
    `employee_code` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_company` (`user_id`, `company_id`),
    INDEX `idx_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: projects (projetos freelancer)
-- ============================================
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(100) NULL,
    `skills_required` JSON NULL,
    `budget_min` DECIMAL(10,2) NULL,
    `budget_max` DECIMAL(10,2) NULL,
    `deadline` DATE NULL,
    `status` ENUM('open', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
    `selected_proposal_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: proposals (propostas)
-- ============================================
CREATE TABLE IF NOT EXISTS `proposals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `professional_id` INT UNSIGNED NOT NULL,
    `cover_letter` TEXT NOT NULL,
    `proposed_value` DECIMAL(10,2) NOT NULL,
    `estimated_days` INT NOT NULL,
    `status` ENUM('pending', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`professional_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_project_id` (`project_id`),
    INDEX `idx_professional_id` (`professional_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: project_messages (mensagens de projetos)
-- ============================================
CREATE TABLE IF NOT EXISTS `project_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `proposal_id` INT UNSIGNED NOT NULL,
    `sender_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`proposal_id`) REFERENCES `proposals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_project_id` (`project_id`),
    INDEX `idx_proposal_id` (`proposal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: contracts (contratos)
-- ============================================
CREATE TABLE IF NOT EXISTS `contracts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `proposal_id` INT UNSIGNED NOT NULL,
    `company_id` INT UNSIGNED NOT NULL,
    `professional_id` INT UNSIGNED NOT NULL,
    `contract_value` DECIMAL(10,2) NOT NULL,
    `platform_fee` DECIMAL(10,2) NOT NULL COMMENT '7% do valor',
    `professional_amount` DECIMAL(10,2) NOT NULL COMMENT 'Valor - taxa',
    `status` ENUM('active', 'completed', 'cancelled', 'disputed') NOT NULL DEFAULT 'active',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`proposal_id`) REFERENCES `proposals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`professional_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_project_id` (`project_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: reviews (avaliações)
-- ============================================
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `reviewer_id` INT UNSIGNED NOT NULL,
    `reviewed_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewer_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_contract_id` (`contract_id`),
    INDEX `idx_reviewed_id` (`reviewed_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: payment_transactions (pagamentos)
-- ============================================
CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('registration', 'subscription', 'playbook', 'contract') NOT NULL,
    `reference_id` INT UNSIGNED NULL COMMENT 'ID da referência (playbook, contrato, etc)',
    `amount` DECIMAL(10,2) NOT NULL,
    `description` VARCHAR(255) NULL,
    `assas_payment_id` VARCHAR(255) NULL,
    `assas_invoice_url` VARCHAR(500) NULL,
    `payment_method` VARCHAR(50) NULL,
    `status` ENUM('pending', 'confirmed', 'received', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `paid_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: admin_configs (configurações admin)
-- ============================================
CREATE TABLE IF NOT EXISTS `admin_configs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `config_key` VARCHAR(100) NOT NULL UNIQUE,
    `config_value` TEXT NULL,
    `config_type` ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `description` VARCHAR(255) NULL,
    `is_sensitive` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: email_configs (configuração de email)
-- ============================================
CREATE TABLE IF NOT EXISTS `email_configs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mail_driver` ENUM('smtp', 'mail') NOT NULL DEFAULT 'smtp',
    `smtp_host` VARCHAR(255) NULL,
    `smtp_port` INT NULL DEFAULT 587,
    `smtp_username` VARCHAR(255) NULL,
    `smtp_password` VARCHAR(255) NULL,
    `smtp_encryption` ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    `from_address` VARCHAR(255) NULL,
    `from_name` VARCHAR(255) NULL,
    `is_active` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: ai_logs (logs de IA)
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `input_text` TEXT NULL,
    `output_text` LONGTEXT NULL,
    `tokens_used` INT NULL,
    `model` VARCHAR(100) NULL,
    `cost` DECIMAL(10,6) NULL,
    `status` ENUM('success', 'error') NOT NULL DEFAULT 'success',
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERIR CONFIGURAÇÕES INICIAIS
-- ============================================

-- Planos de assinatura padrão
INSERT INTO `subscription_plans` (`name`, `description`, `price`, `billing_cycle`, `max_employees`, `max_playbooks`, `max_courses`, `features`) VALUES
('Básico', 'Plano básico para pequenas empresas', 29.90, 'monthly', 10, 5, 2, '["Até 10 funcionários", "5 playbooks/mês", "2 cursos", "Suporte por email"]'),
('Profissional', 'Plano profissional para empresas em crescimento', 79.90, 'monthly', 50, 20, 10, '["Até 50 funcionários", "20 playbooks/mês", "10 cursos", "Suporte prioritário", "Relatórios avançados"]'),
('Enterprise', 'Plano empresarial completo', 199.90, 'monthly', NULL, NULL, NULL, '["Funcionários ilimitados", "Playbooks ilimitados", "Cursos ilimitados", "Suporte 24/7", "API Access", "Customização"]');

-- Configurações padrão do admin
INSERT INTO `admin_configs` (`config_key`, `config_value`, `config_type`, `description`, `is_sensitive`) VALUES
('platform_name', 'FreelancePro', 'string', 'Nome da plataforma', FALSE),
('openai_api_key', '', 'string', 'Chave API do OpenAI', TRUE),
('assas_api_key', '', 'string', 'Chave API do ASSAS', TRUE),
('assas_environment', 'sandbox', 'string', 'Ambiente do ASSAS (sandbox/production)', FALSE),
('recaptcha_site_key', '', 'string', 'Chave do site reCAPTCHA', FALSE),
('recaptcha_secret_key', '', 'string', 'Chave secreta reCAPTCHA', TRUE),
('registration_fee', '29.90', 'number', 'Taxa de registro', FALSE),
('monthly_fee', '29.90', 'number', 'Mensalidade', FALSE),
('playbook_fee', '19.90', 'number', 'Taxa por playbook', FALSE),
('freelancer_fee', '0.07', 'number', 'Taxa freelancer (decimal)', FALSE);

-- Configuração de email padrão
INSERT INTO `email_configs` (`mail_driver`, `smtp_host`, `smtp_port`, `smtp_encryption`, `from_name`, `is_active`) VALUES
('smtp', 'smtp.gmail.com', 587, 'tls', 'FreelancePro', FALSE);

-- Criar usuário admin padrão (senha: admin123)
INSERT INTO `user_profiles` (`name`, `email`, `password`, `user_type`, `status`, `email_verified_at`) VALUES
('Administrador', 'admin@freelancepro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW());

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- FIM DO SCHEMA INICIAL
-- Para alterações futuras, use MIGRATIONS
-- ============================================
