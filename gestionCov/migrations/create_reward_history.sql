-- =============================================================================
-- Migration: create_reward_history.sql
-- Creates the reward_history table used by Reward::syncRewardHistory() to
-- record when a conducteur unlocks a new tier (Bronze → Silver → Gold → Platinum).
--
-- Schema matches the existing PHP code in models/Reward.php which reads/writes:
--   user_id, old_level, new_level, old_remise, new_remise, changed_at
--
-- Safe to run on an existing database — uses CREATE TABLE IF NOT EXISTS.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `reward_history` (
    `id`          INT(11)        NOT NULL AUTO_INCREMENT,
    `user_id`     INT(11)        NOT NULL                  COMMENT 'Driver (conducteur) — references utilisateurs.id',
    `old_level`   VARCHAR(50)    NULL     DEFAULT NULL      COMMENT 'Tier label before the change (NULL = first tier)',
    `new_level`   VARCHAR(50)    NOT NULL                  COMMENT 'Tier label after the change (Bronze/Silver/Gold/Platinum)',
    `old_remise`  DECIMAL(5,2)   NULL     DEFAULT NULL      COMMENT 'Discount % before the change',
    `new_remise`  DECIMAL(5,2)   NOT NULL DEFAULT 0.00      COMMENT 'Discount % after the change',
    `changed_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the tier change was recorded',

    PRIMARY KEY (`id`),
    INDEX `idx_rh_user_id`    (`user_id`),
    INDEX `idx_rh_changed_at` (`changed_at`),

    CONSTRAINT `fk_reward_history_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `utilisateurs` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit log of tier-level changes per conducteur';

-- =============================================================================
-- Optional: back-fill history for any conducteur who has already earned points
-- and crossed a tier threshold but has no history row yet.
-- This INSERT is safe to run even if the table was just created (it inserts 0 rows
-- when no conducteur has earned points yet).
-- =============================================================================

INSERT INTO `reward_history` (`user_id`, `old_level`, `new_level`, `old_remise`, `new_remise`, `changed_at`)
SELECT
    u.id          AS user_id,
    NULL          AS old_level,
    rl.label      AS new_level,
    NULL          AS old_remise,
    rl.remise_percent AS new_remise,
    NOW()         AS changed_at
FROM `utilisateurs` u
INNER JOIN `reward_levels` rl
    ON rl.min_points = (
        SELECT MAX(r2.min_points)
        FROM `reward_levels` r2
        WHERE r2.min_points <= COALESCE(u.points_total, 0)
    )
WHERE u.role = 'conducteur'
  AND COALESCE(u.points_total, 0) >= 5000       -- only those who reached at least Bronze
  AND NOT EXISTS (
        SELECT 1 FROM `reward_history` rh WHERE rh.user_id = u.id
  );

-- =============================================================================
-- END OF MIGRATION
-- =============================================================================
