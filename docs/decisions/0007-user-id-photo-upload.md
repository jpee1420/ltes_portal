# ADR-007: Administrator User ID Photo Upload and Portal Display

## Status
Accepted

## Date
2026-08-23

## Context
School administration, teachers, and learners needed visual identity verification across the Lipit-Tomeeng Elementary School Portal. Specifically:
1. **Administrative Photo Management:** School administrators needed the capability to upload and update official student and staff (teacher/administrator) ID photos directly from the user management directory.
2. **Global Avatar Consistency:** Logged-in users across all roles (Students, Teachers, Admins) needed their official ID photo displayed in the sidebar user profile badge (`NavUser`/`UserInfo`).
3. **Portal Overview Personalization:** Student and Teacher dashboard overviews needed to render official ID photos to establish a personalized, institutional experience.
4. **Class & Section Roster Identification:** Administrators and homeroom teachers needed student photo thumbnails in section rosters for quick visual verification of enrolled learners.

## Decision
1. **Database Schema & Model Representation:**
   - Added a nullable `avatar` column (`string(255)`) to the `users` table via migration `2026_08_23_000004_add_avatar_to_users_table.php`.
   - Updated `User` model fillable attributes and PHPDoc definitions.
   - Because `HandleInertiaRequests` middleware shares the authenticated user object, `auth.user.avatar` is automatically available on the frontend without custom payload transforms.

2. **File Storage & Lifecycle Management:**
   - Uploaded photos are stored on Laravel's `public` disk (`storage/app/public/photos/users/`) under timestamped, randomized filenames (`{user_id}_{timestamp}_{random}.{ext}`).
   - Connected public storage via standard `php artisan storage:link`.
   - `UploadUserPhoto` action automatically deletes the user's previous avatar file when a replacement is uploaded, preventing orphaned disk consumption.
   - Strict server-side validation enforces image mime types (`jpeg,png,jpg,webp`) and maximum file size (2048 KB / 2 MB).

3. **Security & Auditability:**
   - Photo upload endpoint (`POST /admin/users/{user}/photo`, named `admin.users.photo`) is strictly guarded by `['auth', 'role:admin']` middleware. Non-administrators cannot upload or modify photos.
   - Each photo update is logged in the activity trail using `AuditLogger::log('user.photo_updated', ...)`.

4. **Frontend Integration:**
   - **Admin User Management:** Enhanced `Index.vue` with interactive avatar thumbnails and a direct photo upload button/file trigger per row with upload state handling.
   - **Student Dashboard:** Enriched welcome banner in `student/Dashboard.vue` with an official learner avatar display and fallback initials.
   - **Teacher Dashboard:** Updated `teacher/Dashboard.vue` header with the teacher's profile photo.
   - **Section Rosters:** Enhanced `admin/sections/Show.vue` and `teacher/roster/Index.vue` with student avatar thumbnails in class roster tables.

## Consequences
- Administrators can easily upload and maintain official school ID photos.
- Students and teachers see their official school photo immediately across their portal header, sidebar, and rosters.
- All 132 automated tests pass with zero regressions.
