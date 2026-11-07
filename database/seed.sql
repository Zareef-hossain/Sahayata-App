-- =====================================================================
-- Sahayata Unified Platform — starter data
-- Run AFTER database/schema.sql.
--
-- Contains only:
--   * fixed lookup values (roles, disaster types, donation types, payment methods)
--   * clearly-labelled DEMO accounts for testing each role
--   * DEMO relief points (city-level coordinates, not real facilities)
--
-- No real people, phone numbers or payment transactions are included.
-- Demo phone numbers start with 010, which is not a valid Bangladeshi
-- mobile prefix, so they cannot belong to anyone.
--
-- Every demo account uses the password:  Demo@1234
-- =====================================================================

USE sahayata;

INSERT INTO roles (id, slug, name) VALUES
    (1, 'manager',   'Manager'),
    (2, 'admin',     'Admin'),
    (3, 'donor',     'Donor'),
    (4, 'volunteer', 'Volunteer'),
    (5, 'victim',    'Victim');

INSERT INTO disaster_types (name) VALUES
    ('Flood'), ('Fire'), ('Cyclone'), ('Earthquake'), ('Landslide'), ('Other');

INSERT INTO donation_types (name) VALUES
    ('Money'), ('Food'), ('Water'), ('Medicine'), ('Clothing'), ('Shelter');

INSERT INTO payment_methods (name) VALUES
    ('bKash'), ('Nagad'), ('Rocket'), ('Card');

-- Password for all demo accounts: Demo@1234
SET @demo_hash = '$2y$10$RLSanNn3uFAZ7MKzm96ULeVkTMwSXEJdaqsy/iuMJ5mSomb7sM7dq';

INSERT INTO users (id, role_id, full_name, email, phone, password_hash, status, created_by) VALUES
    (1, 1, 'Demo Manager',   'manager@example.com',   '01000000001', @demo_hash, 'active', NULL),
    (2, 2, 'Demo Admin',     'admin@example.com',     '01000000002', @demo_hash, 'active', 1),
    (3, 3, 'Demo Donor',     'donor@example.com',     '01000000003', @demo_hash, 'active', NULL),
    (4, 4, 'Demo Volunteer', 'volunteer@example.com', '01000000004', @demo_hash, 'active', NULL),
    (5, 5, 'Demo Victim',    'victim@example.com',    '01000000005', @demo_hash, 'active', NULL);

INSERT INTO volunteer_profiles (user_id, availability) VALUES
    (4, 'available');

-- Demo relief points at approximate city-centre coordinates.
INSERT INTO relief_points (name, address, latitude, longitude) VALUES
    ('Demo Relief Point - Dhaka',      'Dhaka city centre (demo)',      23.810300, 90.412500),
    ('Demo Relief Point - Sylhet',     'Sylhet city centre (demo)',     24.894900, 91.868700),
    ('Demo Relief Point - Chattogram', 'Chattogram city centre (demo)', 22.356900, 91.783200),
    ('Demo Relief Point - Cumilla',    'Cumilla city centre (demo)',    23.460700, 91.180900);
