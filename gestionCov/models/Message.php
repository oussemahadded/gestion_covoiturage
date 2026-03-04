<?php
/**
 * models/Message.php
 * Modèle Message — Bonus Chat
 */

class Message
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Envoyer un message */
    public function send(int $expediteurId, int $destinataireId, string $contenu, ?int $trajetId = null): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO messages (expediteur_id, destinataire_id, trajet_id, contenu)
             VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$expediteurId, $destinataireId, $trajetId, $contenu]);
    }

    /** Conversation entre deux utilisateurs */
    public function getConversation(int $userId1, int $userId2): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, 
                    e.nom AS exp_nom, e.prenom AS exp_prenom,
                    d.nom AS dest_nom, d.prenom AS dest_prenom
             FROM messages m
             INNER JOIN utilisateurs e ON m.expediteur_id    = e.id
             INNER JOIN utilisateurs d ON m.destinataire_id  = d.id
             WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
                OR (m.expediteur_id = ? AND m.destinataire_id = ?)
             ORDER BY m.created_at ASC'
        );
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        return $stmt->fetchAll();
    }

    /** Liste des contacts avec lesquels l'utilisateur a échangé */
    public function getContacts(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT
                CASE WHEN m.expediteur_id = :uid THEN m.destinataire_id
                     ELSE m.expediteur_id END AS contact_id,
                u.nom, u.prenom,
                (SELECT COUNT(*) FROM messages
                 WHERE destinataire_id = :uid2
                   AND expediteur_id = contact_id
                   AND lu = 0) AS non_lus
             FROM messages m
             INNER JOIN utilisateurs u
               ON u.id = CASE WHEN m.expediteur_id = :uid3 THEN m.destinataire_id ELSE m.expediteur_id END
             WHERE m.expediteur_id = :uid4 OR m.destinataire_id = :uid5
             ORDER BY m.created_at DESC'
        );
        $stmt->execute([
            ':uid'  => $userId, ':uid2' => $userId,
            ':uid3' => $userId, ':uid4' => $userId, ':uid5' => $userId,
        ]);
        return $stmt->fetchAll();
    }

    /** Marquer tous les messages d'un expéditeur comme lus */
    public function markAsRead(int $expediteurId, int $destinataireId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE messages SET lu = 1
             WHERE expediteur_id = ? AND destinataire_id = ? AND lu = 0'
        );
        $stmt->execute([$expediteurId, $destinataireId]);
    }

    /** Nombre de messages non-lus pour un utilisateur */
    public function countUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = 0'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
