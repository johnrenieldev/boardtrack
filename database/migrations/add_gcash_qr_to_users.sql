-- Landlord GCash QR for tenant payments
ALTER TABLE users
    ADD COLUMN gcash_qr_path VARCHAR(255) DEFAULT NULL;
