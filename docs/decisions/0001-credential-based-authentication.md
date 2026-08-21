# ADR-001: Credential-Based Authentication (12-Digit LRN & 7-Digit Employee Number)

## Status
Accepted

## Date
2026-08-22

## Context
Lipit-Tomeeng Elementary School requires a centralized portal serving three distinct user roles:
1. **School Administrators**
2. **Teachers / Faculty (Homeroom Advisers)**
3. **Students & Parents (Shared family account per student)**

Standard web applications typically authenticate users via email addresses or usernames. However, in the Philippine Department of Education (DepEd) ecosystem:
- Elementary students do not universally possess or remember email addresses. Every enrolled student in the Philippines is officially issued a unique **12-digit Learner Reference Number (LRN)** by DepEd.
- Teaching faculty and administrative staff are issued standard **7-digit Employee Numbers**.
- Parents log in using their child's LRN to monitor quarterly grades (Form 138-E) and daily attendance.

## Decision
1. **Single Unified `credential` Column:**
   - Modified the `users` table to replace standard email-only login with a unique `credential` column alongside an explicit `role` enum (`admin`, `teacher`, `student`).
   - `credential` is indexed and stores either a 12-digit LRN (for students) or a 7-digit employee number (for teachers and admins).
2. **Fortify Custom Authentication Pipeline:**
   - Implemented `App\Actions\Fortify\AuthenticateWithCredential` to authenticate users against `credential` + `password`.
   - Maintained fallback support for email login for staff and administrative services.
3. **Default Initial Passwords & Reset Action:**
   - Initial default passwords match the user's credential number (e.g., LRN-as-password for students, Employee-No-as-password for staff).
   - Administrators have a dedicated `ResetUserPassword` action to revert lost passwords back to default credentials.
4. **Role Isolation:**
   - Role-specific route middleware `EnsureRole` (`role:admin`, `role:teacher`, `role:student`) guarding dedicated route files `routes/admin.php`, `routes/teacher.php`, and `routes/student.php`.

## Alternatives Considered

### Alternative A: Separate Tables for Students, Teachers, and Admins (`students`, `teachers`, `admins`)
- **Pros:** Total table separation.
- **Cons:** Complicates session management, Fortify auth guards, password resets, and user management.
- **Rejected:** Unified `users` table with 1-to-1 profile tables (`student_profiles`, `teacher_profiles`) allows unified authentication while keeping domain-specific attributes cleanly normalized.

### Alternative B: Email-only Authentication
- **Pros:** Standard Laravel Breeze/Fortify default.
- **Cons:** Unusable for kindergarten through elementary learners and low-tech parents in public schools.
- **Rejected:** Violates primary operational requirements of DepEd elementary schools.

## Consequences
- Single login form (`/login`) accepts 12-digit LRN or 7-digit Employee Number seamlessly.
- Role-based redirect via `LoginResponse` directs users instantly to `/admin/dashboard`, `/teacher/dashboard`, or `/student/dashboard`.
- Strict validation rules ensure 12 digits for student accounts and 7 digits for faculty accounts.
