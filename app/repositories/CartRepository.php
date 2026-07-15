<?php

declare(strict_types=1);

final class CartRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<string, int>
     */
    public function quantitiesForUser(string $userId): array
    {
        $statement = $this->pdo->prepare('SELECT vehicle_id, quantity FROM cart_items WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll();
        $quantities = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $quantities[(string) $row['vehicle_id']] = (int) $row['quantity'];
        }

        return $quantities;
    }

    public function setQuantity(string $userId, string $vehicleId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($userId, $vehicleId);
            return;
        }

        $existing = $this->quantityForVehicle($userId, $vehicleId);
        $now = gmdate('Y-m-d H:i:s');

        if ($existing === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO cart_items (id, user_id, vehicle_id, quantity, created_at, updated_at)
                 VALUES (:id, :user_id, :vehicle_id, :quantity, :created_at, :updated_at)'
            );
            $statement->execute([
                'id' => synapse_uuid(),
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
                'quantity' => $quantity,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            return;
        }

        $statement = $this->pdo->prepare(
            'UPDATE cart_items SET quantity = :quantity, updated_at = :updated_at
             WHERE user_id = :user_id AND vehicle_id = :vehicle_id'
        );
        $statement->execute([
            'quantity' => $quantity,
            'updated_at' => $now,
            'user_id' => $userId,
            'vehicle_id' => $vehicleId,
        ]);
    }

    public function remove(string $userId, string $vehicleId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM cart_items WHERE user_id = :user_id AND vehicle_id = :vehicle_id');
        $statement->execute([
            'user_id' => $userId,
            'vehicle_id' => $vehicleId,
        ]);
    }

    public function clearForUser(string $userId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM cart_items WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
    }

    private function quantityForVehicle(string $userId, string $vehicleId): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT quantity FROM cart_items WHERE user_id = :user_id AND vehicle_id = :vehicle_id LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'vehicle_id' => $vehicleId,
        ]);
        $value = $statement->fetchColumn();

        return $value === false ? null : (int) $value;
    }
}
