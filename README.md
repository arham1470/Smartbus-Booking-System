# SmartBus Booking System

**A modern, full-stack web-based Bus Booking and Transportation Management Platform.**

Built with PHP, MySQL, HTML5, CSS3, and JavaScript. Designed for passengers, bus operators, and administrators.

---

## Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8+
- **Database**: MySQL 8+ (via PDO)
- **Styling**: Custom responsive CSS (mobile-first)
- **Server**: Apache (XAMPP recommended)

---

## Current Development Status

| Phase | Name                        | Status      | Git Tag                     |
|-------|-----------------------------|-------------|-----------------------------|
| 1     | **Project Setup**           | ✅ Complete | `phase-1-project-setup`     |
| 2     | **Database Design**         | ✅ Complete | `phase-2-database-design`   |
| 3     | **Authentication System**   | ✅ Complete | `phase-3-authentication`    |
| 4     | **Passenger Module**        | ✅ Complete | `phase-4-passenger-module`  |
| 5     | **Operator Module**         | ✅ Complete | `phase-5-operator-module`   |
| 6     | **Admin Module**            | ✅ Complete | `phase-6-admin-module`      |
| 7     | **Advanced Features**       | ✅ Complete | `phase-7-advanced-features` |
| 7     | Advanced Features           | ⏳ Pending   | -                           |
| 8     | Final Optimization          | ⏳ Pending   | -                           |

> **Latest Phase**: Phase 7 completed and committed to Git.

---

## Phase 1 – Deliverables (Completed)

**What was built in this phase:**

- Complete professional project folder structure
- Secure PDO database connection layer (`config/db.php`) – production-ready patterns
- Reusable header, footer, and sidebar templates (prepared for all roles)
- Comprehensive responsive CSS design system (buttons, forms, cards, navbar, tables, alerts, hero, auth layouts)
- Interactive JavaScript foundation (mobile nav, form loading states, password toggle, auto-dismiss alerts)
- Beautiful public landing page (`index.php`)
- Fully styled Login page (`login.php`)
- Fully styled Registration page (`register.php`)
- Logout stub (`logout.php`)
- Proper `.gitignore` and initial documentation
- Git repository initialized with clean history

All code follows clean architecture, security-ready patterns, and mobile-first principles.

---

## Project Structure (After Phase 1)

```
SmartBus-Booking-System/
├── .git/
├── .gitignore
├── README.md
├── index.php                 # Public landing page (hero + features)
├── login.php                 # Login page (styled + ready for backend)
├── register.php              # Registration page (styled + role selection)
├── logout.php                # Session destroy stub
│
├── config/
│   └── db.php                # PDO connection (secure, production patterns)
│
├── assets/
│   ├── css/
│   │   └── style.css         # Full design system + all components
│   ├── js/
│   │   └── script.js         # Mobile nav, forms, utilities
│   └── images/               # .gitkeep ready
│
├── includes/
│   ├── header.php            # Public + dashboard header (role-aware ready)
│   ├── footer.php            # Professional footer
│   └── sidebar.php           # Dashboard sidebar (prepared)
│
├── actions/                  # Future form handlers (Phase 3+)
│   └── .gitkeep
├── passenger/                # Future passenger module (Phase 4)
├── operator/                 # Future operator module (Phase 5)
├── admin/                    # Future admin module (Phase 6)
└── database/                 # Phase 2 Complete
    ├── smartbus.sql            # Complete schema + sample data
    └── ERD_AND_SETUP.md        # ERD + full setup guide
```

---

## Design System

**Brand Colors (exactly as specified)**
- Primary: `#1565C0`
- Secondary: `#42A5F5`
- Accent: `#0D47A1`
- Success: `#2E7D32`
- Warning: `#F9A825`
- Danger: `#C62828`
- Background: `#F5F7FA`
- Card: `#FFFFFF`

---

## Database (Phase 2 - Complete)

**Location:** `database/smartbus.sql`

### Tables Created (9 total)
- `users` — All roles (passenger / operator / admin)
- `operators` — Company profiles for bus operators
- `buses` — Fleet management
- `routes` — Origin → Destination paths
- `schedules` — Actual departures (core search table)
- `bookings` — Reservations
- `booking_seats` — Individual seat assignments (ready for interactive maps)
- `payments` — Payment records
- `notifications` — In-app alerts

### Sample Data Included
- 1 Admin + 2 Operators + 5 Passengers
- 5 Buses across 2 companies
- 8 Routes + 10 Schedules (mix of future/past)
- Multiple bookings with different statuses + seat assignments

**All sample passwords:** `Password123`

### Documentation
Full ERD, relationship explanations, and detailed setup instructions are in:
→ [database/ERD_AND_SETUP.md](database/ERD_AND_SETUP.md)

### Quick Setup
1. Start MySQL in XAMPP
2. Create database `smartbus_db` in phpMyAdmin
3. Import `database/smartbus.sql`

---

## Authentication (Phase 3 - Complete)

**Fully functional login, registration, and role-based access control.**

---

## Passenger Module (Phase 4 - Complete)

**Fully working passenger experience:**

---

## Operator Module (Phase 5 - Complete)

**Complete fleet and schedule management for bus operators:**

---

## Admin Module (Phase 6 - Complete)

**Powerful system-wide administration tools:**

---

## Advanced Features (Phase 7 - Complete)

**Enhanced user experience features added:**

- **Visual Seat Selection** — Interactive seat map in booking flow (with booked seat detection)
- **Booking Confirmation Ticket** — Beautiful ticket-style confirmation page after successful booking
- **Notifications System** — In-app notifications with bell icon in header (mark as read)
- **Advanced Search Filters** — Filter by max price and bus type
- **Pagination** — Added to admin user listings and other key tables

---

### Features Delivered
- **Admin Dashboard** — Full system KPIs, recent users, and recent bookings
- **User Management** — Complete CRUD + status control (active/inactive/suspended)
- **Operator Management** — Dedicated view of all operators
- **Booking Management** — View and modify any booking in the system
- **Reports** — Revenue by operator, popular routes, daily booking trends

### Key Files
- `admin/users.php`, `operators.php`, `bookings.php`, `reports.php`
- `actions/admin_user_action.php`, `admin_booking_action.php`

---

### Features Delivered
- **Operator Dashboard** — Real KPIs (buses, upcoming trips, daily bookings, monthly revenue)
- **Bus Management (CRUD)** — Add, edit, delete buses with validation
- **Schedule Management (CRUD)** — Create, edit, and cancel departures
- **Reservations View** — See all passenger bookings across your fleet
- **Routes** — View and add new routes

### Key Files
- `operator/buses.php`, `schedules.php`, `reservations.php`, `routes.php`
- `actions/bus_action.php`, `schedule_action.php`
- Enhanced `get_current_operator()` helper in `includes/auth.php`

---

### Features Delivered
- **Smart Search** — Search buses by origin, destination, and date
- **Booking Flow** — Select schedule → Choose seats → Enter passenger names → Confirm booking
- **My Bookings** — View all bookings with status, cancel upcoming trips
- **Booking Details** — Full ticket-like view with seat assignments
- **Profile Management** — Update name/phone + secure password change
- **Dynamic Dashboard** — Real stats and upcoming trips from database
- **Flash Messages** — Clean success/error notifications throughout

### Key Files
- `passenger/search.php`, `book.php`, `bookings.php`, `profile.php`
- `actions/booking_action.php`, `profile_action.php`
- Reusable flash messages in `includes/auth.php`

---

### Security Features Implemented
- Secure password hashing (`password_hash` + `password_verify`)
- CSRF token protection on all auth forms
- Secure session management (regeneration, httponly, strict mode)
- Role-based access guards (`require_role()`)
- Automatic role-based redirects after login
- Input validation and sanitization
- Protection against session fixation

### Test Accounts (after importing database)
- **Admin**: `admin@smartbus.com` / `Password123`
- **Operator**: `michael@expressbus.com` / `Password123`
- **Passenger**: `james.wilson@email.com` / `Password123`

### Key Files
- `includes/auth.php` — Core authentication & authorization logic
- `actions/login_action.php` & `register_action.php`
- `config/constants.php` — Role constants and helpers

---

## Getting Started (Current Phase)

### Prerequisites
- XAMPP (Apache + MySQL)
- Modern browser (Chrome, Firefox, Edge)
- Git (already initialized)

### Current Testing (Phase 7)

1. **Login as Passenger** and test new features:
   - Go to **Search Buses** → Use price and bus type filters
   - Book a trip → Enjoy the new **visual seat map** selection
   - After booking, see the new **ticket-style confirmation page**
   - Check the bell icon → View **Notifications**

2. **Login as Admin** to see pagination in Users list.

2. **Run the Project**
   ```
   Open browser → http://localhost/SmartBus-Booking-System/
   ```

3. **Test the Following**

   **Landing Page**
   - Visit `index.php`
   - Verify hero section, feature cards, and CTA buttons
   - Check responsive design (resize browser or use mobile dev tools)

   **Navigation**
   - Click "Register" and "Login" from navbar
   - Test hamburger menu on small screens (< 768px)

   **Login Page**
   - Form fields and styling
   - Password visibility toggle (eye icon)
   - "Forgot password" and "Create one now" links

   **Register Page**
   - All fields (including role selector)
   - Form validation (required fields)
   - Password field behavior
   - Terms checkbox

   **CSS Components**
   - Buttons (primary, secondary, success, danger, outline)
   - Cards and alerts
   - Form controls on focus

4. **Known Limitations (Expected)**
   - Forms do not submit yet (backend in Phase 3)
   - Database connection will show friendly error until Phase 2
   - No user sessions or role protection yet

---

## Git Workflow

This project strictly uses Git. Every completed phase receives a clear commit and tag.

```bash
# View history
git log --oneline --graph

# See tags
git tag

# Current phase tag
git show phase-1-project-setup
```

---

## Next Phase

**Phase 2 – Database Design** will be started only after explicit approval.

When ready, reply with:  
**"Approved. Start Phase 2."**

---

## Important Notes for Future Phases

- All database operations will use PDO prepared statements
- Role-based access control will be enforced starting Phase 3
- Never edit `config/db.php` credentials in a way that commits real passwords (use `.env` pattern later if needed)

---

**SmartBus Booking System** – Built to professional standards.

Phase 1 Foundation Complete • Ready for Database Design
