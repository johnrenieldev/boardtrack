-- Migration: Add Partial Payment Support
-- This migration adds support for partial payments and enhanced bill status tracking

-- Add columns to bills table for partial payment tracking
ALTER TABLE bills 
ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0.00 AFTER amount,
ADD COLUMN partial_payment_status ENUM('none', 'partial', 'full') DEFAULT 'none' AFTER status,
ADD COLUMN payment_plan_id INT DEFAULT NULL AFTER notes,
ADD COLUMN last_payment_date DATE DEFAULT NULL AFTER paid_at;

-- Add reminder tracking to bills table
ALTER TABLE bills
ADD COLUMN reminder_sent_1 BOOLEAN DEFAULT FALSE AFTER last_payment_date,
ADD COLUMN reminder_sent_2 BOOLEAN DEFAULT FALSE AFTER reminder_sent_1,
ADD COLUMN reminder_sent_3 BOOLEAN DEFAULT FALSE AFTER reminder_sent_2,
ADD COLUMN reminder_dates JSON DEFAULT NULL AFTER reminder_sent_3;

-- Add column to payments table for partial payment reference
ALTER TABLE payments
ADD COLUMN is_partial BOOLEAN DEFAULT FALSE AFTER notes,
ADD COLUMN payment_plan_id INT DEFAULT NULL AFTER is_partial;

-- Create payment_plans table for installment tracking
CREATE TABLE IF NOT EXISTS payment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    tenant_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    number_of_installments INT NOT NULL DEFAULT 1,
    installment_amount DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'completed', 'cancelled', 'defaulted') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    next_payment_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_bill (bill_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_next_payment (next_payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment_schedule table for individual installments
CREATE TABLE IF NOT EXISTS payment_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_plan_id INT NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'skipped') DEFAULT 'pending',
    paid_date DATE DEFAULT NULL,
    payment_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_plan_id) REFERENCES payment_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_plan (payment_plan_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update bills table status enum to include partial payment statuses
ALTER TABLE bills 
MODIFY COLUMN status ENUM('unpaid', 'pending_verification', 'partial', 'paid', 'overdue', 'cancelled', 'payment_plan') DEFAULT 'unpaid';
