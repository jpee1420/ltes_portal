# Changelog

All notable changes to the **Lipit-Tomeeng Elementary School Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.4.0] - 2026-08-23

### Added
- **Administrator User ID Photo Upload:**
  - Added nullable `avatar` column to `users` table with `UploadUserPhoto` action and `POST /admin/users/{user}/photo` endpoint.
  - Automated replacement and purging of previous avatar files from `public` disk upon new photo upload.
  - Integrated audit trail logging `user.photo_updated` for all photo upload events.
  - Interactive photo upload trigger and avatar thumbnail preview directly within Admin User Management directory (`admin/users/Index.vue`).
- **Portal-Wide Avatar & Identity Display:**
  - Official avatar rendering in application sidebar (`NavUser` / `UserInfo`) for authenticated users.
  - Official student photo display in Learner & Parent Dashboard overview (`student/Dashboard.vue`).
  - Teacher profile avatar in Faculty & Homeroom Dashboard header (`teacher/Dashboard.vue`).
  - Student photo thumbnails in Class Section Roster (`admin/sections/Show.vue`) and Teacher Advisory Roster (`teacher/roster/Index.vue`).
- **Documentation & Tests:**
  - Architecture Decision Record `ADR-007` in `docs/decisions/0007-user-id-photo-upload.md`.
  - Feature test suite `UserPhotoUploadTest` bringing test suite to 132 passing tests (688 assertions).

---

## [0.3.0] - 2026-08-23

### Added
- **Birthday-Based Default Password Formula:**
  - Standardized formula `MMDDYYYY + substr(credential, -4)` implemented in `DefaultPasswordGenerator`.
  - Added nullable `birth_date` to `TeacherProfile` and staff creation form.
  - Updated `CreateStudentAccount`, `CreateStaffAccount`, and `ResetUserPassword` actions.
- **Audit Logging Subsystem & Activity Trail:**
  - Append-only `audit_logs` schema tracking user authentication and operational events.
  - Automatic authentication listeners registered in `AppServiceProvider` for `Login`, `Logout`, `OtherDeviceLogout`, and `Failed`.
  - Application-level audit logging across user creation, password resets, calendar events, daily attendance, and quarterly grades.
  - Dedicated Admin Audit Trail viewer (`admin/audit-log/Index.vue`) with search, filter badges, and pagination.
- **Bulk Student CSV Import:**
  - `BulkImportStudents` action supporting CSV uploads with UTF-8 BOM stripping, header normalization, and row-level validation.
  - Duplicate LRN skip policy with real-time feedback report.
  - Interactive CSV import page (`admin/users/Import.vue`) with drag-and-drop zone, downloadable sample CSV template, section target selector, and 3-tab results breakdown (Created, Duplicates Skipped, Validation Errors).
- **Section Capacity Limits & Warnings:**
  - Nullable `max_students` column added to `sections` table.
  - Helper methods `enrolledCount()` and `isAtCapacity()` on `Section` model.
  - Capacity progress meters and occupancy badges in Admin section list and roster views.
- **Teacher Attendance Carry-Forward:**
  - `GET /teacher/attendance/carry-forward` endpoint returning the most recently recorded attendance records for the adviser's section.
  - Redesigned Daily Attendance UI with master "Select / Deselect All Present" checkbox, individual status toggles, and 1-click carry-forward button.
- **Grade Matrix localStorage Auto-Save:**
  - 800ms debounced local draft auto-saving in `Matrix.vue` keyed by section, subject, and quarter.
  - Automatic draft restoration prompt upon page refresh with amber "● Unsaved Draft" indicator and auto-clearing upon successful server persistence.
- **Documentation & Tests:**
  - Architecture Decision Record `ADR-006` in `docs/decisions/0006-bulk-csv-import-audit-log-and-workflow-enhancements.md`.
  - 21 new automated tests bringing the test suite to 127 passing tests (669 assertions).

---

## [0.2.0] - 2026-08-23

### Added
- **School Calendar Feature:**
  - `calendar_events` database schema supporting 5 event categories (`exam`, `holiday`, `school_event`, `deped_activity`, `section_event`) with optional multi-day date ranges.
  - Role-aware event scoping: Admins manage school-wide events; Teachers manage section-scoped activities for their assigned homeroom; Learners and Parents view all relevant events.
  - Action mutations: `CreateCalendarEvent`, `UpdateCalendarEvent`, and `DeleteCalendarEvent` with role-level ownership validation.
  - Read queries: `GetCalendarEvents` (monthly matrix with month boundary overlap calculations) and `GetUpcomingEvents` (top 5 chronological activities from today).
  - Pure Vue 3 interactive monthly calendar grid (6×7 day matrix with custom previous/next month navigation) and switchable agenda list view.
  - Role-specific calendar pages for Admin (`admin/calendar/Index.vue`), Teacher (`teacher/calendar/Index.vue`), and Student/Parent (`student/calendar/Index.vue`).
  - "Upcoming Events & Activities" card widgets embedded into Admin, Teacher, and Student portal dashboards.
  - Architecture Decision Record `ADR-005` in `docs/decisions/0005-school-calendar-and-event-scoping.md`.
  - Comprehensive automated test coverage: 20 new tests bringing the suite to 106 passing tests (560 assertions).

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
