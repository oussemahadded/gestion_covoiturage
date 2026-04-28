<?php
/**
 * models/AuditLog.php
 * Journal d'audit pour la traçabilité admin.
 */

class AuditLog
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function create(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $details = []
    ): bool {
        $detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE);
        if ($detailsJson === false) {
            $detailsJson = '{}';
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userAgent = $userAgent !== '' ? substr($userAgent, 0, 255) : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $detailsJson,
            $ipAddress,
            $userAgent,
        ]);
    }

    public function getRecent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));

        $stmt = $this->pdo->prepare(
            'SELECT a.*,
                    u.nom AS user_nom,
                    u.prenom AS user_prenom,
                    u.email AS user_email,
                    u.role AS user_role
             FROM audit_logs a
             LEFT JOIN utilisateurs u ON a.user_id = u.id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT a.*,
                       u.nom AS user_nom,
                       u.prenom AS user_prenom,
                       u.email AS user_email,
                       u.role AS user_role
                FROM audit_logs a
                LEFT JOIN utilisateurs u ON a.user_id = u.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= ' AND a.action = :action';
            $params[':action'] = (string) $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $sql .= ' AND a.entity_type = :entity_type';
            $params[':entity_type'] = (string) $filters['entity_type'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= ' AND a.user_id = :user_id';
            $params[':user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND a.created_at >= :date_from';
            $params[':date_from'] = (string) $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND a.created_at <= :date_to';
            $params[':date_to'] = (string) $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['query'])) {
            $sql .= ' AND (
                u.email LIKE :query
                OR CONCAT(COALESCE(u.prenom, ""), " ", COALESCE(u.nom, "")) LIKE :query
                OR a.details LIKE :query
            )';
            $params[':query'] = '%' . (string) $filters['query'] . '%';
        }

        $sql .= ' ORDER BY a.created_at DESC, a.id DESC';

        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 100;
        $limit = max(1, min(500, $limit));
        $sql .= ' LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

