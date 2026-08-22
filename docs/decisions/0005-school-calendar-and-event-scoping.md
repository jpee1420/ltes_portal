# ADR-005: School Calendar & Role-Aware Event Scoping

## Status
Accepted

## Date
2026-08-23

## Context
While the Lipit-Tomeeng Elementary School Portal successfully manages quarterly grading, section rosters, and daily attendance, stakeholders (administrators, faculty advisers, and student/parent families) lacked a centralized source of truth for upcoming academic milestones, exam weeks, DepEd-mandated programs, and school holidays.

The requirements for this feature included:
1. **School-Wide Events:** DepEd activities, quarterly examination periods, holidays, and campus programs managed by Administrators.
2. **Section-Scoped Events:** Class quizzes, project deadlines, and homeroom announcements managed by Teachers for their assigned advisory sections.
3. **Learner / Parent Visibility:** Students and parents can access all school-wide events plus announcements specifically posted for their enrolled section.
4. **Dual Presentation Views:** A full interactive monthly grid view (hand-rolled with 0 external calendar dependencies) and a chronological agenda list view, paired with an "Upcoming Events" widget across all three portal dashboards.

## Decision
1. **Database Schema:**
   - Table `calendar_events`: `(title, description, category, start_date, end_date, school_year_id, section_id, created_by)`.
   - `category` cast to backed string enum `EventCategory` (`exam`, `holiday`, `school_event`, `deped_activity`, `section_event`).
   - If `section_id === null`, the event is globally school-wide.
   - If `section_id` is populated, the event is scoped exclusively to that section.
2. **Authorization & Policies:**
   - `CalendarEventPolicy` allows Admins to manage all event categories and edit/delete any event.
   - Teachers are constrained to `section_event` category for their own advisory section only and can only edit/delete their own events.
   - Students have read-only access strictly to school-wide events and events matching their enrolled section.
3. **Query & Action Slices:**
   - Write operations encapsulated in `CreateCalendarEvent`, `UpdateCalendarEvent`, and `DeleteCalendarEvent` actions.
   - Presentation aggregations encapsulated in `GetCalendarEvents` (monthly query with overlap handling) and `GetUpcomingEvents` (top 5 upcoming from current date for dashboard widgets).
4. **Frontend Implementation:**
   - Lightweight, zero-dependency Vue 3 calendar component generating a 6×7 day matrix with month navigation via Inertia query parameters (`?year=&month=`) to preserve browser history.
   - Color-coded category tags with semantic badges and modal inspection/editing.

## Consequences
- No external heavy calendar libraries (FullCalendar, etc.) were introduced, keeping the bundle size minimal.
- Section privacy is guaranteed: students and parents never see tests or notes from other sections.
- Dashboard load times remain fast via the lightweight `GetUpcomingEvents` query.
