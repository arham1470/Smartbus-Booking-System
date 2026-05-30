-- =====================================================
-- SmartBus Booking System - Phase 8 Performance Optimizations
-- Run these indexes in production for better query speed
-- =====================================================

USE smartbus_db;

-- Indexes for faster schedule searches (used heavily in passenger search)
CREATE INDEX IF NOT EXISTS idx_schedules_departure_status ON schedules (departure_time, status);
CREATE INDEX IF NOT EXISTS idx_schedules_route_departure ON schedules (route_id, departure_time);

-- Index for user lookups by email (login)
CREATE INDEX IF NOT EXISTS idx_users_email_status ON users (email, status);

-- Index for operator-specific data
CREATE INDEX IF NOT EXISTS idx_buses_operator_status ON buses (operator_id, status);

-- Index for booking lookups by user (My Bookings page)
CREATE INDEX IF NOT EXISTS idx_bookings_user_status ON bookings (user_id, status, booked_at);

-- Index for notifications
CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications (user_id, is_read, created_at);

-- Index for seat checking during booking
CREATE INDEX IF NOT EXISTS idx_booking_seats_schedule ON booking_seats (booking_id);

-- Recommended: Analyze tables after adding indexes
-- ANALYZE TABLE schedules, bookings, users, buses;

-- =====================================================
-- Notes:
-- These indexes significantly speed up:
--   - Passenger search results
--   - Login performance
--   - Operator dashboards
--   - My Bookings / Admin booking lists
-- =====================================================
