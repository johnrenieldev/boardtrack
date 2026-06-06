<?php
/**
 * BoardTrack — Room Model
 * app/models/Room.php
 */

class Room extends Model
{
    protected string $table = 'rooms';

    public function getAllWithOccupancy(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (
            isset($filters['air_conditioned']) &&
            $filters['air_conditioned'] !== '' &&
            $this->hasColumn('air_conditioned')
        ) {
            $where[] = 'r.air_conditioned = :air_conditioned';
            $params[':air_conditioned'] = (int) $filters['air_conditioned'];
        }

        if (!empty($filters['allowed_gender'])) {
            $where[] = 'r.allowed_gender = :allowed_gender';
            $params[':allowed_gender'] = $filters['allowed_gender'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "SELECT r.*,
                       COALESCE(occ.actual_occupants, 0) AS actual_occupants
                FROM {$this->table} r
                LEFT JOIN (
                    SELECT t.room_id, COUNT(*) AS actual_occupants
                    FROM tenants t
                    JOIN users u ON u.id = t.user_id
                    WHERE u.status = 'approved'
                    GROUP BY t.room_id
                ) occ ON occ.room_id = r.id
                {$whereSql}
                ORDER BY r.floor ASC, r.room_number ASC";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function getAvailable(?string $gender = null, ?bool $airConditioned = null): array
    {
        $where = ["r.status = 'available'"];
        $params = [];
        
        if ($gender) {
            $where[] = "(r.allowed_gender = 'any' OR r.allowed_gender = :gender)";
            $params[':gender'] = $gender;
        }

        // Filter by air conditioning preference if specified
        if ($airConditioned !== null && $this->hasColumn('air_conditioned')) {
            $where[] = "r.air_conditioned = :ac";
            $params[':ac'] = $airConditioned ? 1 : 0;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT r.*,
                       COALESCE(occ.actual_occupants, 0) AS actual_occupants
                FROM {$this->table} r
                LEFT JOIN (
                    SELECT t.room_id, COUNT(*) AS actual_occupants
                    FROM tenants t
                    JOIN users u ON u.id = t.user_id
                    WHERE u.status = 'approved'
                    GROUP BY t.room_id
                ) occ ON occ.room_id = r.id
                WHERE {$whereSql}
                  AND (r.max_occupants - COALESCE(occ.actual_occupants, 0)) > 0
                ORDER BY r.floor ASC, r.room_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStatistics(): array
    {
        $sql  = "SELECT
                    COUNT(*) AS total_rooms,
                    SUM(CASE WHEN r.status <> 'maintenance' AND COALESCE(occ.actual_occupants, 0) = 0 THEN 1 ELSE 0 END) AS available,
                    SUM(CASE WHEN r.status <> 'maintenance' AND COALESCE(occ.actual_occupants, 0) > 0 THEN 1 ELSE 0 END) AS occupied,
                    SUM(r.status='maintenance') AS maintenance,
                    SUM(r.allowed_gender='male') AS male_only,
                    SUM(r.allowed_gender='female') AS female_only,
                    SUM(r.allowed_gender='any') AS mixed_any,
                    AVG(r.monthly_rent) AS avg_rent,
                    SUM(COALESCE(occ.actual_occupants, 0)) AS total_occupied
                 FROM {$this->table} r
                 LEFT JOIN (
                    SELECT t.room_id, COUNT(*) AS actual_occupants
                    FROM tenants t
                    JOIN users u ON u.id = t.user_id
                    WHERE u.status = 'approved'
                    GROUP BY t.room_id
                 ) occ ON occ.room_id = r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }

    /**
     * Rooms with at least one approved tenant (for billing)
     */
    public function getBillableRooms(): array
    {
        $sql = "SELECT r.*,
                       COUNT(t.id) AS occupant_count,
                       GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') AS tenant_names
                FROM {$this->table} r
                INNER JOIN tenants t ON t.room_id = r.id
                INNER JOIN users u ON t.user_id = u.id AND u.status = 'approved'
                GROUP BY r.id
                ORDER BY r.floor ASC, r.room_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateOccupancy(int $roomId): void
    {
        $sql  = "SELECT r.max_occupants, r.status, COUNT(u.id) AS cnt
                 FROM {$this->table} r
                 LEFT JOIN tenants t ON t.room_id = r.id
                 LEFT JOIN users u ON u.id = t.user_id AND u.status = 'approved'
                 WHERE r.id = :id GROUP BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $roomId]);
        $row  = $stmt->fetch();
        if (!$row) return;
        if (($row['status'] ?? '') === 'maintenance') {
            return;
        }

        $status = ((int)$row['cnt'] >= (int)$row['max_occupants']) ? 'occupied' : 'available';
        $this->update(['status' => $status], ['id' => $roomId]);
    }
}
