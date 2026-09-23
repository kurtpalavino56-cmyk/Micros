UPSKILL UI CLEANUP — DIRECT COPY PACKAGE

Copy the files in this package into the matching paths of your Laravel project.
The folder structure mirrors resources/, app/, and routes/.

FILES TO DELETE AFTER COPYING THE PATCHES:
- resources/views/faculty/badges/create.blade.php
- resources/views/faculty/certificates/create.blade.php
- resources/views/faculty/profile-dedicated.blade.php

IMPORTANT:
- The dedicated certificate/badge routes and controller methods have already been removed in the supplied patched routes/web.php and FacultyController.php.
- Do not delete saveInlineBadge() or the inline badge builder.
- Do not replace resources/views/layouts/app.blade.php.

RECOMMENDED AFTER COPYING:
php artisan view:clear
php artisan route:clear

Then test:
1. Faculty dashboard
2. My Courses
3. Create Courses
4. Enrolled Students
5. Inbox
6. Analytics
7. Profile
8. Admin dashboard
9. Admin courses
10. Admin users
11. Admin pathways
