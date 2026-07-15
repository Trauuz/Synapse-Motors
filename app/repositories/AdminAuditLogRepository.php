<?php

declare(strict_types=1);

final class AdminAuditLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param array<string, mixed> $entry
     */
    public function create(array $entry): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_audit_logs (
                id, actor_user_id, actor_name, actor_email, action, summary, occurred_at
            ) VALUES (
                :id, :actor_user_id, :actor_name, :actor_email, :action, :summary, :occurred_at
            )'
        );

        $statement->execute([
            'id' => $entry['id'] ?? 'audit-' . bin2hex(random_bytes(6)),
            'actor_user_id' => (string) ($entry['actor_user_id'] ?? ''),
            'actor_name' => (string) ($entry['actor_name'] ?? ''),
            'actor_email' => (string) ($entry['actor_email'] ?? ''),
            'action' => (string) ($entry['action'] ?? ''),
            'summary' => (string) ($entry['summary'] ?? ''),
            'occurred_at' => (string) ($entry['occurred_at'] ?? gmdate('Y-m-d H:i:s')),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forActor(string $actorUserId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM admin_audit_logs WHERE actor_user_id = :actor_user_id ORDER BY occurred_at DESC, id DESC'
        );
        $statement->execute(['actor_user_id' => $actorUserId]);
        $entries = $statement->fetchAll();

        return is_array($entries) ? $entries : [];
    }

    public function countForActor(string $actorUserId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM admin_audit_logs WHERE actor_user_id = :actor_user_id'
        );
        $statement->execute(['actor_user_id' => $actorUserId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forActorPage(string $actorUserId, int $limit, int $offset): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM admin_audit_logs
            WHERE actor_user_id = :actor_user_id
            ORDER BY occurred_at DESC, id DESC
            LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':actor_user_id', $actorUserId, PDO::PARAM_STR);
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();
        $entries = $statement->fetchAll();

        return is_array($entries) ? $entries : [];
    }
}
