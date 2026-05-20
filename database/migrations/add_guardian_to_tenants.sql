-- Emergency guardian / parent contact for tenants
ALTER TABLE tenants
  ADD COLUMN IF NOT EXISTS guardian_name VARCHAR(120) DEFAULT NULL AFTER emergency_contact_phone,
  ADD COLUMN IF NOT EXISTS guardian_email VARCHAR(255) DEFAULT NULL AFTER guardian_name,
  ADD COLUMN IF NOT EXISTS guardian_purpose TEXT DEFAULT NULL AFTER guardian_email;
