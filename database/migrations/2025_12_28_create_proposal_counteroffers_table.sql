-- =============================================
-- Tabela de contrapropostas de propostas
-- =============================================

CREATE TABLE IF NOT EXISTS proposal_counteroffers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_type ENUM('company', 'professional') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    estimated_days INT NOT NULL,
    message TEXT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending',
    responded_by INT NULL,
    responded_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_counter_proposal FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE
);

-- Status de negociação na tabela de propostas
ALTER TABLE proposals
    ADD COLUMN negotiation_status VARCHAR(32) NOT NULL DEFAULT 'idle' AFTER status;
