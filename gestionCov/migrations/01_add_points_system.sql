-- Migration: Ajouter le système de récompense par points
-- Date: 2026-06-08
-- Description: Remplace le système de paiement par un système de points cumulatifs

-- ============================================================================
-- 1. Modifier la table utilisateurs: ajouter les champs de points
-- ============================================================================

ALTER TABLE `utilisateurs` 
ADD COLUMN `points_total` INT DEFAULT 0 COMMENT 'Total de points accumulés',
ADD COLUMN `eligibilite_remise_sesame_at` DATETIME NULL COMMENT 'Date d''accès à la remise SESAME (100+ points)';

-- ============================================================================
-- 2. Créer la table d'historique des points
-- ============================================================================

CREATE TABLE IF NOT EXISTS `points_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `conducteur_id` INT(11) NOT NULL,
  `reservation_id` INT(11) DEFAULT NULL,
  `points_gained` INT(11) NOT NULL,
  `reason` VARCHAR(100) NOT NULL COMMENT 'ex: trajet_complete, bonus, etc',
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conducteur` (`conducteur_id`),
  KEY `idx_reservation` (`reservation_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_points_history_conducteur` 
    FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_points_history_reservation` 
    FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 3. Modifier la table réservations: ajouter le champ points_earned
-- ============================================================================

ALTER TABLE `reservations` 
ADD COLUMN `points_earned` INT(11) DEFAULT NULL COMMENT 'Points gagnés par le conducteur lors de la complétion du trajet';

-- ============================================================================
-- 4. Ajouter les paramètres de configuration pour le système de points
-- ============================================================================

INSERT INTO `app_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('points_par_trajet', '10', NOW()),
('points_seuil_remise_sesame', '100', NOW())
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ============================================================================
-- 5. Indexes supplémentaires pour optimiser les requêtes de points
-- ============================================================================

ALTER TABLE `utilisateurs` 
ADD INDEX `idx_points_total` (`points_total`),
ADD INDEX `idx_eligibilite_sesame` (`eligibilite_remise_sesame_at`);

-- ============================================================================
-- IMPORTANT: NOTES DE MIGRATION
-- ============================================================================
-- 
-- 1. La logique de paiement (prix, payment_status, etc.) est CONSERVÉE pour
--    compatibilité historique et traçabilité, mais N'EST PLUS UTILISÉE
--    dans la logique métier.
--
-- 2. Les réservations existantes NE GENERERONT PAS de points rétroactifs.
--    Seules les NOUVELLES réservations confirmées généreront des points.
--
-- 3. Un trajet terminé = 10 points gagnés par le conducteur.
--
-- 4. A 100 points, le conducteur devient éligible à une remise SESAME.
--    La colonne `eligibilite_remise_sesame_at` enregistre la date.
--
-- 5. Les points NE PEUVENT PAS être échangés, vendus, ni transférés.
--    Ils ne sont QUE cumulatifs pour déterminer l'éligibilité.
--
-- ============================================================================
