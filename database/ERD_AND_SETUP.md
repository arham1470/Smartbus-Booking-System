# SmartBus Database Design - Phase 2

**Complete Entity Relationship Diagram + Setup Guide**

---

## Overview

The SmartBus database is designed with **strong normalization**, clear relationships, and real-world performance considerations.

**Key Design Principles:**
- Single source of truth for users (passengers + operators + admins)
- Role-specific data stored in dedicated tables (`operators`)
- Schedules are the central searchable entity
- Seat-level granularity supported via `booking_seats` (ready for Phase 7 interactive seat map)
- Full audit trail using timestamps + status fields
- Soft business rules enforced via foreign keys

---

## Entity Relationship Diagram (Simplified Visual)

```
┌─────────────┐
│   users     │
│─────────────│
│ id          │◄──────┐
│ full_name   │       │
│ email       │       │
│ password    │       │
│ phone       │       │
│ role        │       │
│ status      │       │
└──────┬──────┘       │
       │              │
       │ 1            │
       │              │
       │              │
       ▼ N            │
┌─────────────┐       │
│ operators   │       │
│─────────────│       │
│ id          │       │
│ user_id  ───┼───────┘ (1:1 for operators)
│ company_name│
│ license     │
└──────┬──────┘
       │ 1
       │
       │ N
       ▼
┌─────────────┐
│   buses     │
│─────────────│
│ id          │
│ operator_id │
│ bus_number  │
│ total_seats │
│ status      │
└──────┬──────┘
       │ 1
       │
       │ N
       ▼
┌─────────────┐      ┌─────────────┐
│ schedules   │      │   routes    │
│─────────────│      │─────────────│
│ id          │◄─────│ id          │
│ bus_id      │      │ origin      │
│ route_id    │      │ destination │
│ departure   │      │ base_fare   │
│ price       │      └─────────────┘
│ available   │
│ status      │
└──────┬──────┘
       │ 1
       │
       │ N
       ▼
┌─────────────┐
│  bookings   │
│─────────────│
│ id          │
│ user_id     │
│ schedule_id │
│ reference   │
│ seats       │
│ total       │
│ status      │
└──────┬──────┘
       │ 1
       ├──────────────┬──────────────┐
       │              │              │
       ▼ N            ▼ N            ▼ N
┌──────────────┐ ┌─────────────┐ ┌─────────────┐
│ booking_seats│ │  payments   │ │notifications│
│──────────────│ │─────────────│ │─────────────│
│ booking_id   │ │ booking_id  │ │ user_id     │
│ seat_number  │ │ amount      │ │ message     │
│ passenger    │ │ status      │ │ is_read     │
└──────────────┘ └─────────────┘ └─────────────┘
```

**Relationship Summary:**

| Relationship          | Type     | Description |
|-----------------------|----------|-----------|
| users → operators     | 1 : 1    | One user can be one operator profile |
| operators → buses     | 1 : N    | One company owns many buses |
| routes → schedules    | 1 : N    | One route has many scheduled trips |
| buses → schedules     | 1 : N    | One bus runs on many schedules |
| users → bookings      | 1 : N    | One passenger can make many bookings |
| schedules → bookings  | 1 : N    | One schedule can have many bookings |
| bookings → booking_seats | 1 : N | One booking can reserve multiple seats |
| bookings → payments   | 1 : N    | One booking can have payment records |
| users → notifications | 1 : N    | Every user can receive notifications |

---

## Table Descriptions

### 1. `users` (Core)
- Every person who uses the system
- `role` column determines permissions
- Passwords are **never** stored in plain text (use `password_hash()` in PHP)

### 2. `operators`
- Extra company information for users with role = 'operator'
- 1:1 relationship with users

### 3. `buses`
- Physical vehicles
- `total_seats` is the maximum capacity
- `available_seats` lives on `schedules` (can change per trip)

### 4. `routes`
- Static path information (Chicago → New York)
- Does **not** contain dates/times

### 5. `schedules`
- **Most important table for passengers**
- Represents a specific bus departure on a specific date/time
- `available_seats` is decremented when bookings are made

### 6. `bookings` + `booking_seats`
- `bookings` = the order
- `booking_seats` = individual seat assignments (enables Phase 7 seat selection)

### 7. `payments`
- Tracks money movement
- Supports multiple payment methods

### 8. `notifications`
- In-app messaging system (no email yet)

---

## Sample Data Included

The SQL file contains realistic data:

**Users:**
- 1 Admin (`admin@smartbus.com`)
- 2 Operators (Express Bus Lines + CityLink Transport)
- 5 Passengers

**All sample passwords:** `Password123`

**Data Volume:**
- 5 buses
- 8 routes
- 10 schedules (mix of upcoming + past)
- 5 bookings (different statuses)
- Seat assignments + payments

This data is perfect for testing search, booking, and reports in later phases.

---

## Setup Instructions (XAMPP + phpMyAdmin)

### Step-by-Step (Windows)

1. **Start Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL**

2. **Open phpMyAdmin**
   - Go to: `http://localhost/phpmyadmin`

3. **Create the Database**
   - Click **"New"** on the left sidebar
   - Database name: `smartbus_db`
   - Collation: `utf8mb4_unicode_ci`
   - Click **Create**

4. **Import the SQL File**
   - Select the new `smartbus_db` database on the left
   - Click the **"Import"** tab at the top
   - Click **"Choose File"**
   - Navigate to:
     ```
     C:\xampp\htdocs\SmartBus-Booking-System\database\smartbus.sql
     ```
   - Click **"Go"** at the bottom

5. **Verify Import**
   - You should see 9 tables in the left sidebar
   - Click on `users` → Browse → You should see 8 users

6. **(Optional) Quick Test Query**
   ```sql
   SELECT 
       u.full_name, 
       u.role, 
       o.company_name
   FROM users u
   LEFT JOIN operators o ON u.id = o.user_id
   ORDER BY u.role;
   ```

### Alternative: Using MySQL Command Line

```bash
cd C:\xampp\mysql\bin
mysql -u root -p
```

Then inside MySQL shell:
```sql
source C:/xampp/htdocs/SmartBus-Booking-System/database/smartbus.sql
```

---

## Connection Settings for PHP

Update (or verify) in `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartbus_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // Empty on default XAMPP
```

---

## Important Notes for Developers

- Never delete the `smartbus_db` database without backing up if you have real test data.
- The `booking_seats` table is intentionally included early so Phase 7 seat selection doesn't require schema changes.
- All monetary values use `DECIMAL(10,2)` — never use `FLOAT`.
- Status columns use `ENUM` for clarity and performance (easy to extend later if needed).

---

## Next Steps After This Phase

Once this database is imported, you are ready for:

**Phase 3 – Authentication System**
- Login / Register with real password hashing
- Session management
- Role-based access control using the `users.role` column

---

**Phase 2 Complete** — Database architecture is production-ready.
