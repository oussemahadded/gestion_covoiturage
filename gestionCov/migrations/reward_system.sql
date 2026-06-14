-- ============================================================
-- Migration : Système de récompenses dynamiques — CHAYA3NI
-- Niveaux de récompense basés sur les points conducteurs
-- Eligibilité : conducteur avec email @sesame.com.tn
-- ============================================================

-- 1. Table des niveaux de récompense (dynamique, admin-configurable)
CREATE TABLE IF NOT EXISTS `reward_levels` (
  `id`             INT(11)         NOT NULL AUTO_INCREMENT,
  `label`          VARCHAR(50)     NOT NULL COMMENT 'Ex: Bronze, Silver, Gold, Platinum',
  `min_points`     INT(11)         NOT NULL COMMENT 'Points minimum pour atteindre ce niveau',
  `remise_percent` DECIMAL(5,2)    NOT NULL DEFAULT 0.00 COMMENT 'Pourcentage de remise accordé',
  `badge_color`    VARCHAR(20)     NOT NULL DEFAULT '#CD7F32' COMMENT 'Couleur hex du badge',
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_label` (`label`),
  KEY `idx_min_points` (`min_points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Niveaux de récompense définis par l\'administrateur';

-- 2. Données par défaut (ignorer si déjà présentes)
INSERT IGNORE INTO `reward_levels` (`label`, `min_points`, `remise_percent`, `badge_color`) VALUES
  ('Bronze',   5000,  2.00,  '#CD7F32'),
  ('Silver',  10000,  5.00,  '#A8A9AD'),
  ('Gold',    20000, 10.00,  '#FFD700'),
  ('Platinum',50000, 15.00,  '#E5E4E2');

-- 3. Table d'historique des changements de niveau
CREATE TABLE IF NOT EXISTS `reward_history` (
  `id`          INT(11)         NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11)         NOT NULL,
  `old_level`   VARCHAR(50)     DEFAULT NULL COMMENT 'Niveau précédent (NULL = premier niveau atteint)',
  `new_level`   VARCHAR(50)     NOT NULL,
  `old_remise`  DECIMAL(5,2)    DEFAULT NULL,
  `new_remise`  DECIMAL(5,2)    NOT NULL,
  `changed_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reward_user` (`user_id`),
  KEY `idx_reward_changed_at` (`changed_at`),
  CONSTRAINT `fk_reward_user`
    FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Historique des changements de niveau de récompense';

-- 4. Assurer que la colonne points_total existe sur utilisateurs
ALTER TABLE `utilisateurs`
  ADD COLUMN IF NOT EXISTS `points_total` INT(11) NOT NULL DEFAULT 0
  COMMENT 'Total de points accumulés par le conducteur';

-- ============================================================
-- FIN DE LA MIGRATION
-- ============================================================
