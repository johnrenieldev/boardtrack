-- =====================================================
-- Migration: addCompatibilityCache.sql
-- Purpose: Cache compatibility scores to improve performance
-- Date: May 28, 2026
-- =====================================================

CREATE TABLE IF NOT EXISTS tenant_compatibility_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    room_id INT NOT NULL,
    compatibility_score DECIMAL(5,2) NOT NULL,
    compatibility_status VARCHAR(50) NOT NULL,
    reasons JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_room (tenant_id, room_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_room (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
