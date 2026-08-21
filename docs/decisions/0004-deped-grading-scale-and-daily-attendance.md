# ADR-004: DepEd Form 138-E Grading Rules, Passing Threshold 75, and Daily Attendance Matrix

## Status
Accepted

## Date
2026-08-22

## Context
DepEd elementary grading policies mandate:
1. **Grading Periods:** Four quarterly grading periods (`Q1`, `Q2`, `Q3`, `Q4`).
2. **Score Range:** Raw scores from 60 to 100.
3. **Passing Grade:** Exactly **75** is the minimum passing mark (`score >= 75` is Passed, `< 75` is Failed).
4. **General Descriptors:**
   - $90 - 100$: Outstanding (O)
   - $85 - 89$: Very Satisfactory (VS)
   - $80 - 84$: Satisfactory (S)
   - $75 - 79$: Fairly Satisfactory (FS)
   - Below $75$: Did Not Meet Expectations (Failed)
5. **Daily Attendance Statuses:** `present`, `absent`, `tardy`, `excused`.

## Decision
1. **Grades Table Schema:**
   - Table `grades`: `(student_id, subject_id, school_year_id, quarter)` with unique composite index.
   - `score` stored as an unsigned tiny integer with validation range `60–100`.
   - `Grade::isPassing()` returns `$this->score >= 75`.
2. **Form 138-E Report Card Calculation:**
   - Handled server-side in `FetchStudentReportCard` query class.
   - Calculates subject final ratings (average of available quarters), quarterly general averages, overall final general average, and official DepEd performance descriptor.
3. **Interactive Teacher Grading Matrix:**
   - `teacher/grades/Matrix.vue` renders spreadsheet-style tabular input per subject with live recalculation and visual warning highlights on failing marks ($< 75$).
4. **Attendance Recording:**
   - Table `attendance_records`: `(student_id, date)` unique composite index.
   - Teachers record daily homeroom attendance with quick "Mark All Present" button, per-student status pills, and optional remarks.
   - Student dashboard and attendance view aggregate attendance rate percentage and chronological logs.

## Consequences
- Grade encoding is idempotent (`updateOrCreate`) and safe to edit and re-save.
- Students and parents have transparent, instantaneous access to Form 138-E learning progress without waiting for paper distribution.
- Attendance rates are calculated automatically across the academic calendar.
