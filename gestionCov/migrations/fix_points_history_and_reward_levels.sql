-- =============================================================================
-- Migration: fix_points_history_and_reward_levels.sql
-- Purpose  : Align points_history to modern schema + seed reward_levels tiers
-- Run once : safe to re-run (all ALTER IF NOT EXISTS, upserts)
-- =============================================================================

-- ---------------------------------------------------------------------------
-- 1. points_history — modern schema
--    Required columns: utilisateur_id, points_awarded, entity_type, entity_id
-- ---------------------------------------------------------------------------

-- Add utilisateur_id if missing
ALTER TABLE `points_history`
    ADD COLUMN IF NOT EXISTS `utilisateur_id` INT(11)     NULL DEFAULT NULL AFTER `id`;

-- Add points_awarded if missing
ALTER TABLE `points_history`
    ADD COLUMN IF NOT EXISTS `points_awarded`  INT(11)     NOT NULL DEFAULT 0 AFTER `utilisateur_id`;

-- Add entity_type if missing
ALTER TABLE `points_history`
    ADD COLUMN IF NOT EXISTS `entity_type`     VARCHAR(50) NULL DEFAULT NULL;

-- Add entity_id if missing
ALTER TABLE `points_history`
    ADD COLUMN IF NOT EXISTS `entity_id`       INT(11)     NULL DEFAULT NULL;

-- Add created_at if missing
ALTER TABLE `points_history`
    ADD COLUMN IF NOT EXISTS `created_at`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Add index on utilisateur_id for faster lookups
ALTER TABLE `points_history`
    ADD INDEX IF NOT EXISTS `idx_ph_utilisateur` (`utilisateur_id`);

-- ---------------------------------------------------------------------------
-- 2. Migrate legacy rows (if any) from old schema to new columns
--    Old: conducteur_id → utilisateur_id, points_gained → points_awarded
-- ---------------------------------------------------------------------------

-- If the old conducteur_id column exists, copy its values into utilisateur_id
UPDATE `points_history`
SET `utilisateur_id` = `conducteur_id`
WHERE `utilisateur_id` IS NULL
  AND EXISTS (
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME   = 'points_history'
        AND COLUMN_NAME  = 'conducteur_id'
  );

-- If the old points_gained column exists, copy its values into points_awarded
UPDATE `points_history`
SET `points_awarded` = `points_gained`
WHERE `points_awarded` = 0
  AND EXISTS (
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME   = 'points_history'
        AND COLUMN_NAME  = 'points_gained'
  );

-- Fill entity_type from legacy reason column where entity_type is null
UPDATE `points_history`
SET `entity_type` = 'trajet_complete'
WHERE `entity_type` IS NULL;

-- ---------------------------------------------------------------------------
-- 3. reward_levels — seed the four canonical tiers
--    Uses INSERT ... ON DUPLICATE KEY UPDATE for idempotency.
--    Assumes reward_levels has a UNIQUE key on (label) or (min_points).
-- ---------------------------------------------------------------------------

-- Ensure the table exists (create if not)
CREATE TABLE IF NOT EXISTS `reward_levels` (
    `id`             INT(11)        NOT NULL AUTO_INCREMENT,
    `label`          VARCHAR(50)    NOT NULL,
    `min_points`     INT(11)        NOT NULL DEFAULT 0,
    `remise_percent` DECIMAL(5,2)   NOT NULL DEFAULT 0.00,
    `badge_color`    VARCHAR(20)    NOT NULL DEFAULT '#6B7280',
    `created_at`     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_label`      (`label`),
    UNIQUE KEY `uq_min_points` (`min_points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upsert the four tiers
INSERT INTO `reward_levels` (`label`, `min_points`, `remise_percent`, `badge_color`)
VALUES
    ('Bronze',   5000,  2.00,  '#CD7F32'),
    ('Silver',   10000, 5.00,  '#A8A9AD'),
    ('Gold',     20000, 10.00, '#FFD700'),
    ('Platinum', 50000, 15.00, '#E5E4E2')
ON DUPLICATE KEY UPDATE
    `remise_percent` = VALUES(`remise_percent`),
    `badge_color`    = VALUES(`badge_color`);

-- ---------------------------------------------------------------------------
-- 4. app_settings — ensure points-per-km rate is set to 250 (default)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `app_settings` (
    `id`            INT(11)       NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(100)  NOT NULL,
    `setting_value` VARCHAR(255)  NOT NULL,
    `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `app_settings` (`setting_key`, `setting_value`)
VALUES ('prix_par_km', '250.000')
ON DUPLICATE KEY UPDATE
    `setting_value` = CASE
        WHEN CAST(`setting_value` AS DECIMAL(10,3)) <= 10 THEN '250.000'
        ELSE `setting_value`
    END;

-- =============================================================================
-- END OF MIGRATION
-- =============================================================================
