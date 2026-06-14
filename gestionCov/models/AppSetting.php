<?php
/**
 * models/AppSetting.php
 * Model for application settings stored in the database.
 * Points system: prix_par_km now represents points per km (default 250).
 */

class AppSetting
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Returns the points-per-km rate.
     * Auto-migrates the DB value from the old monetary system (≤ 10) to 250 pts/km.
     */
    public function getPrixParKm(): float
    {
        $stmt = $this->pdo->prepare('SELECT setting_value FROM app_settings WHERE setting_key = "prix_par_km" LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row !== false && is_numeric($row['setting_value'])) {
            $value = (float) $row['setting_value'];
            // Auto-migrate: old monetary values (≤ 10 TND/km) → new points default
            if ($value <= 10.0) {
                $this->updatePrixParKm(250.0);
                return 250.0;
            }
            return $value;
        }

        return defined('DEFAULT_PRIX_PAR_KM') ? (float) DEFAULT_PRIX_PAR_KM : 250.0;
    }

    /**
     * Updates the points-per-km setting.
     * Value must be a positive number (e.g. 250 = 250 pts/km).
     */
    public function updatePrixParKm(float $value): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES ("prix_par_km", ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ');
        $formattedValue = number_format($value, 3, '.', '');
        return $stmt->execute([$formattedValue, $formattedValue]);
    }

    /**
     * Generic getter for any setting key with a default fallback.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $stmt = $this->pdo->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row['setting_value'] : $default;
    }
}
