# CLAUDE.md

This file guides Claude Code (claude.ai/code) when it works in this repository.

## Project

This is a course enrollment app with an admin panel, built on **Laravel 8.6** (PHP ^7.3). The admin panel was generated with QuickAdminPanel (CoreUI 2 + Bootstrap 4 + jQuery DataTables). The public site uses the Colorlib "Etrain" theme. The plan is to grow it into a full course-management admin panel. A paid **internship registration** module (see below) now sits alongside the original course enrollment.

## Running locally (MAMP)

- **Use MAMP's PHP 7.4.** The default `php` on PATH is MAMP PHP 8.3, and with it `php artisan` crashes: the locked Laravel 8.6 vendor code isn't compatible with PHP 8.1+.
  ```
  PHP=/Applications/MAMP/bin/php/php7.4.33/bin/php
  $PHP artisan route:list
  $PHP artisan migrate --seed
  $PHP artisan serve
  ```
  Upgrading the framework (to the latest Laravel 8.x or later) would remove this constraint. Treat that upgrade as its own task.
- DB: MySQL via MAMP (`127.0.0.1:8889`). The database name and credentials are in `.env`.
- **Setting up the internship module on an existing DB** (already done on the original dev DB; a fresh `migrate --seed` does all of it automatically):
  ```
  $PHP artisan migrate
  $PHP artisan db:seed --class='\InternshipPermissionsSeeder'
  $PHP artisan db:seed --class='\InternshipsTableSeeder'
  ```
  Then add the Razorpay keys to `.env` (see the module section).
- Seeded logins: `admin@admin.com` / `password` (Admin) and `institution@institution.com` / `password` (Institution).
- Front-end assets: Laravel Mix 4 (`npm run dev` / `npm run prod`). The admin layout loads most CSS/JS from CDNs in `resources/views/layouts/admin.blade.php`, not from Mix.
- Tests: `$PHP vendor/bin/phpunit` (52 tests, all passing). `phpunit.xml` forces in-memory SQLite so tests can never touch the dev MySQL database. Any new test that hits the DB needs `RefreshDatabase`. `tests/Browser/*` are Dusk tests, but Dusk is **not** installed, so they don't run.
  - The internship tests are `tests/Feature/Internship{Crud,Registration,Payment}Test.php`, sharing the fixtures in `InternshipTestCase.php` (not run itself). The base class replaces the real gateway with a fake, so tests never call Razorpay.
  - Under PHPUnit the app is in console mode, so `AuthGates` does not define permission gates. `InternshipTestCase::defineGates()` mirrors it. Flipping the "running in console" flag instead is not an option: it turns CSRF checking on for test requests.
  - The suite was also run once against a scratch MySQL database (a temporary phpunit config pointing at it, since dropped) and passed. To repeat that, never point it at the dev database, because `RefreshDatabase` wipes it.
- Seeding one class: `$PHP artisan db:seed --class='\InternshipPermissionsSeeder'`. The seeders are global classes (classmap), so `--class` needs the leading backslash. After adding a seeder, run `composer dump-autoload` (with the same PHP 7.4).

## Architecture

**Models** live directly in `app/` (Laravel 8 style without `app/Models`): `User`, `Role`, `Permission`, `Institution`, `Discipline`, `Course`, `Enrollment`, plus the internship module's `University`, `College`, `Stream`, `Subject`, `Internship`, `StudentProfile`, `InternshipRegistration` and `Payment`. All use `SoftDeletes` except `InternshipRegistration` and `Payment`, which are never deleted.

**Domain:**
- An `Institution` has many `Course`s. An `Institution` and a `Course` each have a `photo`/logo stored through spatie/laravel-medialibrary v8.
- `Course` ↔ `Discipline` is many-to-many (`course_discipline` pivot).
- An `Enrollment` belongs to a `User` and a `Course`. Its `status` is one of `Enrollment::STATUS_RADIO`: `awaiting` (the default), `accepted` or `rejected`.
- A `User` optionally belongs to an `Institution` (`institution_id`). Institution-role users are tied to one institution this way.
- The internship module's `University` is **not** the same thing as `Institution` (a tenant that owns courses), and `Subject` is not `Discipline` (a course tag). They are separate tables on purpose; don't merge them.

**Roles / authorization:**
- The roles are seeded with fixed IDs: 1 = Admin, 2 = Institution, 3 = Student, 4 = College Admin. Helper methods: `User::isAdmin()`, `User::isInstitution()`, `User::isStudent()`, `User::isCollegeAdmin()`.
- Permissions are DB rows named `{entity}_{access|create|edit|show|delete}`, plus `user_management_access`. `app/Http/Middleware/AuthGates.php` turns each permission into a `Gate` on every web request.
- Authorization checks:
  - Admin controllers check permissions with `abort_if(Gate::denies('course_create'), 403)`.
  - Store and update requests check them in the FormRequest's `authorize()` (`app/Http/Requests/*`).
  - Blade views check them with `@can`.
  - When you add an entity, add its permissions to `PermissionsTableSeeder`. Then update `PermissionRoleTableSeeder`: Institution gets all `course_*` and `enrollment_*` permissions except `enrollment_create`.
- **Multi-tenancy uses global scopes.** `app/Scopes/CourseScope.php`, `EnrollmentScope.php`, and `InternshipRegistrationScope.php` restrict queries to the user's institution when the request path is `admin/*` and the user is an Institution user. The scopes check the request path, so they do **not** apply to `api/*` routes or to console/queue code. Institution users see only their courses and enrollments; Admin users see all data.

**Routes:**
- `routes/web.php`:
  - Public routes: `/`, `courses` (index/show), and the `enroll/*` flow. A guest who enrolls is registered and logged in by `EnrollmentController@store`.
  - `my-courses` is only for logged-in users.
  - The `admin/*` group uses the `Admin` namespace and `auth` middleware. Each entity has the standard resource routes, plus `{entity}/destroy` (a mass delete) and, for entities with photos, `{entity}/media` (Dropzone upload to `storage/tmp/uploads`).
- `routes/api.php`: the same CRUD under `api/v1/*` with `auth:api` (Passport). The controllers are in `app/Http/Controllers/Api/V1/Admin`, and the resources are in `app/Http/Resources/Admin`.

**Conventions for admin CRUD**: the original entities follow the QuickAdminPanel pattern per entity (the internship entities use `BaseCrudController` instead, described below):
- a controller in `Admin/`
- `Store*`, `Update*` and `MassDestroy*` FormRequests
- the views `resources/views/admin/{entity}/{index,create,edit,show}.blade.php`
- a menu entry in `resources/views/partials/menu.blade.php` wrapped in `@can('{entity}_access')`
- labels in `resources/lang/en/cruds.php`; UI strings use `trans('cruds.{entity}.fields.*')` and `trans('global.*')`

Index pages load the whole table (`Model::all()`) and render it with client-side DataTables. `yajra/laravel-datatables-oracle` is installed but unused.

**View composer**: `App\View\Composers\MenuComposer` is registered in `AppServiceProvider` for `partials.header` and supplies the front-end menu's disciplines and institutions. `app/Http/View/Composers/MenuComposer.php` is a stale duplicate (it declares `SidebarComposer`) and is not used.

## Internship registration module

A separate, paid flow for UGC internships. It does not touch the course enroll flow.

- **Public form:** `GET/POST /internship/register`, handled by `InternshipRegistrationController` and validated by `StoreInternshipRegistrationRequest`. It creates a `User` (Student role), a `StudentProfile` (1:1) and an `InternshipRegistration`, then signs the student in and redirects to the payment page.
- **Academic chain:** University → College → Stream → Subject (`universities`, `colleges`, `streams`, `subjects`; each child has its parent's FK). The form's dropdowns load children from the public JSON endpoints `internship/lookup/{colleges,streams,subjects}`, and changing a parent resets all its descendants. The FormRequest also checks that each level belongs to the one above, so don't loosen that.
- **Pricing:** the price lives on `internships` (`price` excludes tax, `tax_percent` defaults to 0 until GST is switched on, plus nullable `duration_weeks` and `is_active`). Registration snapshots price, tax and total, so later edits don't change old records. A total of 0 activates the registration immediately.
- **Payment (Razorpay, INR):**
  - `RazorpayClient` talks to the REST API through Guzzle, and `InternshipPaymentService` creates orders and marks payments paid.
  - Payment is confirmed by the checkout signature (`.../verify`) and by the webhook `POST /internship/razorpay/webhook` (CSRF-exempt; it verifies the HMAC itself).
  - Both paths are idempotent. A registration only becomes `paid` through a verified signature.
  - Config is in `config/razorpay.php`. Set `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET` and `RAZORPAY_WEBHOOK_SECRET` in `.env`. The mode (test/live) comes from the key prefix, so going to production is only a key swap.
- **Admin CRUD:** University, College, Stream, Subject and Internship all extend `Admin\BaseCrudController`. A subclass is just a `config()` array (fields, parent, unique scope, which relations block deletion), and the views are the generic `resources/views/admin/crud/*`. For another simple "name + parent" entity, copy one of those controllers instead of the full QuickAdminPanel set.
  - Permissions follow the usual `{entity}_*` pattern, and `InternshipPermissionsSeeder` is idempotent and grants them to Admin only.
  - `Admin\InternshipRegistrationsController` lists registrations with their payment status.
- **Files, by role:**
  - Migrations: `database/migrations/2026_10_31_00000{1..8}_*`
  - Public views: `resources/views/internship/{register,show}.blade.php`
  - Services: `app/Services/InternshipPaymentService.php` and `app/Services/Razorpay/RazorpayClient.php`
  - Seeders: `InternshipPermissionsSeeder` and `InternshipsTableSeeder` (both idempotent; the second is registered in `DatabaseSeeder`)
  - `Internship::getTaxAmount()` and `getTotalAmount()` hold the price math.
- **Form fields:** name, email, mobile and WhatsApp (10-digit Indian numbers), guardian name, gender, password (min 8), programme (an active `Internship`), the academic chain, class/semester, year of study, registration number, roll number and academic session. The fixed option lists are constants/methods on `StudentProfile` (`GENDER_SELECT`, `classSemesterOptions()` and so on). The session list is computed from the current year. There is no marksheet field, because the student receives a certificate of internship.
- **Status page:** `GET /internship/registration/{registration}` (owner only) shows the summary and the Pay button. `POST .../order` creates or reuses the Razorpay order from the server-side amount, and `POST .../verify` checks the signature. A signed-in student with an existing registration who opens the form is redirected to this page.
- **Gotchas:**
  - The unique `name` rules ignore soft-deleted rows and the DB indexes on `name` are deliberately not unique, so a deleted name can be reused. Uniqueness for College, Stream and Subject is per parent.
  - The Etrain theme's `custom.js` turns every `<select>` into a nice-select widget. The registration form destroys it on page load so the dynamic dropdowns work.
  - The webhook route is listed in `VerifyCsrfToken::$except`.
  - `User::studentProfile()` and `User::internshipRegistrations()` were added to the existing `User` model.
- **Seed data:** two placeholder internships (₹999 and ₹1499, no duration) were seeded into the dev DB. The University/College/Stream/Subject tables start empty, and the admin adds that data.
- **Not yet verified / open items:**
  - The registration form's JavaScript (the cascade, the WhatsApp "same as mobile" box, the price hint) and the Razorpay checkout popup have not been exercised in a real browser. The server side and routes are covered by tests.
  - Razorpay keys are not in `.env` yet, so the Pay button reports the gateway as unavailable until they are added. The webhook URL and the `payment.captured` / `payment.failed` events must be configured in the Razorpay dashboard (a tunnel such as ngrok is needed locally).
  - There is no link to `/internship/register` from the public site menu, and no email on registration or payment.
  - Tax is off (0%). Prices are placeholders to be set by the admin.
- **Planned next:** attendance and class creation. Hook them to `internships` (a class belongs to an internship) and to `internship_registrations` with status `paid` (attendance). `internship_registrations.certificate_number` is reserved for certificates and certificate verification.

## Admin Dashboard & College Login

The dashboard (`Admin\HomeController`, `resources/views/admin/home.blade.php`) now displays role-appropriate statistics with three-level user hierarchy:

- **Admin users** see: total users, institutions, courses, enrollments, internship programs, registrations, paid/pending registrations, total revenue (₹), and recent registration activity (last 10).
- **Institution users** see: their users, courses, accepted and pending enrollments.
- **College admin users** (NEW) see: students in their college, total registrations from college, paid/pending breakdown, and recent registrations from that college only.

**College Login Implementation:**
- College Admin users have role ID 4 and a `college_id` set, allowing them to see only registrations from their college.
- `StudentProfile.college_id` is used for filtering (links students to their college).
- Scope filters: InternshipRegistrations where `studentProfile.college_id = auth()->user()->college_id`.
- Migrations added `college_id` foreign key to `users` table.
- Model relationships: User→college(), College→studentProfiles(), College→collegeAdmins().
- Permissions: `InternshipPermissionsSeeder` grants College Admin read-only access to internship management resources (`*_access` and `*_show` permissions).

## Which roles a role may assign

Each role has a list of roles it may assign (`role_assignable` pivot, `Role::assignableRoles()`), edited with checkboxes on the role create/edit page. `User::assignableRoleIds()` is the single source: **Admin (role 1) always gets every role**, other roles only their ticked ones (none ticked = none). It limits the Role dropdown on the user forms, rejects other roles server-side (`StoreUserRequest`/`UpdateUserRequest`), and `User::canManage()` also hides, blocks editing and deleting users who hold any role the actor could not assign (users index/show/edit/update/destroy/massDestroy). The migration backfilled "all roles" for every role that already had `user_create`/`user_edit`, so nothing changed until narrowed. Roles created later start with none ticked.

## Classes and college logos

- **College logo:** `colleges.logo` (path on the `public` disk, folder `colleges/`). `BaseCrudController` has an `image` field type (JPG/PNG/WEBP up to 2 MB, SVG deliberately rejected; replacing a file deletes the old one; editing without a file keeps it). `College::logo_url` gives the URL, and the student welcome letter shows it. **Needs `php artisan storage:link`** (done locally; do it on the server too).
- **Classes:** `InternshipClass` (`internship_classes`; not `Class`, a reserved word) belongs to an `Internship`, so the college comes from `internship.college_id`. Admin CRUD in `Admin\InternshipClassesController` (own views in `resources/views/admin/internship_classes/`, menu entry under Internship management).
  - Recurrence: `none` (one-time; `end_date` is forced to `start_date`), `daily`, or `weekly` with `days_of_week` stored as ISO weekday numbers, e.g. `1,3,5` (Mon..Sun = 1..7). Recurring classes require `end_date`. Times are `start_time`/`end_time` (`H:i`), shown in 12-hour format.
  - Sessions are **computed, not stored**: `InternshipClass::occurrences()`, `occursOn()`, `totalSessions()`. When attendance is built it should key on (class id, date).
  - Super admin sees all classes, filters by university/college, and creates a class on behalf of a college by picking the college and then one of its internships. College admins see and manage only their own college's classes (`InternshipClassScope`), and posting another college's internship fails validation.
  - **WhatsApp:** the class page lists students with a *paid* registration for the internship (so an offline registration must be marked paid first) with a `wa.me` click-to-chat button each, plus a copy-able group message. No API, nothing is sent automatically. Message text is `InternshipClass::whatsappMessage()`; numbers come from `StudentProfile::whatsappPhone()` (WhatsApp number, falling back to mobile, normalised to `91XXXXXXXXXX`). The sign-off uses `panel.site_title`. The "Sent" marker is kept only in the browser (localStorage). The API version (templates, consent, delivery log) is a possible later step.
  - Permissions `internship_class_{access,create,edit,show,delete}` are created by `InternshipClassPermissionsSeeder` (additive, granted to Admin and College Admin). Students do not see classes yet; "My Classes" for the student portal is the obvious next step.
- **Schema fix:** `users`, `courses` and `institutions` `.college_id` had only been added to the dev DB by hand. Migration `2026_11_02_000003` now creates them (skipped if they exist), so a fresh `migrate --seed` works. `CollegeAdminsTableSeeder` is no longer in `DatabaseSeeder` (demo data that also needs a `universities.code` column that does not exist).

## Student portal

Student-only accounts (`User::isStudentOnly()`: Student is their single role) use the same admin layout, but the sidebar shows only **My Profile** and **Welcome Letter**, and `/home` and `/admin` redirect to `admin/my-profile`. Both pages are in `Admin\StudentPortalController` with views under `resources/views/admin/student/`. The welcome letter is shown only for paid registrations and has a print/save-as-PDF button.

## Admin UI Styling

The admin panel uses a modern dark-sidebar design defined in `public/css/admin-modern.css`:

**Layout:**
- Fixed header (70px height) at the top with z-index 1001
- Fixed left sidebar (260px width) below header, dark theme (#1e293b background)
- Main content area fills remaining space with responsive width calculations
- Sidebar collapses/expands with smooth transitions (0.3s)
- Content expands to full width when sidebar is hidden
- Tables and pagination properly sized and scrollable

**Color scheme:**
- Primary accent: #22c55e (green)
- Dark sidebar: #1e293b
- Light background: #f8fafc
- Text primary: #1e293b
- Text secondary: #64748b

**Components:**
- Buttons: Primary (green), Danger (red), Info (blue), Warning (orange), Secondary (gray)
- Cards: White background with subtle shadow, 8px border radius
- Forms: Clean input styling with border and focus states
- Tables: DataTables with alternating row colors and hover effects
- Badges: Color-coded status indicators (success/danger/warning/info)
- Alerts: Left-border accent with status colors

**Responsive behavior:**
- Desktop (≥993px): Fixed sidebar with 260px width, main content takes remaining space
- Mobile (<993px): Sidebar slides from left, overlays main content
- All pagination controls and form elements remain accessible at all breakpoints

## Known gaps / enhancement candidates

- Users who self-register through the *course* enroll flow get no Student role. The internship flow does assign it.
- Nothing prevents a user from enrolling in the same course twice.
- The course create/edit forms list **all** institutions, including for Institution users. Course IDs in `massDestroy` are scoped only through `CourseScope`.
- No enrollment status workflow (accept/reject actions, notifications) beyond editing the radio field.
- Lists aren't paginated server-side, and there is no search or filtering beyond DataTables.
- Students who log in after the internship flow land on the admin home (`/home` redirects there), which shows them nothing useful. A student dashboard is needed, and the planned attendance and class features will want one.
- A student who abandons payment and later registers again with the same email is rejected as a duplicate. They must log in and use their status page (the form links to Log in).
