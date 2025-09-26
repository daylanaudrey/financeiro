-- Migration: Update process status to customs statuses
-- Sistema Aduaneiro - Atualizar status dos processos para status aduaneiros

ALTER TABLE `processes`
MODIFY COLUMN `status` ENUM('PRE_EMBARQUE', 'EMBARCADO', 'REGISTRADO', 'CANAL_VERDE', 'CANAL_VERMELHO', 'CANAL_CINZA') DEFAULT 'PRE_EMBARQUE';