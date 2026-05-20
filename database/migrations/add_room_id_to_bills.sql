-- Migrate bills from per-tenant to per-room billing
-- Run once: mysql -u root boardtrack < database/migrations/add_room_id_to_bills.sql

ALTER TABLE bills
    ADD COLUMN room_id INT NULL AFTER id,
    ADD INDEX idx_room_status (room_id, status);

-- Backfill room_id from tenant's assigned room
UPDATE bills b
INNER JOIN tenants t ON b.tenant_id = t.id
SET b.room_id = t.room_id
WHERE b.room_id IS NULL AND t.room_id IS NOT NULL;

ALTER TABLE bills
    MODIFY COLUMN tenant_id INT NULL,
    MODIFY COLUMN room_id INT NOT NULL,
    ADD CONSTRAINT fk_bills_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE;
