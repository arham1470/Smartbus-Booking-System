-- =====================================================
-- SmartBus Booking System
-- Complete Database Schema + Sample Data
-- PHASE 2 - DATABASE DESIGN
-- =====================================================
-- 
-- This script creates the entire database with:
-- - Proper normalization
-- - Foreign key relationships
-- - Indexes for performance
-- - Realistic sample data
--
-- Usage:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Create a new database named: smartbus_db
-- 3. Select the database → Import → Choose this file
--    OR run this script directly in SQL tab
--
-- =====================================================

-- Drop database if exists (for clean reinstalls during development)
DROP DATABASE IF EXISTS smartbus_db;

-- Create fresh database with proper charset
CREATE DATABASE smartbus_db 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE smartbus_db;

-- =====================================================
-- TABLE: users
-- Stores all system users (passengers, operators, admins)
-- Role-based access is controlled via the 'role' column
-- =====================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL COMMENT 'bcrypt hash from password_hash()',
    phone VARCHAR(30) NULL,
    role ENUM('passenger', 'operator', 'admin') NOT NULL DEFAULT 'passenger',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: operators
-- Additional profile data for bus operators (companies)
-- Linked 1:1 with users where role = 'operator'
-- =====================================================
CREATE TABLE operators (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    license_number VARCHAR(50) NULL,
    contact_person VARCHAR(100) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    INDEX idx_operators_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: buses
-- Fleet managed by operators
-- =====================================================
CREATE TABLE buses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    operator_id BIGINT UNSIGNED NOT NULL,
    bus_number VARCHAR(50) NOT NULL,
    bus_type ENUM('Standard', 'Deluxe', 'Sleeper', 'Semi-Sleeper') NOT NULL DEFAULT 'Standard',
    total_seats SMALLINT UNSIGNED NOT NULL DEFAULT 40,
    seat_layout VARCHAR(50) NULL COMMENT 'e.g. 2x2, 2x1',
    amenities TEXT NULL COMMENT 'AC, WiFi, Charging, Toilet, etc.',
    status ENUM('active', 'maintenance', 'retired') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (operator_id) REFERENCES operators(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    UNIQUE KEY uk_operator_bus_number (operator_id, bus_number),
    INDEX idx_buses_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: routes
-- Defines origin → destination paths (not time-specific)
-- =====================================================
CREATE TABLE routes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    origin_city VARCHAR(100) NOT NULL,
    destination_city VARCHAR(100) NOT NULL,
    distance_km DECIMAL(8,2) NULL,
    estimated_duration_minutes INT UNSIGNED NULL COMMENT 'Average travel time in minutes',
    base_fare DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_routes_origin (origin_city),
    INDEX idx_routes_destination (destination_city),
    INDEX idx_routes_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: schedules
-- Actual bus departures (date + time specific)
-- This is the core table passengers search against
-- =====================================================
CREATE TABLE schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bus_id BIGINT UNSIGNED NOT NULL,
    route_id BIGINT UNSIGNED NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    price_per_seat DECIMAL(10,2) NOT NULL,
    available_seats SMALLINT UNSIGNED NOT NULL,
    status ENUM('scheduled', 'boarding', 'departed', 'arrived', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (bus_id) REFERENCES buses(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    INDEX idx_schedules_departure (departure_time),
    INDEX idx_schedules_route (route_id),
    INDEX idx_schedules_status (status),
    INDEX idx_schedules_bus (bus_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: bookings
-- Passenger reservations
-- =====================================================
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Passenger who made the booking',
    schedule_id BIGINT UNSIGNED NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE COMMENT 'Human-friendly code e.g. SB-20250214-7841',
    number_of_seats SMALLINT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    
    INDEX idx_bookings_reference (booking_reference),
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_schedule (schedule_id),
    INDEX idx_bookings_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: booking_seats
-- Individual seats reserved in a booking (supports Phase 7 seat map)
-- =====================================================
CREATE TABLE booking_seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    seat_number VARCHAR(10) NOT NULL COMMENT 'e.g. A12, 23, Window-05',
    passenger_name VARCHAR(150) NULL COMMENT 'Name of person sitting in this seat',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    UNIQUE KEY uk_booking_seat (booking_id, seat_number),
    INDEX idx_booking_seats_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: payments
-- Payment records linked to bookings
-- =====================================================
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile_money', 'bank_transfer') NOT NULL DEFAULT 'card',
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    transaction_reference VARCHAR(100) NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    
    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: notifications
-- In-app notifications for users (all roles)
-- =====================================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking', 'payment', 'schedule', 'system', 'reminder') NOT NULL DEFAULT 'system',
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- 1. USERS (Mixed roles)
-- Password for all sample accounts: "Password123" 
-- (Hashed with password_hash() - actual hash below is for "Password123")

INSERT INTO users (full_name, email, password_hash, phone, role, status) VALUES
-- Admin
('System Administrator', 'admin@smartbus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0100', 'admin', 'active'),

-- Operators (2 companies)
('Michael Torres', 'michael@expressbus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0201', 'operator', 'active'),
('Sarah Patel', 'sarah@citylink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0202', 'operator', 'active'),

-- Passengers
('James Wilson', 'james.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1001', 'passenger', 'active'),
('Emily Rodriguez', 'emily.rodriguez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1002', 'passenger', 'active'),
('David Kim', 'david.kim@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1003', 'passenger', 'active'),
('Aisha Thompson', 'aisha.thompson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1004', 'passenger', 'active'),
('Carlos Mendoza', 'carlos.mendoza@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1005', 'passenger', 'active');

-- 2. OPERATORS (linked to operator users)
INSERT INTO operators (user_id, company_name, license_number, contact_person, city, status) VALUES
(2, 'Express Bus Lines', 'OP-78421', 'Michael Torres', 'Chicago', 'active'),
(3, 'CityLink Transport', 'OP-99234', 'Sarah Patel', 'New York', 'active');

-- 3. BUSES (owned by operators)
INSERT INTO buses (operator_id, bus_number, bus_type, total_seats, seat_layout, amenities, status) VALUES
(1, 'EB-101', 'Deluxe', 40, '2x2', 'AC, WiFi, Power Outlets, Reclining Seats', 'active'),
(1, 'EB-102', 'Standard', 45, '2x2', 'AC, Reclining Seats', 'active'),
(2, 'CL-201', 'Sleeper', 32, '2x1', 'AC, WiFi, Charging Ports, Blankets, Toilet', 'active'),
(2, 'CL-202', 'Semi-Sleeper', 38, '2x2', 'AC, WiFi, Power Outlets', 'active'),
(1, 'EB-103', 'Standard', 42, '2x2', 'AC', 'maintenance');

-- 4. ROUTES
INSERT INTO routes (origin_city, destination_city, distance_km, estimated_duration_minutes, base_fare, status) VALUES
('Chicago', 'New York', 1260, 780, 89.00, 'active'),
('New York', 'Boston', 350, 240, 35.00, 'active'),
('Chicago', 'Detroit', 450, 300, 42.00, 'active'),
('Los Angeles', 'San Francisco', 615, 420, 65.00, 'active'),
('Boston', 'New York', 350, 255, 38.00, 'active'),
('Chicago', 'St. Louis', 480, 330, 48.00, 'active'),
('New York', 'Washington DC', 370, 270, 45.00, 'active'),
('Detroit', 'Chicago', 450, 310, 42.00, 'active');

-- 5. SCHEDULES (mix of upcoming and a few past for testing)
-- Note: Using realistic future dates relative to when script is run
INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, price_per_seat, available_seats, status) VALUES
-- Operator 1 (Express Bus)
(1, 1, '2026-02-20 08:00:00', '2026-02-21 05:00:00', 95.00, 40, 'scheduled'),
(2, 3, '2026-02-20 14:30:00', '2026-02-20 19:30:00', 48.00, 45, 'scheduled'),
(1, 6, '2026-02-21 09:15:00', '2026-02-21 14:45:00', 52.00, 38, 'scheduled'),

-- Operator 2 (CityLink)
(3, 2, '2026-02-20 07:45:00', '2026-02-20 11:45:00', 42.00, 32, 'scheduled'),
(4, 4, '2026-02-21 22:00:00', '2026-02-22 05:00:00', 72.00, 36, 'scheduled'),
(3, 7, '2026-02-22 10:00:00', '2026-02-22 14:30:00', 55.00, 30, 'scheduled'),

-- More variety
(2, 1, '2026-02-22 23:30:00', '2026-02-23 20:30:00', 89.00, 42, 'scheduled'),
(4, 5, '2026-02-19 06:00:00', '2026-02-19 10:15:00', 38.00, 12, 'scheduled'),  -- Almost full
(1, 3, '2026-02-18 16:00:00', '2026-02-18 21:00:00', 45.00, 0, 'departed'),      -- Past example
(3, 2, '2026-02-23 08:00:00', '2026-02-23 12:00:00', 42.00, 28, 'scheduled');

-- 6. BOOKINGS + SEATS + PAYMENTS (some confirmed, one cancelled)
INSERT INTO bookings (user_id, schedule_id, booking_reference, number_of_seats, total_amount, status, payment_status, booked_at) VALUES
(4, 1, 'SB-20260218-7841', 2, 190.00, 'confirmed', 'paid', '2026-02-15 14:22:00'),
(5, 4, 'SB-20260218-7842', 1, 42.00, 'confirmed', 'paid', '2026-02-16 09:10:00'),
(6, 2, 'SB-20260218-7843', 3, 144.00, 'confirmed', 'paid', '2026-02-17 11:45:00'),
(7, 8, 'SB-20260218-7844', 2, 76.00, 'cancelled', 'refunded', '2026-02-10 08:30:00'),
(4, 5, 'SB-20260218-7845', 1, 72.00, 'pending', 'unpaid', '2026-02-18 16:05:00');

-- Booking seats for the above bookings
INSERT INTO booking_seats (booking_id, seat_number, passenger_name) VALUES
(1, 'A12', 'James Wilson'),
(1, 'A13', 'Maria Wilson'),
(2, 'B07', 'Emily Rodriguez'),
(3, 'C03', 'David Kim'),
(3, 'C04', 'Linda Kim'),
(3, 'C05', 'Tommy Kim'),
(4, 'A22', 'Aisha Thompson'),   -- This booking was cancelled
(4, 'A23', 'Marcus Thompson'),
(5, 'D15', 'Carlos Mendoza');

-- Payments
INSERT INTO payments (booking_id, amount, payment_method, status, transaction_reference, paid_at) VALUES
(1, 190.00, 'card', 'completed', 'TXN-9847321', '2026-02-15 14:23:15'),
(2, 42.00, 'mobile_money', 'completed', 'MM-449201', '2026-02-16 09:11:40'),
(3, 144.00, 'card', 'completed', 'TXN-9848102', '2026-02-17 11:46:02'),
(4, 76.00, 'card', 'refunded', 'TXN-9832101', '2026-02-10 08:31:00');

-- 7. NOTIFICATIONS
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES
(4, 'Booking Confirmed', 'Your booking SB-20260218-7841 for Chicago → New York on Feb 20 has been confirmed. Seats: A12, A13.', 'booking', FALSE),
(5, 'Booking Confirmed', 'Your booking SB-20260218-7842 is confirmed. Have a great trip!', 'booking', TRUE),
(7, 'Booking Cancelled', 'Your booking SB-20260218-7844 was successfully cancelled. Refund has been processed.', 'booking', FALSE),
(4, 'Payment Successful', 'Payment of $190.00 for booking SB-20260218-7841 was successful.', 'payment', TRUE),
(1, 'New Operator Registered', 'A new operator account was created: CityLink Transport.', 'system', FALSE);

-- =====================================================
-- FINAL NOTES
-- =====================================================
-- All passwords for sample accounts = "Password123"
-- Use this for testing login in Phase 3
--
-- Recommended indexes added for common queries:
--   - Searching schedules by date/route
--   - Looking up bookings by user or reference
--   - Operator filtering
--
-- You can now proceed to Phase 3 (Authentication System)
-- =====================================================
