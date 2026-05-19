<?php

require_once __DIR__ . '/Database.php';

final class UavRepository
{
    private const ALLOWED_STATUS = ['Müsait', 'Uçuşta', 'Bakımda', 'Arızalı'];

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getPdo();
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT IhaID as id, Ad as name, Model as model, Seri_No as serial_number, Durum as status
             FROM IHA ORDER BY IhaID DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT IhaID as id, Ad as name, Model as model, Seri_No as serial_number, Durum as status
             FROM IHA WHERE IhaID = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function create(string $name, string $model, string $serialNumber, string $status = 'Müsait'): int
    {
        $status = $this->normalizeStatus($status);
        $stmt = $this->pdo->prepare(
            'INSERT INTO IHA (Ad, Model, Seri_No, Durum) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $model, $serialNumber, $status]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $fields): bool
    {
        $allowed = ['Ad', 'Model', 'Seri_No', 'Durum'];
        $sets = [];
        $params = [];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $fields)) {
                continue;
            }
            if ($col === 'Durum') {
                $params[] = $this->normalizeStatus((string) $fields[$col]);
            } else {
                $params[] = (string) $fields[$col];
            }
            $sets[] = $col . ' = ?';
        }

        if ($sets === []) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE IHA SET ' . implode(', ', $sets) . ' WHERE IhaID = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM IHA WHERE IhaID = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    private function normalizeStatus(string $status): string
    {
        // Capitalize correctly for Enum
        $s = mb_convert_case(trim($status), MB_CASE_TITLE, "UTF-8");
        if (!in_array($s, self::ALLOWED_STATUS, true)) {
            return 'Müsait';
        }
        return $s;
    }
}
