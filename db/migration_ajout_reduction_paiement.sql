-- Migration: Ajouter les colonnes de réduction et moyen de paiement à la table lignevente
-- Cette migration ajoute les colonnes nécessaires pour gérer les réductions et les moyens de paiement

ALTER TABLE `details_vente` ADD COLUMN `tauxReduction` DOUBLE DEFAULT 0 AFTER `remboursement`;
ALTER TABLE `details_vente` ADD COLUMN `montantReduction` DOUBLE DEFAULT 0 AFTER `tauxReduction`;
ALTER TABLE `details_vente` ADD COLUMN `totalApresReduction` DOUBLE DEFAULT 0 AFTER `montantReduction`;
ALTER TABLE `details_vente` ADD COLUMN `moyenPaiement` VARCHAR(50) DEFAULT 'especes' AFTER `totalApresReduction`;

-- Optionnel: Ajouter une colonne de reference de facture
ALTER TABLE `details_vente` ADD COLUMN `reference_facture` VARCHAR(50) AFTER `Created_at`;
