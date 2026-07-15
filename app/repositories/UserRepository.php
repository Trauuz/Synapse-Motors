<?php

declare(strict_types=1);

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => strtolower(trim($email))]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(string $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findAdminByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM users WHERE role = 'Admin' AND email = :email LIMIT 1"
        );
        $statement->execute(['email' => strtolower(trim($email))]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $userId = $attributes['id'] ?? synapse_uuid();
        $now = gmdate('Y-m-d H:i:s');

        $statement = $this->pdo->prepare(
            'INSERT INTO users (
                id, name, email, password_hash, role, address, contact_no,
                email_verified_at, access_status, invited_by_user_id, invited_at,
                last_seen_at, created_at, updated_at
            ) VALUES (
                :id, :name, :email, :password_hash, :role, :address, :contact_no,
                :email_verified_at, :access_status, :invited_by_user_id, :invited_at,
                :last_seen_at, :created_at, :updated_at
            )'
        );

        $statement->execute([
            'id' => $userId,
            'name' => (string) ($attributes['name'] ?? ''),
            'email' => strtolower(trim((string) ($attributes['email'] ?? ''))),
            'password_hash' => $attributes['password_hash'] ?? null,
            'role' => (string) ($attributes['role'] ?? 'Buyer'),
            'address' => (string) ($attributes['address'] ?? ''),
            'contact_no' => (string) ($attributes['contact_no'] ?? ''),
            'email_verified_at' => $attributes['email_verified_at'] ?? null,
            'access_status' => (string) ($attributes['access_status'] ?? 'active'),
            'invited_by_user_id' => $attributes['invited_by_user_id'] ?? null,
            'invited_at' => $attributes['invited_at'] ?? null,
            'last_seen_at' => $attributes['last_seen_at'] ?? null,
            'created_at' => $attributes['created_at'] ?? $now,
            'updated_at' => $attributes['updated_at'] ?? $now,
        ]);

        return $this->findById((string) $userId) ?? throw new RuntimeException('Unable to reload user record.');
    }

    /**
     * @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    public function update(string $userId, array $changes): array
    {
        $current = $this->findById($userId);

        if (!is_array($current)) {
            throw new RuntimeException('User record not found.');
        }

        $next = array_merge($current, $changes, ['updated_at' => gmdate('Y-m-d H:i:s')]);
        $statement = $this->pdo->prepare(
            'UPDATE users SET
                name = :name,
                email = :email,
                password_hash = :password_hash,
                role = :role,
                address = :address,
                contact_no = :contact_no,
                email_verified_at = :email_verified_at,
                access_status = :access_status,
                invited_by_user_id = :invited_by_user_id,
                invited_at = :invited_at,
                last_seen_at = :last_seen_at,
                updated_at = :updated_at
            WHERE id = :id'
        );

        $statement->execute([
            'id' => $userId,
            'name' => (string) ($next['name'] ?? ''),
            'email' => strtolower(trim((string) ($next['email'] ?? ''))),
            'password_hash' => $next['password_hash'] ?? null,
            'role' => (string) ($next['role'] ?? 'Buyer'),
            'address' => (string) ($next['address'] ?? ''),
            'contact_no' => (string) ($next['contact_no'] ?? ''),
            'email_verified_at' => $next['email_verified_at'] ?? null,
            'access_status' => (string) ($next['access_status'] ?? 'active'),
            'invited_by_user_id' => $next['invited_by_user_id'] ?? null,
            'invited_at' => $next['invited_at'] ?? null,
            'last_seen_at' => $next['last_seen_at'] ?? null,
            'updated_at' => (string) $next['updated_at'],
        ]);

        return $this->findById($userId) ?? throw new RuntimeException('Unable to reload user record.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allAdmins(): array
    {
        $statement = $this->pdo->query("SELECT * FROM users WHERE role = 'Admin' ORDER BY name ASC");
        $admins = $statement->fetchAll();

        return is_array($admins) ? $admins : [];
    }

    public function countAdmins(): int
    {
        $statement = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Admin'");

        return (int) $statement->fetchColumn();
    }

    public function countAdminsByAccessStatus(string $accessStatus): int
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE role = 'Admin' AND access_status = :access_status"
        );
        $statement->execute(['access_status' => $accessStatus]);

        return (int) $statement->fetchColumn();
    }

    public function countAdminsPendingVerification(): int
    {
        $statement = $this->pdo->query(
            "SELECT COUNT(*) FROM users WHERE role = 'Admin' AND email_verified_at IS NULL"
        );

        return (int) $statement->fetchColumn();
    }
}
