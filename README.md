# Lipit-Tomeeng Elementary School Portal

> Official digital academic management portal for **Lipit-Tomeeng Elementary School**, built with **Laravel 13**, **Vue 3**, **Inertia.js v3**, **Tailwind CSS v4**, and **MySQL**.

---

## 🏛️ Overview

The Lipit-Tomeeng Elementary School Portal is a centralized web platform designed to streamline elementary school operations, academic grading, daily attendance tracking, school event schedules, and parent communication following Department of Education (DepEd) standards.

### ✨ Three Dedicated Portals

1. **👑 Administrator Portal:**
   - Manage academic cycles and activate school years (`2026-2027`).
   - Create and configure class sections (Kindergarten through Grade 6), set learner capacity limits, and assign teacher advisers.
   - User account directory: search, filter, register students (12-digit LRN) and staff (7-digit Employee No), upload/replace official ID photos, and reset passwords using the Birthday formula (`MMDDYYYY + last4`).
   - Bulk Student CSV Import: import student batches from spreadsheets with duplicate LRN skip reporting and validation summaries.
   - Immutable Audit Log: monitor security actions, logins, logouts, session timeouts, photo uploads, and state-mutating portal transactions.
   - Section roster enrollment: enroll and unassign learners to/from class sections with capacity warnings.
   - School-Wide Calendar Management: schedule DepEd activities, periodic examinations, holidays, and campus programs.

2. **👩‍🏫 Faculty / Class Adviser Portal:**
   - Homeroom class roster overview with student profiles.
   - Interactive Quarterly Grade Matrix (DepEd subjects, Q1–Q4) with live recalculation, visual fail indicators ($< 75$), and **localStorage draft auto-save**.
   - Daily Attendance tracker with "Carry Forward Previous Day" 1-click action, master select/deselect all checkboxes, status pills (Present, Absent, Tardy, Excused), and remarks.
   - Homeroom Calendar: post section quizzes, project deadlines, and class announcements for advisory learners.

3. **🎒 Learner & Parent Portal:**
   - DepEd Form 138-E Progress Report Card with quarterly scores, final subject ratings, quarterly general averages, and DepEd performance descriptors.
   - Attendance summary KPI cards (Attendance Rate %, Days Present, Days Absent, Tardy, Excused) and chronological presence logs.
   - School & Class Calendar: interactive monthly grid and agenda list displaying all school-wide activities and section-specific announcements.

---

## 🚀 Quick Start

### 1. Prerequisites
- **PHP 8.2+** (tested on PHP 8.5)
- **Node.js 20+** & **npm**
- **Composer**
- **Docker / Laravel Sail** (or local MySQL/SQLite)

### 2. Installation
```bash
# Clone the repository
git clone <repo-url> ltes_portal
cd ltes_portal

# Install backend dependencies
composer install

# Install frontend dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run database migrations and seed standard DepEd curriculum, sample calendar, & accounts
php artisan migrate:fresh --seed
```

### 3. Run Development Server
```bash
# In one terminal:
composer run dev

# Or separately:
php artisan serve
npm run dev
```

---

## 🔑 Default Credentials (Seeded)

The portal uses unique numeric identification credentials for seamless login:

| Role | Name | Credential (Login) | Default Password Formula | Portal Path |
|---|---|---|---|---|
| **Administrator** | School Administrator | `1000001` | `1000001` (or `MMDDYYYY` + last 4) | `/admin/dashboard` |
| **Teacher (Adviser)** | Maria Santos | `2000001` | `2000001` (or `MMDDYYYY` + last 4) | `/teacher/dashboard` |
| **Student / Parent** | Juan Dela Cruz | `101010101001` | `101010101001` (or `MMDDYYYY` + last 4) | `/student/dashboard` |

> [!NOTE]
> Accounts are created with default passwords matching the Birthday formula (`MMDDYYYY` + last 4 digits of LRN or Employee No). Users can update their password in Account Settings, and administrators can reset forgotten passwords back to the default formula with a single click.

---

## 🛠️ CLI Commands

| Command | Description |
|---|---|
| `php artisan test --compact` | Run Pest test suite (127+ tests) |
| `vendor/bin/pint --dirty --format agent` | Format PHP code according to project style standards |
| `npm run build` | Compile production frontend assets |
| `php artisan wayfinder:generate` | Regenerate TypeScript route actions |
| `php artisan route:list --except-vendor` | Inspect registered application routes |
| `php artisan db:seed` | Seed default curriculum, subjects, sample calendar events, and users |

---

## 🏗️ Architecture & Decisions

This project follows a **Feature-Driven Vertical Slice Architecture**:
- **Mutations (Writes):** Encapsulated in single-purpose Action classes under `app/Actions/<Feature>/`.
- **Aggregations (Reads):** Encapsulated in performant Query classes under `app/Queries/<Feature>/`.
- **Controllers:** Thin controllers grouped by authorization role under `app/Http/Controllers/<Role>/`.
- **Frontend:** Modular Vue 3 pages grouped by role and feature under `resources/js/pages/<role>/<feature>/`.

### Architecture Decision Records (ADRs)
Detailed technical rationale and trade-offs are documented in [`docs/decisions/`](docs/decisions/):
- [**ADR-001:** Credential-Based Authentication (12-Digit LRN & 7-Digit Employee Number)](docs/decisions/0001-credential-based-authentication.md)
- [**ADR-002:** Feature-Driven Vertical Slice Architecture](docs/decisions/0002-vertical-slice-feature-architecture.md)
- [**ADR-003:** Homeroom Adviser Model & DepEd K-6 Academic Structure](docs/decisions/0003-academic-structure-and-homeroom-model.md)
- [**ADR-004:** DepEd Form 138-E Grading Rules, Passing Threshold 75, and Daily Attendance Matrix](docs/decisions/0004-deped-grading-scale-and-daily-attendance.md)
- [**ADR-005:** School Calendar & Role-Aware Event Scoping](docs/decisions/0005-school-calendar-and-event-scoping.md)
- [**ADR-006:** Bulk CSV Import, Audit Trail, and Portal Workflow Enhancements](docs/decisions/0006-bulk-csv-import-audit-log-and-workflow-enhancements.md)
- [**ADR-007:** Administrator User ID Photo Upload and Portal Display](docs/decisions/0007-user-id-photo-upload.md)

---

## 🧪 Testing

Every feature is covered with automated tests using **Pest PHP**:
```bash
php artisan test --compact
```
- **132 feature and unit tests**
- **688 assertions**
- 100% test passing rate across Authentication, User Management, ID Photo Upload, Bulk CSV Import, Audit Trail, Academic Structure, Sections, Grading, Attendance, School Calendar, and Dashboards.
