-- =====================================================
-- BoardTrack Database Schema
-- Complete SQL for all tables, indexes, and constraints
-- =====================================================

-- Drop tables if they exist (for clean setup)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bills;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS waiting_list;
DROP TABLE IF EXISTS personality_answers;
DROP TABLE IF EXISTS personality_questions;
DROP TABLE IF EXISTS tenants;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS password_resets;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. USERS TABLE (Base table for authentication)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('landlord', 'tenant') NOT NULL DEFAULT 'tenant',
    status ENUM('pending', 'active', 'rejected', 'moved_out', 'waiting_list', 'unverified') DEFAULT 'unverified',
    phone VARCHAR(20) DEFAULT NULL,
    gcash_qr_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role_status (role, status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. EMAIL VERIFICATIONS TABLE
-- =====================================================
CREATE TABLE email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PASSWORD RESETS TABLE
-- =====================================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. ROOMS TABLE
-- =====================================================
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    floor INT DEFAULT 1,
    room_type ENUM('single', 'shared') NOT NULL DEFAULT 'shared',
    max_occupants INT NOT NULL DEFAULT 1,
    current_occupants INT DEFAULT 0,
    monthly_rent DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT,
    amenities JSON,
    status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_room_type (room_type),
    INDEX idx_status (status),
    INDEX idx_floor (floor),
    CHECK (current_occupants <= max_occupants)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TENANTS TABLE (Extended profile for tenants)
-- =====================================================
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    room_id INT DEFAULT NULL,
    room_preference ENUM('single', 'shared') DEFAULT NULL,
    id_file_path VARCHAR(255) DEFAULT NULL,
    id_verified BOOLEAN DEFAULT FALSE,
    id_verified_at TIMESTAMP NULL,
    move_in_date DATE DEFAULT NULL,
    move_out_date DATE DEFAULT NULL,
    emergency_contact_name VARCHAR(100) DEFAULT NULL,
    emergency_contact_phone VARCHAR(20) DEFAULT NULL,
    guardian_name VARCHAR(120) DEFAULT NULL,
    guardian_email VARCHAR(255) DEFAULT NULL,
    guardian_purpose TEXT DEFAULT NULL,
    address TEXT DEFAULT NULL,
    personality_completed BOOLEAN DEFAULT FALSE,
    personality_completed_at TIMESTAMP NULL,
    personality_flagged BOOLEAN DEFAULT FALSE,
    personality_flag_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
    INDEX idx_room (room_id),
    INDEX idx_user (user_id),
    INDEX idx_move_in (move_in_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. PERSONALITY QUESTIONS TABLE
-- =====================================================
CREATE TABLE personality_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('sleep_schedule', 'cleanliness', 'noise_tolerance', 'study_habits', 'social_preference') NOT NULL,
    question_text TEXT NOT NULL,
    weight DECIMAL(3,2) NOT NULL DEFAULT 1.00,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. PERSONALITY ANSWERS TABLE
-- =====================================================
CREATE TABLE personality_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value INT NOT NULL CHECK (answer_value BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES personality_questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_question (tenant_id, question_id),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. WAITING LIST TABLE
-- =====================================================
CREATE TABLE waiting_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    room_type_preference ENUM('single', 'shared') NOT NULL,
    priority_order INT DEFAULT 0,
    status ENUM('waiting', 'notified', 'assigned', 'expired') DEFAULT 'waiting',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notified_at TIMESTAMP NULL,
    assigned_at TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_room_type (room_type_preference),
    INDEX idx_priority (priority_order, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. BILLS TABLE
-- =====================================================
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT DEFAULT NULL,
    room_id INT NOT NULL,
    billing_type ENUM('individual', 'room_based') NOT NULL DEFAULT 'room_based',
    charge_category ENUM('rent', 'utility', 'maintenance', 'penalty', 'other') NOT NULL DEFAULT 'rent',
    bill_name VARCHAR(100) NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('unpaid', 'pending_verification', 'paid', 'overdue', 'cancelled') DEFAULT 'unpaid',
    created_by INT NOT NULL,
    paid_at DATETIME DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_room_status (room_id, status),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. PAYMENTS TABLE
-- =====================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    tenant_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_method ENUM('bank_transfer', 'gcash', 'cash', 'other') DEFAULT 'other',
    proof_file_path VARCHAR(255) NOT NULL,
    proof_file_name VARCHAR(100) DEFAULT NULL,
    notes TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT DEFAULT NULL,
    review_notes TEXT,
    reviewed_at TIMESTAMP NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_bill (bill_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_uploaded (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. COMPLAINTS TABLE
-- =====================================================
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    category ENUM('maintenance', 'roommate_conflict', 'billing', 'room_change', 'other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    is_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
    landlord_response TEXT,
    resolved_by INT DEFAULT NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_anonymous (is_anonymous)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. ANNOUNCEMENTS TABLE
-- =====================================================
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    event_date DATE DEFAULT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_priority (priority),
    INDEX idx_created (created_at),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('system', 'billing', 'complaint', 'announcement', 'room', 'payment') DEFAULT 'system',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    link_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. AUDIT LOGS TABLE
-- =====================================================
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT DEFAULT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Insert default landlord account (password: landlord123)
-- You should change this password immediately after setup
INSERT INTO users (name, email, password, role, status) VALUES 
('System Landlord', 'landlord@boardtrack.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'active');

-- Insert personality questions
INSERT INTO personality_questions (category, question_text, weight, display_order) VALUES
-- Sleep Schedule (High weight - 1.5)
('sleep_schedule', 'I typically go to bed between:', 1.50, 1),
('sleep_schedule', 'I consider myself a:', 1.50, 2),

-- Cleanliness (High weight - 1.4)
('cleanliness', 'I prefer my living space to be:', 1.40, 3),
('cleanliness', 'I clean my personal space:', 1.40, 4),

-- Noise Tolerance (High weight - 1.5)
('noise_tolerance', 'When studying or working, I prefer the environment to be:', 1.50, 5),
('noise_tolerance', 'I am comfortable with music or TV playing:', 1.50, 6),

-- Study Habits (Medium weight - 1.2)
('study_habits', 'I typically study/work in my room:', 1.20, 7),
('study_habits', 'When focused on tasks, I prefer:', 1.20, 8),

-- Social Preference (Lower weight - 1.0)
('social_preference', 'I prefer to spend my free time:', 1.00, 9),
('social_preference', 'When it comes to having guests in the room:', 1.00, 10);
