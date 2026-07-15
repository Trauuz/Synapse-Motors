<?php

declare(strict_types=1);

final class EmailVerificationTokenRepository
{
    public function __construct(private PDO $pdo)
    {
        $this->ensureSchema();
    }

    public function create(string $userId, string $tokenHash, string $expiresAt): void
    {
        $tokenId = synapse_uuid();
        $now = gmdate('Y-m-d H:i:s');

        $statement = $this->pdo->prepare(
            'INSERT INTO email_verification_tokens (id, user_id, token_hash, expires_at, consumed_at, created_at)
             VALUES (:id, :user_id, :token_hash, :expires_at, NULL, :created_at)'
        );

        $statement->execute([
            'id' => $tokenId,
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => $now,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findActiveByTokenHash(string $tokenHash): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM email_verification_tokens
             WHERE token_hash = :token_hash
               AND consumed_at IS NULL
               AND expires_at >= :now
             ORDER BY created_at DESC
             LIMIT 1'
        );

        $statement->execute([
            'token_hash' => $tokenHash,
            'now' => gmdate('Y-m-d H:i:s'),
        ]);

        $token = $statement->fetch();

        return is_array($token) ? $token : null;
    }

    public function consumeForUser(string $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE email_verification_tokens
             SET consumed_at = :consumed_at
             WHERE user_id = :user_id
               AND consumed_at IS NULL'
        );

        $statement->execute([
            'consumed_at' => gmdate('Y-m-d H:i:s'),
            'user_id' => $userId,
        ]);
    }

    public function deleteUnconsumedForUser(string $userId): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM email_verification_tokens
             WHERE user_id = :user_id
               AND consumed_at IS NULL'
        );

        $statement->execute(['user_id' => $userId]);
    }

    private function ensureSchema(): void
    {
        $driverName = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driverName === 'sqlite') {
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS email_verification_tokens (
                    id TEXT PRIMARY KEY,
                    user_id TEXT NOT NULL,
                    token_hash TEXT NOT NULL UNIQUE,
                    expires_at TEXT NOT NULL,
                    consumed_at TEXT NULL,
                    created_at TEXT NOT NULL
                )'
            );
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_email_verification_tokens_user_id ON email_verification_tokens (user_id)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_email_verification_tokens_expires_at ON email_verification_tokens (expires_at)');

            return;
        }

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS email_verification_tokens (
                id CHAR(36) PRIMARY KEY,
                user_id CHAR(36) NOT NULL,
                token_hash CHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                consumed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_email_verification_tokens_user_id (user_id),
                INDEX idx_email_verification_tokens_expires_at (expires_at)
            )'
        );
    }
}
