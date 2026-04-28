<?php
/**
 * config/database.php
 * Connexion PDO — Singleton Pattern
 * Gestion de Covoiturage — PFA
 */

class Database
{
    // ── Paramètres de connexion ──────────────────────────────────────────────
    private static string $host     = 'localhost';
    private static string $dbname   = 'gestion_cov';
    private static string $username = 'root';
    private static string $password = '';
    private static string $charset  = 'utf8mb4';

    // ── Instance unique (Singleton) ──────────────────────────────────────────
    private static ?PDO $instance = null;

    /** Empêche l'instanciation directe */
    private function __construct() {}

    /** Empêche le clonage */
    private function __clone() {}

    /**
     * Retourne l'unique instance PDO.
     * Crée la connexion à la première demande.
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$dbname,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lance des exceptions sur erreur
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Retourne des tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                    // Vraies requêtes préparées
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // En production, ne jamais afficher les détails de l'erreur
                error_log('[DB ERROR] ' . $e->getMessage());
                die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
            }
        }

        return self::$instance;
    }
}
