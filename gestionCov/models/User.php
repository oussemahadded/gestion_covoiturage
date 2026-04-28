<?php
/**
 * models/User.php
 * Modèle Utilisateur — accès aux données PDO
 */

class User
{
    private PDO $pdo;
    private ?bool $hasStatutCompteColumnCache = null;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Lecture ──────────────────────────────────────────────────────────────

    /** Retourne un utilisateur par son email */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /** Retourne un utilisateur par son id */
    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Retourne tous les utilisateurs (admin) */
    public function getAll(): array
    {
        $sql = $this->hasStatutCompteColumn()
            ? 'SELECT id, nom, prenom, email, telephone, role, statut_compte, created_at
               FROM utilisateurs
               ORDER BY created_at DESC'
            : 'SELECT id, nom, prenom, email, telephone, role, \'actif\' AS statut_compte, created_at
               FROM utilisateurs
               ORDER BY created_at DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    /** Compte total des utilisateurs */
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
    }

    // ── Écriture ─────────────────────────────────────────────────────────────

    /**
     * Inscrit un nouvel utilisateur
     * @return int|false l'id inséré ou false
     */
    public function create(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $telephone,
        string $role,
        string $statutCompte = 'actif'
    ): int|false
    {
        // Hachage sécurisé du mot de passe
        $hash = password_hash($motDePasse, PASSWORD_BCRYPT);

        if ($this->hasStatutCompteColumn()) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut_compte)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $nom,
                $prenom,
                $email,
                $hash,
                $telephone,
                $role,
                $this->normalizeAccountStatus($statutCompte)
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$nom, $prenom, $email, $hash, $telephone, $role]);
        }

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Supprime un utilisateur par son id.
     * Note: la suppression physique n'est plus utilisée depuis l'UI admin
     * afin de préserver l'historique (trajets, réservations, avis, messages).
     */
    public function deleteById(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /** Vérifie si un email est déjà pris */
    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Moyenne des notes d'un conducteur */
    public function getAverageRating(int $conducteurId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT AVG(a.note) as moyenne
             FROM avis a
             INNER JOIN trajets t ON a.trajet_id = t.id
             WHERE t.conducteur_id = ?'
        );
        $stmt->execute([$conducteurId]);
        $result = $stmt->fetchColumn();
        return $result ? round((float) $result, 1) : 0.0;
    }

    public function updateAccountStatus(int $id, string $status): bool
    {
        if (!$this->hasStatutCompteColumn()) {
            return false;
        }

        if (!in_array($status, ['actif', 'en_attente', 'refuse', 'desactive'], true)) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE utilisateurs SET statut_compte = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    private function hasStatutCompteColumn(): bool
    {
        if ($this->hasStatutCompteColumnCache !== null) {
            return $this->hasStatutCompteColumnCache;
        }

        $stmt = $this->pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'statut_compte'");
        $this->hasStatutCompteColumnCache = (bool) $stmt->fetch();
        return $this->hasStatutCompteColumnCache;
    }

    private function normalizeAccountStatus(string $status): string
    {
        $allowed = ['actif', 'en_attente', 'refuse', 'desactive'];
        return in_array($status, $allowed, true) ? $status : 'actif';
    }
}
