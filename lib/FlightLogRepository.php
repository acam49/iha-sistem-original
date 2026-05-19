<?php

require_once __DIR__ . '/Database.php';

final class FlightLogRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getPdo();
    }

    public function listByUavForUser(int $uavId, int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT LogID as id, IhaID as uav_id, Baslangic_Saati as started_at, Bitis_Saati as ended_at, Hava_Durumu as notes
             FROM UCUS_LOGU
             WHERE IhaID = ? AND PilotID = ?
             ORDER BY Baslangic_Saati DESC, LogID DESC'
        );
        $stmt->execute([$uavId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT LogID as id, IhaID as uav_id, Baslangic_Saati as started_at, Bitis_Saati as ended_at, Hava_Durumu as notes
             FROM UCUS_LOGU
             WHERE LogID = ? AND PilotID = ? LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function create(int $uavId, int $userId, string $startedAt, string $endedAt, ?string $notes): ?int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO UCUS_LOGU (IhaID, PilotID, Baslangic_Saati, Bitis_Saati, Hava_Durumu) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$uavId, $userId, $startedAt, $endedAt, $notes]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $fields): bool
    {
        $existing = $this->findByIdForUser($id, $userId);
        if ($existing === null) {
            return false;
        }

        $sets = [];
        $params = [];
        
        if (array_key_exists('started_at', $fields)) {
            $sets[] = 'Baslangic_Saati = ?';
            $params[] = $fields['started_at'];
        }
        if (array_key_exists('ended_at', $fields)) {
            $sets[] = 'Bitis_Saati = ?';
            $params[] = $fields['ended_at'];
        }
        if (array_key_exists('notes', $fields)) {
            $sets[] = 'Hava_Durumu = ?';
            $params[] = $fields['notes'] === '' ? null : $fields['notes'];
        }
        
        if ($sets === []) {
            return false;
        }
        $params[] = $id;
        $params[] = $userId;
        $sql = 'UPDATE UCUS_LOGU SET ' . implode(', ', $sets) . ' WHERE LogID = ? AND PilotID = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM UCUS_LOGU WHERE LogID = ? AND PilotID = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}
