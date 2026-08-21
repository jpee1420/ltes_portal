# ADR-003: Homeroom Adviser Model & DepEd K-6 Academic Structure

## Status
Accepted

## Date
2026-08-22

## Context
Philippine public elementary schools operate under the standard Department of Education (DepEd) curriculum guidelines:
1. **Grade Levels:** Kindergarten and Grades 1 through 6.
2. **Subjects:** 49 standard learning areas defined across Kinder to Grade 6 (Mother Tongue, Filipino, English, Mathematics, Science, Araling Panlipunan, ESP, MAPEH, EPP/TLE).
3. **Advisory / Homeroom Structure:** In elementary schools, a single teacher serves as the homeroom class adviser for an entire section and teaches/manages all subjects for that section.
4. **Active School Year Cycle:** Academic operations are strictly bound to one active `SchoolYear` (e.g. `2026-2027`).

## Decision
1. **Academic Hierarchy:**
   `SchoolYear` (1:N) &rarr; `Section` (N:1 with `GradeLevel`, N:1 with `User [adviser]`, 1:N with `StudentProfile`).
   `GradeLevel` (1:N with `Subject`).
2. **Homeroom Adviser Model:**
   - Each `Section` belongs to an active `SchoolYear` and `GradeLevel`, and has an `adviser_id` referencing a teacher `User`.
   - Teachers only manage learners enrolled in their advisory section for the active school year.
3. **Seeding Strategy:**
   - Pre-seeded 7 Grade Levels (`Kindergarten`, `Grade 1` to `Grade 6`).
   - Pre-seeded all 49 standard DepEd K–6 subjects mapped to their respective grade levels.

## Consequences
- Grade and attendance encoding authorization is simplified and secured: a teacher can only encode grades and attendance for students belonging to their assigned homeroom section.
- School administrators can easily activate a new school year with a single click, instantly scoping dashboards, rosters, and report cards.
