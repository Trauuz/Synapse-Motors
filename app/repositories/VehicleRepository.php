<?php

declare(strict_types=1);

final class VehicleRepository
{
    public function __construct(private PDO $pdo)
    {
        $this->ensureSchema();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM vehicles ORDER BY created_at ASC, id ASC');
        $vehicles = $statement->fetchAll();

        if (!is_array($vehicles)) {
            return [];
        }

        return array_map([$this, 'hydrateVehicle'], $vehicles);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(string $vehicleId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM vehicles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $vehicleId]);
        $vehicle = $statement->fetch();

        return is_array($vehicle) ? $this->hydrateVehicle($vehicle) : null;
    }

    public function countAll(): int
    {
        $statement = $this->pdo->query('SELECT COUNT(*) FROM vehicles');

        return (int) $statement->fetchColumn();
    }

    /**
     * @param array<int, array<string, mixed>> $vehicles
     */
    public function replaceCatalog(array $vehicles): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec('DELETE FROM vehicles');

            foreach ($vehicles as $vehicle) {
                $this->insertVehicle($vehicle);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $this->insertVehicle($attributes);

        return $this->findById((string) ($attributes['id'] ?? '')) ?? throw new RuntimeException('Unable to reload vehicle record.');
    }

    /**
     * @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    public function update(string $vehicleId, array $changes): array
    {
        $current = $this->findById($vehicleId);

        if (!is_array($current)) {
            throw new RuntimeException('Vehicle record not found.');
        }

        $next = array_merge($current, $changes, ['updated_at' => gmdate('Y-m-d H:i:s')]);
        $statement = $this->pdo->prepare(
            'UPDATE vehicles SET
                name = :name,
                detail = :detail,
                price = :price,
                category = :category,
                image = :image,
                width = :width,
                height = :height,
                alt = :alt,
                body = :body,
                powertrain = :powertrain,
                drive = :drive,
                availability = :availability,
                collection = :collection,
                stock_quantity = :stock_quantity,
                updated_at = :updated_at
            WHERE id = :id'
        );

        $statement->execute([
            'id' => $vehicleId,
            'name' => (string) ($next['name'] ?? ''),
            'detail' => (string) ($next['detail'] ?? ''),
            'price' => (int) ($next['price'] ?? 0),
            'category' => (string) ($next['category'] ?? ''),
            'image' => (string) ($next['image'] ?? ''),
            'width' => (int) ($next['width'] ?? 0),
            'height' => (int) ($next['height'] ?? 0),
            'alt' => (string) ($next['alt'] ?? ''),
            'body' => (string) ($next['body'] ?? ''),
            'powertrain' => (string) ($next['powertrain'] ?? ''),
            'drive' => (string) ($next['drive'] ?? ''),
            'availability' => (string) ($next['availability'] ?? ''),
            'collection' => (string) ($next['collection'] ?? ''),
            'stock_quantity' => (int) ($next['stock_quantity'] ?? 0),
            'updated_at' => (string) $next['updated_at'],
        ]);

        return $this->findById($vehicleId) ?? throw new RuntimeException('Unable to reload vehicle record.');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function delete(string $vehicleId): ?array
    {
        $vehicle = $this->findById($vehicleId);

        if (!is_array($vehicle)) {
            return null;
        }

        $statement = $this->pdo->prepare('DELETE FROM vehicles WHERE id = :id');
        $statement->execute(['id' => $vehicleId]);

        return $vehicle;
    }

    /**
     * @param array<string, mixed> $vehicle
     */
    private function insertVehicle(array $vehicle): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $record = array_merge([
            'created_at' => $now,
            'updated_at' => $now,
        ], $vehicle);

        $statement = $this->pdo->prepare(
            'INSERT INTO vehicles (
                id, name, detail, price, category, image, width, height, alt, body,
                powertrain, drive, availability, collection, stock_quantity, created_at, updated_at
            ) VALUES (
                :id, :name, :detail, :price, :category, :image, :width, :height, :alt, :body,
                :powertrain, :drive, :availability, :collection, :stock_quantity, :created_at, :updated_at
            )'
        );

        $statement->execute($this->vehicleParameters($record));
    }

    /**
     * @param array<string, mixed> $vehicle
     * @return array<string, mixed>
     */
    private function vehicleParameters(array $vehicle): array
    {
        return [
            'id' => (string) ($vehicle['id'] ?? ''),
            'name' => (string) ($vehicle['name'] ?? ''),
            'detail' => (string) ($vehicle['detail'] ?? ''),
            'price' => (int) ($vehicle['price'] ?? 0),
            'category' => (string) ($vehicle['category'] ?? ''),
            'image' => (string) ($vehicle['image'] ?? ''),
            'width' => (int) ($vehicle['width'] ?? 0),
            'height' => (int) ($vehicle['height'] ?? 0),
            'alt' => (string) ($vehicle['alt'] ?? ''),
            'body' => (string) ($vehicle['body'] ?? ''),
            'powertrain' => (string) ($vehicle['powertrain'] ?? ''),
            'drive' => (string) ($vehicle['drive'] ?? ''),
            'availability' => (string) ($vehicle['availability'] ?? ''),
            'collection' => (string) ($vehicle['collection'] ?? ''),
            'stock_quantity' => (int) ($vehicle['stock_quantity'] ?? 0),
            'created_at' => (string) ($vehicle['created_at'] ?? gmdate('Y-m-d H:i:s')),
            'updated_at' => (string) ($vehicle['updated_at'] ?? gmdate('Y-m-d H:i:s')),
        ];
    }

    private function ensureSchema(): void
    {
        $driverName = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driverName === 'sqlite') {
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS vehicles (
                    id TEXT PRIMARY KEY,
                    name TEXT NOT NULL,
                    detail TEXT NOT NULL,
                    price INTEGER NOT NULL,
                    category TEXT NOT NULL,
                    image TEXT NOT NULL,
                    width INTEGER NOT NULL,
                    height INTEGER NOT NULL,
                    alt TEXT NOT NULL,
                    body TEXT NOT NULL,
                    powertrain TEXT NOT NULL,
                    drive TEXT NOT NULL,
                    availability TEXT NOT NULL,
                    collection TEXT NOT NULL,
                    stock_quantity INTEGER NOT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                )'
            );

            return;
        }

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS vehicles (
                id VARCHAR(120) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                detail VARCHAR(255) NOT NULL,
                price INT NOT NULL,
                category VARCHAR(255) NOT NULL,
                image VARCHAR(255) NOT NULL,
                width INT NOT NULL,
                height INT NOT NULL,
                alt VARCHAR(255) NOT NULL,
                body VARCHAR(120) NOT NULL,
                powertrain VARCHAR(120) NOT NULL,
                drive VARCHAR(120) NOT NULL,
                availability VARCHAR(120) NOT NULL,
                collection VARCHAR(120) NOT NULL,
                stock_quantity INT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            )'
        );
    }

    /**
     * @param array<string, mixed> $vehicle
     * @return array<string, mixed>
     */
    private function hydrateVehicle(array $vehicle): array
    {
        $vehicle['price'] = (int) ($vehicle['price'] ?? 0);
        $vehicle['width'] = (int) ($vehicle['width'] ?? 0);
        $vehicle['height'] = (int) ($vehicle['height'] ?? 0);
        $vehicle['stock_quantity'] = (int) ($vehicle['stock_quantity'] ?? 0);

        return $vehicle;
    }
}
