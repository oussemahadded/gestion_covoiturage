<?php
/**
 * models/AppSetting.php
 * Model for application settings stored in the database.
 */

class AppSetting
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function getPrixParKm(): float
    {
        $stmt = $this->pdo->prepare('SELECT setting_value FROM app_settings WHERE setting_key = "prix_par_km" LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row !== false && is_numeric($row['setting_value'])) {
            return (float) $row['setting_value'];
        }

        return defined('DEFAULT_PRIX_PAR_KM') ? (float) DEFAULT_PRIX_PAR_KM : 1.000;
    }

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
}
