-- Add tenant preference for air-conditioned rooms
ALTER TABLE tenants
    ADD COLUMN air_conditioned_preference BOOLEAN DEFAULT FALSE;

