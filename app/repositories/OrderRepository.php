<?php

declare(strict_types=1);

final class OrderRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param array<string, mixed> $draft
     * @return array<string, mixed>
     */
    public function createDraft(string $userId, array $draft): array
    {
        $orderId = synapse_uuid();
        $now = gmdate('Y-m-d H:i:s');
        $customer = is_array($draft['customer'] ?? null) ? $draft['customer'] : [];
        $lineItems = is_array($draft['line_items'] ?? null) ? $draft['line_items'] : [];

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO orders (
                    id, user_id, customer_name, customer_email, contact_number, notes,
                    total_php, payment_method, payment_method_label, payment_reference,
                    status, created_at, updated_at, submitted_at
                ) VALUES (
                    :id, :user_id, :customer_name, :customer_email, :contact_number, :notes,
                    :total_php, NULL, NULL, NULL,
                    :status, :created_at, :updated_at, NULL
                )'
            );

            $statement->execute([
                'id' => $orderId,
                'user_id' => $userId,
                'customer_name' => (string) ($customer['name'] ?? ''),
                'customer_email' => (string) ($customer['email'] ?? ''),
                'contact_number' => (string) ($customer['contact_number'] ?? ''),
                'notes' => (string) ($draft['notes'] ?? ''),
                'total_php' => (int) ($draft['total_php'] ?? 0),
                'status' => 'draft',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $itemStatement = $this->pdo->prepare(
                'INSERT INTO order_items (
                    id, order_id, vehicle_id, vehicle_name, vehicle_detail, unit_price_php,
                    quantity, line_total_php, created_at
                ) VALUES (
                    :id, :order_id, :vehicle_id, :vehicle_name, :vehicle_detail, :unit_price_php,
                    :quantity, :line_total_php, :created_at
                )'
            );

            foreach ($lineItems as $lineItem) {
                if (!is_array($lineItem)) {
                    continue;
                }

                $vehicle = is_array($lineItem['vehicle'] ?? null) ? $lineItem['vehicle'] : [];
                $quantity = (int) ($lineItem['quantity'] ?? 0);
                $lineTotalPhp = (int) ($lineItem['line_total_php'] ?? 0);
                $unitPricePhp = $quantity > 0 ? (int) round($lineTotalPhp / $quantity) : 0;

                $itemStatement->execute([
                    'id' => synapse_uuid(),
                    'order_id' => $orderId,
                    'vehicle_id' => (string) ($vehicle['id'] ?? ''),
                    'vehicle_name' => (string) ($vehicle['name'] ?? ''),
                    'vehicle_detail' => (string) ($vehicle['detail'] ?? ''),
                    'unit_price_php' => $unitPricePhp,
                    'quantity' => $quantity,
                    'line_total_php' => $lineTotalPhp,
                    'created_at' => $now,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->findById($orderId) ?? throw new RuntimeException('Unable to reload draft order.');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(string $orderId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $orderId]);
        $order = $statement->fetch();

        if (!is_array($order)) {
            return null;
        }

        $order['line_items'] = $this->lineItemsForOrder($orderId);

        return $order;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDraftForUser(string $userId, string $orderId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM orders WHERE id = :id AND user_id = :user_id AND status = 'draft' LIMIT 1"
        );
        $statement->execute([
            'id' => $orderId,
            'user_id' => $userId,
        ]);
        $order = $statement->fetch();

        if (!is_array($order)) {
            return null;
        }

        $order['line_items'] = $this->lineItemsForOrder($orderId);

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    public function submitDraft(string $orderId, string $paymentMethod, string $paymentMethodLabel, string $reference): array
    {
        $submittedAt = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            "UPDATE orders
             SET payment_method = :payment_method,
                 payment_method_label = :payment_method_label,
                 payment_reference = :payment_reference,
                 status = 'submitted',
                 updated_at = :updated_at,
                 submitted_at = :submitted_at
             WHERE id = :id"
        );
        $statement->execute([
            'payment_method' => $paymentMethod,
            'payment_method_label' => $paymentMethodLabel,
            'payment_reference' => $reference,
            'updated_at' => $submittedAt,
            'submitted_at' => $submittedAt,
            'id' => $orderId,
        ]);

        return $this->findById($orderId) ?? throw new RuntimeException('Unable to reload submitted order.');
    }

    public function deleteDraft(string $orderId): void
    {
        $statement = $this->pdo->prepare("DELETE FROM orders WHERE id = :id AND status = 'draft'");
        $statement->execute(['id' => $orderId]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lineItemsForOrder(string $orderId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT vehicle_id, vehicle_name, vehicle_detail, unit_price_php, quantity, line_total_php
             FROM order_items WHERE order_id = :order_id ORDER BY created_at ASC, id ASC'
        );
        $statement->execute(['order_id' => $orderId]);
        $rows = $statement->fetchAll();
        $lineItems = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $vehicleId = (string) ($row['vehicle_id'] ?? '');
            $vehicle = find_vehicle_by_id($vehicleId);

            if ($vehicle === null) {
                $vehicle = [
                    'id' => $vehicleId,
                    'name' => (string) ($row['vehicle_name'] ?? ''),
                    'detail' => (string) ($row['vehicle_detail'] ?? ''),
                ];
            }

            $lineItems[] = [
                'vehicle' => $vehicle,
                'quantity' => (int) ($row['quantity'] ?? 0),
                'line_total_php' => (int) ($row['line_total_php'] ?? 0),
            ];
        }

        return $lineItems;
    }
}
