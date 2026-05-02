<?php
/**
 * BoardTrack — Room Model
 * app/model/Room.php
 */

class Room extends Model
{
    protected string $table = 'rooms';

    public function getAllWithOccupancy(): array
    {
        $sql = "SELECT r.*,
                       COUNT(t.id) AS actual_occupants
                FROM {$this->table} r
                LEFT JOIN tenants t ON t.room_id = r.id
                          AND EXISTS (SELECT 1 FROM users u WHERE u.id = t.user_id AND u.status = 'active')
                GROUP BY r.id
                ORDER BY r.floor ASC, r.room_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAvailable(): array
    {
        $sql = "SELECT r.*,
                       COUNT(t.id) AS actual_occupants
                FROM {$this->table} r
                LEFT JOIN tenants t ON t.room_id = r.id
                GROUP BY r.id
                HAVING r.status = 'available' AND (r.max_occupants - COUNT(t.id)) > 0
                ORDER BY r.floor ASC, r.room_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStatistics(): array
    {
        $sql  = "SELECT
                    COUNT(*) AS total_rooms,
                    SUM(status='available') AS available,
                    SUM(status='occupied')  AS occupied,
                    SUM(status='maintenance') AS maintenance,
                    AVG(monthly_rent) AS avg_rent,
                    SUM(status='occupied') AS total_occupied
                 FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }

    public function updateOccupancy(int $roomId): void
    {
        $sql  = "SELECT r.max_occupants, COUNT(t.id) AS cnt
                 FROM {$this->table} r
                 LEFT JOIN tenants t ON t.room_id = r.id
                 WHERE r.id = :id GROUP BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $roomId]);
        $row  = $stmt->fetch();
        if (!$row) return;
        $status = ($row['cnt'] >= $row['max_occupants']) ? 'occupied' : 'available';
        $this->update(['status' => $status], ['id' => $roomId]);
    }
}