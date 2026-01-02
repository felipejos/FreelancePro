-- Migration: Add service contract file columns to contracts table
ALTER TABLE `contracts`
    ADD COLUMN `service_contract_path` VARCHAR(255) NULL AFTER `professional_amount`,
    ADD COLUMN `service_contract_original_name` VARCHAR(255) NULL AFTER `service_contract_path`;
