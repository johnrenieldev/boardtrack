-- Add air conditioning attribute to rooms
ALTER TABLE rooms
    ADD COLUMN air_conditioned BOOLEAN DEFAULT FALSE;

