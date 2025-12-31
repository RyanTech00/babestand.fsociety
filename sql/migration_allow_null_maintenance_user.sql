-- =====================================================
-- MIGRAÇÃO: Permitir user_id NULL em vehicle_maintenance
-- Para manter histórico quando veículo vai para o stand
-- BabeStand - 2025-12-29
-- =====================================================

-- Remover foreign key existente
ALTER TABLE vehicle_maintenance DROP FOREIGN KEY vehicle_maintenance_ibfk_2;

-- Permitir NULL no user_id
ALTER TABLE vehicle_maintenance MODIFY user_id INT UNSIGNED NULL;

-- Recriar foreign key com ON DELETE SET NULL
ALTER TABLE vehicle_maintenance
ADD CONSTRAINT fk_maintenance_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
