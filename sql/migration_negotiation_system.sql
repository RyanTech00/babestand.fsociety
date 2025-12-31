-- =====================================================
-- MIGRAÇÃO: Sistema de Negociação Venda/Troca
-- =====================================================

-- 1. Alterar tabela sell_trade_requests para novo sistema
ALTER TABLE sell_trade_requests
    MODIFY COLUMN status ENUM('aguarda_oferta', 'oferta_enviada', 'contraproposta', 'utilizador_aceitou', 'admin_aceitou', 'concluido', 'recusado', 'cancelado') DEFAULT 'aguarda_oferta',
    ADD COLUMN current_offer DECIMAL(12,2) NULL AFTER message,
    ADD COLUMN user_counter_offer DECIMAL(12,2) NULL AFTER current_offer,
    ADD COLUMN final_price DECIMAL(12,2) NULL AFTER user_counter_offer;

-- 2. Tabela de histórico de negociação
CREATE TABLE IF NOT EXISTS negotiation_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    message TEXT NULL,
    offer_value DECIMAL(12,2) NULL,
    action ENUM('initial', 'offer', 'counter_offer', 'accept', 'reject', 'message') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES sell_trade_requests(id) ON DELETE CASCADE,
    INDEX idx_request (request_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Atualizar registos existentes para novo estado
UPDATE sell_trade_requests SET status = 'aguarda_oferta' WHERE status = 'pendente';
UPDATE sell_trade_requests SET status = 'aguarda_oferta' WHERE status = 'em_analise';
