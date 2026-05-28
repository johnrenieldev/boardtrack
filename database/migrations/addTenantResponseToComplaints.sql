-- Add tenant response fields to complaints table
-- This allows tenants to reply to landlord responses on complaints

ALTER TABLE complaints
ADD COLUMN tenant_response TEXT DEFAULT NULL AFTER landlord_response,
ADD COLUMN tenant_response_at TIMESTAMP NULL DEFAULT NULL AFTER tenant_response;
