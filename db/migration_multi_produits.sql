-- ================================================
-- Script pour supporter les MULTI-PRODUITS par VENTE
-- ================================================

-- 1. Modifier la table `vente` pour ajouter les colonnes manquantes
ALTER TABLE `vente` 
ADD COLUMN `telephone` VARCHAR(100) AFTER `client`,
ADD COLUMN `observation` TEXT AFTER `telephone`,
ADD COLUMN `totalHT` DOUBLE AFTER `observation`,
MODIFY COLUMN `produit` VARCHAR(255) DEFAULT NULL,
MODIFY COLUMN `caracteristique` VARCHAR(255) DEFAULT NULL,
MODIFY COLUMN `numero_serie` VARCHAR(100) DEFAULT NULL,
MODIFY COLUMN `prix_unitaire` DOUBLE DEFAULT NULL,
MODIFY COLUMN `quantite` INT DEFAULT NULL,
MODIFY COLUMN `fin_garantie` DATE DEFAULT NULL;

-- 2. Créer la table `details_vente` pour les produits multiples
CREATE TABLE IF NOT EXISTS `details_vente` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `idvente` INT(11) NOT NULL,
  `idproduit` INT(11),
  `designation` VARCHAR(255) NOT NULL,
  `caracteristique` VARCHAR(255),
  `quantite` INT NOT NULL,
  `prixUnitaire` DOUBLE,
  `montant` DOUBLE,
  `finGarantie` DATE,
  `Created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`idvente`) REFERENCES `vente`(`idvente`) ON DELETE CASCADE,
  FOREIGN KEY (`idproduit`) REFERENCES `produit`(`idproduit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Ajouter un index sur idvente pour les performances
CREATE INDEX idx_details_vente_idvente ON `details_vente`(`idvente`);

-- ================================================
-- Notes:
-- - Lancez ce script dans phpMyAdmin avant de tester
-- - Les anciennes données seront conservées
-- - La table details_vente stocke les produits multiples
-- - La table vente contient l'entête de la facture
-- ================================================
