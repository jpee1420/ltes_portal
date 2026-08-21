# ADR-002: Feature-Driven Vertical Slice Architecture for Laravel & Vue 3

## Status
Accepted

## Date
2026-08-22

## Context
As the elementary school portal scales to handle enrollment, grading, attendance, schedules, school year transitions, and reporting, a traditional monolithic MVC folder structure (`app/Http/Controllers/` with dozens of unrelated controllers) leads to high coupling and cognitive overhead.

We evaluated three potential structural patterns:
1. **Direction A (Full Domain Isolation):** All layers (Models, Policies, Actions, Queries, Controllers) strictly encapsulated in feature folders (e.g. `app/Domains/Grades/Models/Grade.php`).
2. **Direction B (Feature-Driven Vertical Slice):** Shared flat `app/Models/` and `app/Policies/`, but feature-grouped `app/Actions/<Feature>/` (write mutations), `app/Queries/<Feature>/` (complex read aggregations), role-scoped `app/Http/Controllers/<Role>/`, and feature-grouped Vue pages (`resources/js/pages/<role>/<feature>/`).
3. **Direction C (Traditional Flat Laravel MVC):** Flat controllers and services.

## Decision
Adopt **Direction B (Feature-Driven Vertical Slice Architecture)**:
- **Write Operations:** Encapsulated in single-responsibility Action classes under `app/Actions/<Feature>/` (e.g., `CreateStudentAccount`, `ActivateSchoolYear`, `BulkEncodeGrades`, `EncodeDailyAttendance`).
- **Read Aggregations & Matrices:** Encapsulated in Query classes under `app/Queries/<Feature>/` (e.g., `GetSectionGradeMatrix`, `FetchStudentReportCard`, `GetStudentAttendanceSummary`).
- **Role HTTP Controllers:** Grouped strictly by authorization role under `app/Http/Controllers/<Role>/` (`Admin/`, `Teacher/`, `Student/`).
- **Data Models:** Kept flat under `app/Models/` to prevent premature domain boundaries while core entities (`User`, `StudentProfile`, `SchoolYear`, `Grade`, `AttendanceRecord`) are actively shared across features.
- **Frontend Pages:** Grouped by role and feature under `resources/js/pages/<role>/<feature>/`.

## Alternatives Considered

### Full Domain Isolation (Direction A)
- **Pros:** Maximum module isolation.
- **Cons:** High friction for Eloquent relationships, cross-domain queries (e.g., Student report card needing Section, Subject, SchoolYear, and Grade models), and boilerplate overhead.
- **Rejected:** Overkill for an elementary school portal MVP.

### Traditional Flat MVC (Direction C)
- **Pros:** Standard default Laravel structure.
- **Cons:** Bloated controller actions, logic duplication between API/web, hard to test mutations in isolation.
- **Rejected:** Leads to maintenance bottlenecks.

## Consequences
- Every state mutation has a dedicated, unit-testable action class.
- Complex tabular views (such as quarterly grade matrices and Form 138-E report cards) have dedicated, performant query classes.
- Controllers remain extremely thin, handling only input validation and Inertia response delegation.
