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

| Phase | Name                        | Status      | Git Tag                  |
|-------|-----------------------------|-------------|--------------------------|
| 1     | **Project Setup**           | ✅ Complete | `phase-1-project-setup`  |
| 2     | Database Design             | ⏳ Pending   | -                        |
| 3     | Authentication System       | ⏳ Pending   | -                        |
| 4     | Passenger Module            | ⏳ Pending   | -                        |
| 5     | Operator Module             | ⏳ Pending   | -                        |
| 6     | Admin Module                | ⏳ Pending   | -                        |
| 7     | Advanced Features           | ⏳ Pending   | -                        |
| 8     | Final Optimization          | ⏳ Pending   | -                        |

> **Latest Phase**: Phase 1 completed and committed to Git.

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
└── database/                 # Future SQL scripts (Phase 2)
    └── .gitkeep
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

## Getting Started (Current Phase)

### Prerequisites
- XAMPP (Apache + MySQL)
- Modern browser (Chrome, Firefox, Edge)
- Git (already initialized)

### Current Testing (Phase 1)

1. **Start Services**
   - Open XAMPP Control Panel
   - Start **Apache** (MySQL not required yet)

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
