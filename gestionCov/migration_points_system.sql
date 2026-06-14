-- ============================================================
-- Migration : Système de récompense basé sur des points
-- Remplace le paiement monétaire par des points conducteur
-- Règle : 1 km = 250 points
-- ============================================================

-- 1. Colonnes points dans la table utilisateurs
ALTER TABLE `utilisateurs`
  ADD COLUMN IF NOT EXISTS `points_total` INT(11) NOT NULL DEFAULT 0 COMMENT 'Total de points accumulés par le conducteur',
  ADD COLUMN IF NOT EXISTS `eligibilite_remise_sesame_at` DATETIME DEFAULT NULL COMMENT 'Date à laquelle le conducteur a atteint le seuil de remise SESAME';

-- 2. Colonne points_earned dans la table réservations (points conducteur attribués au trajet)
ALTER TABLE `reservations`
  ADD COLUMN IF NOT EXISTS `points_earned` INT(11) DEFAULT NULL COMMENT 'Points conducteur attribués lorsque ce trajet est terminé';

-- 3. Table historique des points
CREATE TABLE IF NOT EXISTS `points_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT(11) NOT NULL,
  `points_awarded` INT(11) NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL COMMENT 'Description lisible du gain',
  `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'trajet, bonus, etc.',
  `entity_id` INT(11) DEFAULT NULL,
  `created_at` DATETIME DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_points_user` (`utilisateur_id`),
  CONSTRAINT `fk_points_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Mise à jour du barème : prix_par_km = 250 (points/km)
UPDATE `app_settings`
SET `setting_value` = '250.000'
WHERE `setting_key` = 'prix_par_km';

-- Si la ligne n'existe pas encore, l'insérer
INSERT IGNORE INTO `app_settings` (`setting_key`, `setting_value`)
VALUES ('prix_par_km', '250.000');

-- 5. Élargir la colonne prix dans trajets pour supporter de grands nombres de points
--    (ex: 50 km × 250 pts = 12 500 points)
ALTER TABLE `trajets`
  MODIFY COLUMN `prix` DECIMAL(10,2) NOT NULL DEFAULT 0;

-- 6. Élargir prix_par_km pour supporter 250+ 
ALTER TABLE `trajets`
  MODIFY COLUMN `prix_par_km` DECIMAL(10,3) DEFAULT 250.000;

-- ============================================================
-- FIN DE LA MIGRATION
-- ============================================================
