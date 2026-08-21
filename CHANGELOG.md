# Changelog

All notable changes to the **Lipit-Tomeeng Elementary School Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.1.0] - 2026-08-22

### Added
- **Authentication & Security:**
  - Credential-based authentication supporting 12-digit LRN for students and 7-digit Employee Numbers for staff/admins via `AuthenticateWithCredential` Fortify action.
  - Role-based redirect via `LoginResponse` and route authorization guards via `EnsureRole` middleware.
  - Admin one-click password reset to default credential numbers via `ResetUserPassword`.
- **User Management Slice:**
  - Admin student account creation with 12-digit numeric regex and uniqueness validation.
  - Admin faculty/staff account creation with 7-digit employee number validation.
  - Searchable, filterable user directory table with role badges and pagination.
- **Academic Structure:**
  - School year management with one-click activation.
  - Standard DepEd curriculum database seeders with 7 Grade Levels (Kindergarten to Grade 6) and 49 learning areas.
  - Class section CRUD with teacher adviser assignment and student roster enrollment/unassignment.
- **Grades & Assessment (DepEd Form 138-E):**
  - Database schema for quarterly subject scores with unique constraint on `(student_id, subject_id, school_year_id, quarter)`.
  - Passing grade threshold established at 75 (`Grade::isPassing()`).
  - Teacher quarterly grade encoding matrix with live spreadsheet inputs and visual fail indicators.
  - Student & Parent Form 138-E report card view with quarterly general averages and official DepEd performance descriptors.
- **Daily Attendance Tracker:**
  - Daily homeroom attendance recording with date picker, "Mark All Present" shortcut, status pills (`present`, `absent`, `tardy`, `excused`), and remarks.
  - Student attendance summary KPI cards (attendance rate %, present, absent, tardy) and chronological date log.
- **Portals & Dashboards:**
  - Administrator dashboard with KPI metrics, section breakdown, and recent learner registrations.
  - Teacher dashboard with homeroom advisory card and today's attendance snapshot.
  - Student / Parent dashboard with academic highlights and attendance summary.
  - Dynamic role-aware sidebar navigation in `AppSidebar.vue` and `AppHeader.vue`.
- **Documentation & Architecture:**
  - Architecture Decision Records (`ADR-001` through `ADR-004`) in `docs/decisions/`.
  - Comprehensive `README.md` and complete Pest test suite (86 tests, 443 assertions).
