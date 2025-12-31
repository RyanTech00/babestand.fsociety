-- ============================================
-- BabeStand - Dados de Exemplo (Seed)
-- ============================================

SET NAMES utf8mb4;

-- --------------------------------------------
-- Admin
-- Password: 
-- --------------------------------------------
INSERT INTO `users` (`role_id`, `name`, `email`, `password`, `phone`, `is_active`, `email_verified_at`) VALUES
(1, 'Administrador', 'admin@babestand.fsociety.pt', '$argon2id$v=19$m=65536,t=4,p=3$YzJGNWRHVmtjR0Z6YzNkdg$K8rJPxQH5kPvYmJqZmNhN2RlZjEyMzQ1Njc4OTBhYmNk', '+351 912 345 678', 1, NOW());

-- --------------------------------------------
-- Utilizadores de teste
-- Password: 
-- --------------------------------------------
INSERT INTO `users` (`role_id`, `name`, `email`, `password`, `phone`, `address`, `nif`, `is_active`, `email_verified_at`) VALUES
(2, 'João Silva', 'joao.silva@example.com', '$argon2id$v=19$m=65536,t=4,p=3$dXNlcnBhc3N3b3JkMTIz$K8rJPxQH5kPvYmJqZmNhN2RlZjEyMzQ1Njc4OTBhYmNk', '+351 913 456 789', 'Rua das Flores, 45, Porto', '123456789', 1, NOW()),
(2, 'Maria Santos', 'maria.santos@example.com', '$argon2id$v=19$m=65536,t=4,p=3$dXNlcnBhc3N3b3JkMTIz$K8rJPxQH5kPvYmJqZmNhN2RlZjEyMzQ1Njc4OTBhYmNk', '+351 914 567 890', 'Av. da Liberdade, 100, Lisboa', '987654321', 1, NOW()),
(2, 'Pedro Costa', 'pedro.costa@example.com', '$argon2id$v=19$m=65536,t=4,p=3$dXNlcnBhc3N3b3JkMTIz$K8rJPxQH5kPvYmJqZmNhN2RlZjEyMzQ1Njc4OTBhYmNk', '+351 915 678 901', 'Rua do Comércio, 25, Braga', '456789123', 1, NOW());

-- --------------------------------------------
-- Veículos de exemplo
-- --------------------------------------------
INSERT INTO `vehicles` (`brand_id`, `model`, `version`, `fuel_type_id`, `status_id`, `year`, `color`, `mileage`, `doors`, `seats`, `power_hp`, `engine_cc`, `transmission`, `price`, `previous_price`, `description`, `is_featured`) VALUES
-- Volkswagen Golf
(19, 'Golf', '1.6 TDI Confortline', 2, 1, 2021, 'Cinzento', 45000, 5, 5, 115, 1598, 'Manual', 22500.00, 24000.00, 'Volkswagen Golf em excelente estado. Revisões feitas na marca. Único dono. Equipado com ar condicionado, navegação GPS, sensores de estacionamento e câmara traseira.', 1),

-- BMW Série 3
(2, 'Série 3', '320d M Sport', 2, 1, 2020, 'Preto', 62000, 4, 5, 190, 1995, 'Automática', 35900.00, NULL, 'BMW Série 3 com pacote M Sport. Interior em pele, teto de abrir panorâmico, Head-up display, sistema de som Harman Kardon. Manutenção sempre feita na marca.', 1),

-- Mercedes-Benz Classe A
(11, 'Classe A', 'A180 AMG Line', 1, 1, 2022, 'Branco', 28000, 5, 5, 136, 1332, 'Automática', 32500.00, 34000.00, 'Mercedes-Benz Classe A com acabamento AMG Line. MBUX com ecrã de 10.25", pacote de luzes ambiente, jantes 18", assistente de parqueamento.', 1),

-- Renault Clio
(15, 'Clio', '1.0 TCe Intens', 1, 1, 2023, 'Vermelho', 15000, 5, 5, 100, 999, 'Manual', 18900.00, NULL, 'Renault Clio praticamente novo. Garantia de fábrica até 2026. Multimédia Easy Link 9.3", ar condicionado automático, sensores de luz e chuva.', 0),

-- Peugeot 3008
(14, '3008', '1.5 BlueHDi GT', 2, 1, 2021, 'Azul', 52000, 5, 5, 130, 1499, 'Automática', 29900.00, 32000.00, 'Peugeot 3008 GT com i-Cockpit. Teto panorâmico, navegação 3D conectada, câmara 360°, night vision. SUV versátil e económico.', 1),

-- Toyota Corolla Hybrid
(18, 'Corolla', '1.8 Hybrid Comfort', 3, 1, 2022, 'Prata', 35000, 5, 5, 122, 1798, 'Automática', 26500.00, NULL, 'Toyota Corolla Híbrido com consumos muito baixos. Toyota Safety Sense 2.0, ecrã táctil 8", Apple CarPlay e Android Auto. Garantia híbrido até 2032.', 0),

-- Audi A4
(1, 'A4', '2.0 TDI S-Line', 2, 1, 2020, 'Cinzento', 78000, 4, 5, 150, 1968, 'Automática', 31900.00, 33500.00, 'Audi A4 S-Line com Audi Virtual Cockpit. Bancos desportivos em pele/Alcantara, LED Matrix, sistema de som Bang & Olufsen.', 0),

-- Ford Focus
(6, 'Focus', '1.0 EcoBoost ST-Line', 1, 3, 2024, 'Laranja', 0, 5, 5, 125, 999, 'Manual', 27500.00, NULL, 'NOVO! Ford Focus ST-Line a chegar brevemente. Reserve já o seu. Motor EcoBoost eficiente, SYNC 4, Co-Pilot360.', 0),

-- Seat Leon
(16, 'Leon', '1.5 TSI FR', 1, 1, 2021, 'Vermelho', 41000, 5, 5, 150, 1498, 'Manual', 24900.00, 26500.00, 'Seat Leon FR com motor TSI. Jantes 18" Cosmo Grey, Full LED, navegação plus, painel digital. Carro muito dinâmico.', 0),

-- Hyundai Tucson
(8, 'Tucson', '1.6 CRDi Premium', 2, 1, 2022, 'Verde', 32000, 5, 5, 136, 1598, 'Automática', 33500.00, NULL, 'Hyundai Tucson com design arrojado. Ecrã 10.25", Bluelink connected, aquecimento de bancos e volante, SmartSense completo.', 1),

-- Nissan Qashqai
(12, 'Qashqai', 'e-Power N-Connecta', 3, 1, 2023, 'Preto', 18000, 5, 5, 190, 1498, 'Automática', 36900.00, 38500.00, 'Nissan Qashqai e-Power. Tecnologia revolucionária - motor elétrico alimentado por gasolina. ProPILOT, Around View Monitor.', 0),

-- Dacia Sandero
(4, 'Sandero', '1.0 TCe Expression', 1, 1, 2023, 'Azul', 12000, 5, 5, 90, 999, 'Manual', 14500.00, NULL, 'Dacia Sandero - Best seller! Excelente relação qualidade/preço. Media Display 8", ar condicionado, controlo de velocidade.', 0),

-- Kia Sportage
(9, 'Sportage', '1.6 T-GDi GT-Line', 1, 2, 2021, 'Branco', 55000, 5, 5, 177, 1598, 'Automática', 28900.00, 30000.00, 'Kia Sportage GT-Line. Atualmente em manutenção. Disponível em breve. Interior premium, teto panorâmico, garantia 7 anos.', 0),

-- Citroën C3
(3, 'C3', '1.2 PureTech Shine', 1, 1, 2022, 'Branco', 22000, 5, 5, 83, 1199, 'Manual', 16900.00, NULL, 'Citroën C3 com design único e Airbumps. ConnectedCAM integrada, ecrã táctil 10", programa Citroën Advanced Comfort.', 0),

-- Volvo XC40
(20, 'XC40', 'T4 R-Design', 1, 1, 2021, 'Preto', 48000, 5, 5, 190, 1969, 'Automática', 38500.00, 41000.00, 'Volvo XC40 R-Design. Segurança máxima com Pilot Assist, City Safety. Interior escandinavo premium, Harman Kardon.', 1);

-- --------------------------------------------
-- Imagens dos veículos (placeholder)
-- --------------------------------------------
INSERT INTO `vehicle_images` (`vehicle_id`, `filename`, `original_name`, `is_primary`, `sort_order`) VALUES
(1, 'golf_1.jpg', 'volkswagen_golf_front.jpg', 1, 0),
(1, 'golf_2.jpg', 'volkswagen_golf_rear.jpg', 0, 1),
(1, 'golf_3.jpg', 'volkswagen_golf_interior.jpg', 0, 2),
(2, 'bmw_320d_1.jpg', 'bmw_serie3_front.jpg', 1, 0),
(2, 'bmw_320d_2.jpg', 'bmw_serie3_side.jpg', 0, 1),
(3, 'mercedes_a180_1.jpg', 'mercedes_classea_front.jpg', 1, 0),
(4, 'clio_1.jpg', 'renault_clio_front.jpg', 1, 0),
(5, 'peugeot_3008_1.jpg', 'peugeot_3008_front.jpg', 1, 0),
(6, 'corolla_1.jpg', 'toyota_corolla_front.jpg', 1, 0),
(7, 'audi_a4_1.jpg', 'audi_a4_front.jpg', 1, 0),
(8, 'focus_1.jpg', 'ford_focus_front.jpg', 1, 0),
(9, 'leon_1.jpg', 'seat_leon_front.jpg', 1, 0),
(10, 'tucson_1.jpg', 'hyundai_tucson_front.jpg', 1, 0),
(11, 'qashqai_1.jpg', 'nissan_qashqai_front.jpg', 1, 0),
(12, 'sandero_1.jpg', 'dacia_sandero_front.jpg', 1, 0),
(13, 'sportage_1.jpg', 'kia_sportage_front.jpg', 1, 0),
(14, 'c3_1.jpg', 'citroen_c3_front.jpg', 1, 0),
(15, 'xc40_1.jpg', 'volvo_xc40_front.jpg', 1, 0);

-- --------------------------------------------
-- Test drives de exemplo
-- --------------------------------------------
INSERT INTO `test_drives` (`user_id`, `vehicle_id`, `status_id`, `scheduled_date`, `scheduled_time`, `notes`) VALUES
(2, 1, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Gostaria de testar o Golf antes de decidir.'),
(3, 3, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', 'Interessada no Mercedes Classe A.'),
(4, 5, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'Test drive realizado com sucesso.');

-- --------------------------------------------
-- Favoritos de exemplo
-- --------------------------------------------
INSERT INTO `favorites` (`user_id`, `vehicle_id`) VALUES
(2, 1),
(2, 3),
(3, 5),
(3, 10),
(4, 2);

-- --------------------------------------------
-- Mensagens de contacto de exemplo
-- --------------------------------------------
INSERT INTO `contact_messages` (`name`, `email`, `phone`, `subject`, `message`, `vehicle_id`, `ip_address`) VALUES
('António Ferreira', 'antonio.f@email.com', '+351 916 789 012', 'Informação sobre BMW Série 3', 'Bom dia, gostaria de saber se o BMW ainda está disponível e se aceitam retoma. Obrigado.', 2, '192.168.1.100'),
('Sandra Oliveira', 'sandra.o@email.com', NULL, 'Financiamento disponível?', 'Olá, têm opções de financiamento para os vossos veículos? Obrigada.', NULL, '192.168.1.101');

-- ============================================
-- Fim do Seed
-- ============================================
