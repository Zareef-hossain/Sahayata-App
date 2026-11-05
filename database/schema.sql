-- =====================================================================
-- Sahayata Unified Platform — database schema
-- Target: MySQL 8.0+ / MariaDB 10.4+ (XAMPP ships MariaDB)
--
-- Run this file first, then database/seed.sql.
-- WARNING: running it again DROPS the existing sahayata database.
-- =====================================================================

DROP DATABASE IF EXISTS sahayata;
CREATE DATABASE sahayata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sahayata;

-- ---------------------------------------------------------------------
-- Lookup tables (small lists of fixed values)
-- ---------------------------------------------------------------------

-- The five account types: manager, admin, donor, volunteer, victim.
CREATE TABLE roles (
    id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(20) NOT NULL UNIQUE,   -- used in code, e.g. 'donor'
    name VARCHAR(50) NOT NULL           -- shown in the UI, e.g. 'Donator'
) ENGINE=InnoDB;

-- "Disaster Type" dropdown on the Request for Help screen.
CREATE TABLE disaster_types (
    id   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- "Donation Type" field on the Make Donation screen.
CREATE TABLE donation_types (
    id   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- "Payment Method" field on the Make Donation screen (demo only — no real gateway).
CREATE TABLE payment_methods (
    id   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Users and accounts
-- ---------------------------------------------------------------------

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id       TINYINT UNSIGNED NOT NULL,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(20)  NOT NULL UNIQUE,      -- stored as 01XXXXXXXXX
    password_hash VARCHAR(255) NOT NULL,              -- from password_hash()
    -- pending  = registered but OTP not verified yet
    -- active   = can log in
    -- inactive = deactivated (e.g. an admin deactivated by the manager)
    status        ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'pending',
    created_by    INT UNSIGNED NULL,                  -- manager who created an admin account
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role       FOREIGN KEY (role_id)    REFERENCES roles(id),
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_users_role_status (role_id, status)
) ENGINE=InnoDB;

-- Extra data that only volunteers have ("Status : Available" / "(Busy)").
CREATE TABLE volunteer_profiles (
    user_id      INT UNSIGNED PRIMARY KEY,
    availability ENUM('available', 'busy') NOT NULL DEFAULT 'available',
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_volunteer_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Demo one-time passwords (registration and forgot password).
-- Only a hash of the code is stored. No SMS is sent — see README.
CREATE TABLE otp_codes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    purpose     ENUM('registration', 'password_reset') NOT NULL,
    code_hash   VARCHAR(255) NOT NULL,
    attempts    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    expires_at  DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_otp_codes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_otp_codes_lookup (user_id, purpose, consumed_at)
) ENGINE=InnoDB;

-- Failed login attempts, used to slow down password guessing.
CREATE TABLE login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(150) NOT NULL,
    ip_address   VARCHAR(45)  NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_email (email, attempted_at),
    INDEX idx_login_attempts_ip (ip_address, attempted_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Relief work
-- ---------------------------------------------------------------------

-- Places shown as "Nearest Relief Point" on the Track My Request map.
CREATE TABLE relief_points (
    id        SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(100) NOT NULL,
    address   VARCHAR(255) NOT NULL,
    latitude  DECIMAL(9, 6) NOT NULL,
    longitude DECIMAL(9, 6) NOT NULL
) ENGINE=InnoDB;

-- A victim's "Request for Help".
-- Workflow: pending -> approved / rejected -> assigned -> picked_up -> in_transit -> delivered
CREATE TABLE help_requests (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    victim_id        INT UNSIGNED NOT NULL,
    disaster_type_id SMALLINT UNSIGNED NOT NULL,
    urgency          ENUM('low', 'medium', 'high') NOT NULL,
    location         VARCHAR(255) NOT NULL,
    latitude         DECIMAL(9, 6) NULL,          -- optional, from "My Location"
    longitude        DECIMAL(9, 6) NULL,
    contact_phone    VARCHAR(20) NOT NULL,
    description      TEXT NOT NULL,
    status           ENUM('pending', 'approved', 'rejected', 'assigned',
                          'picked_up', 'in_transit', 'delivered') NOT NULL DEFAULT 'pending',
    reviewed_by      INT UNSIGNED NULL,           -- admin who approved / rejected
    reviewed_at      DATETIME NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_help_requests_victim   FOREIGN KEY (victim_id)        REFERENCES users(id),
    CONSTRAINT fk_help_requests_disaster FOREIGN KEY (disaster_type_id) REFERENCES disaster_types(id),
    CONSTRAINT fk_help_requests_reviewer FOREIGN KEY (reviewed_by)      REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_help_requests_status (status),
    INDEX idx_help_requests_victim (victim_id)
) ENGINE=InnoDB;

-- Every status change of a help request, used for the progress timeline.
CREATE TABLE request_status_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    help_request_id INT UNSIGNED NOT NULL,
    status          ENUM('pending', 'approved', 'rejected', 'assigned',
                         'picked_up', 'in_transit', 'delivered') NOT NULL,
    changed_by      INT UNSIGNED NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_request FOREIGN KEY (help_request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_status_history_user    FOREIGN KEY (changed_by)      REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status_history_request (help_request_id, created_at)
) ENGINE=InnoDB;

-- A donor's "Make Donation". Payments are DEMO records only.
CREATE TABLE donations (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donor_id          INT UNSIGNED NOT NULL,
    help_request_id   INT UNSIGNED NULL,           -- the approved request chosen with "Donate Now"
    donation_type_id  SMALLINT UNSIGNED NOT NULL,
    amount_bdt        DECIMAL(12, 2) NOT NULL,
    payment_method_id SMALLINT UNSIGNED NOT NULL,
    payer_mobile      VARCHAR(20) NOT NULL,
    demo_reference    VARCHAR(32) NOT NULL UNIQUE, -- generated reference, not a real transaction ID
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_donations_amount   CHECK (amount_bdt > 0),
    CONSTRAINT fk_donations_donor     FOREIGN KEY (donor_id)          REFERENCES users(id),
    CONSTRAINT fk_donations_request   FOREIGN KEY (help_request_id)   REFERENCES help_requests(id) ON DELETE SET NULL,
    CONSTRAINT fk_donations_type      FOREIGN KEY (donation_type_id)  REFERENCES donation_types(id),
    CONSTRAINT fk_donations_method    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id),
    INDEX idx_donations_donor (donor_id)
) ENGINE=InnoDB;

-- Delivery of relief for one help request.
-- open      = created, waiting for a volunteer to "Accept Task"
-- assigned  = a volunteer is responsible (admin "Assign to this Task" or volunteer accepted)
-- picked_up / in_transit / delivered = updated by the volunteer on "Delivery Progress"
CREATE TABLE deliveries (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    help_request_id   INT UNSIGNED NOT NULL UNIQUE,  -- one delivery per request
    donation_id       INT UNSIGNED NULL,
    volunteer_id      INT UNSIGNED NULL,
    assigned_by       INT UNSIGNED NULL,             -- admin, or NULL when the volunteer accepted it
    items_description VARCHAR(255) NULL,             -- e.g. "50 water bottles"
    status            ENUM('open', 'assigned', 'picked_up', 'in_transit', 'delivered') NOT NULL DEFAULT 'open',
    assigned_at       DATETIME NULL,
    delivered_at      DATETIME NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_request   FOREIGN KEY (help_request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_deliveries_donation  FOREIGN KEY (donation_id)     REFERENCES donations(id) ON DELETE SET NULL,
    CONSTRAINT fk_deliveries_volunteer FOREIGN KEY (volunteer_id)    REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_deliveries_assigner  FOREIGN KEY (assigned_by)     REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_deliveries_status (status),
    INDEX idx_deliveries_volunteer (volunteer_id, status)
) ENGINE=InnoDB;
