# CLAUDE.md

This file guides Claude Code (claude.ai/code) when it works in this repository.

## Project

This is a course enrollment app with an admin panel, built on **Laravel 8.6** (PHP ^7.3). The admin panel was generated with QuickAdminPanel (CoreUI 2 + Bootstrap 4 + jQuery DataTables). The public site uses the Colorlib "Etrain" theme. The plan is to grow it into a full course-management admin panel.

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
- Seeded logins: `admin@admin.com` / `password` (Admin) and `institution@institution.com` / `password` (Institution).
- Front-end assets: Laravel Mix 4 (`npm run dev` / `npm run prod`). The admin layout loads most CSS/JS from CDNs in `resources/views/layouts/admin.blade.php`, not from Mix.
- Tests: `$PHP vendor/bin/phpunit`. Only example tests exist. `tests/Browser/*` are Dusk tests, but Dusk is **not** installed, so they don't run.

## Architecture

**Models** live directly in `app/` (Laravel 8 style without `app/Models`): `User`, `Role`, `Permission`, `Institution`, `Discipline`, `Course`, `Enrollment`. Every model uses `SoftDeletes`.

**Domain:**
- An `Institution` has many `Course`s. An `Institution` and a `Course` each have a `photo`/logo stored through spatie/laravel-medialibrary v8.
- `Course` ↔ `Discipline` is many-to-many (`course_discipline` pivot).
- An `Enrollment` belongs to a `User` and a `Course`. Its `status` is one of `Enrollment::STATUS_RADIO`: `awaiting` (the default), `accepted` or `rejected`.
- A `User` optionally belongs to an `Institution` (`institution_id`). Institution-role users are tied to one institution this way.

**Roles / authorization:**
- The roles are seeded with fixed IDs: 1 = Admin, 2 = Institution, 3 = Student. `User::isInstitution()` hardcodes role id `2`.
- Permissions are DB rows named `{entity}_{access|create|edit|show|delete}`, plus `user_management_access`. `app/Http/Middleware/AuthGates.php` turns each permission into a `Gate` on every web request.
- Authorization checks:
  - Admin controllers check permissions with `abort_if(Gate::denies('course_create'), 403)`.
  - Store and update requests check them in the FormRequest's `authorize()` (`app/Http/Requests/*`).
  - Blade views check them with `@can`.
  - When you add an entity, add its permissions to `PermissionsTableSeeder`. Then update `PermissionRoleTableSeeder`: Institution gets all `course_*` and `enrollment_*` permissions except `enrollment_create`.
- **Multi-tenancy uses global scopes.** `app/Scopes/CourseScope.php` and `EnrollmentScope.php` restrict queries to the user's institution when the request path is `admin/*` and the user is an Institution user. The scopes check the request path, so they do **not** apply to `api/*` routes or to console/queue code.

**Routes:**
- `routes/web.php`:
  - Public routes: `/`, `courses` (index/show), and the `enroll/*` flow. A guest who enrolls is registered and logged in by `EnrollmentController@store`.
  - `my-courses` is only for logged-in users.
  - The `admin/*` group uses the `Admin` namespace and `auth` middleware. Each entity has the standard resource routes, plus `{entity}/destroy` (a mass delete) and, for entities with photos, `{entity}/media` (Dropzone upload to `storage/tmp/uploads`).
- `routes/api.php`: the same CRUD under `api/v1/*` with `auth:api` (Passport). The controllers are in `app/Http/Controllers/Api/V1/Admin`, and the resources are in `app/Http/Resources/Admin`.

**Conventions for admin CRUD**: follow the existing QuickAdminPanel pattern per entity:
- a controller in `Admin/`
- `Store*`, `Update*` and `MassDestroy*` FormRequests
- the views `resources/views/admin/{entity}/{index,create,edit,show}.blade.php`
- a menu entry in `resources/views/partials/menu.blade.php` wrapped in `@can('{entity}_access')`
- labels in `resources/lang/en/cruds.php`; UI strings use `trans('cruds.{entity}.fields.*')` and `trans('global.*')`

Index pages load the whole table (`Model::all()`) and render it with client-side DataTables. `yajra/laravel-datatables-oracle` is installed but unused.

**View composer**: `App\View\Composers\MenuComposer` is registered in `AppServiceProvider` for `partials.header` and supplies the front-end menu's disciplines and institutions. `app/Http/View/Composers/MenuComposer.php` is a stale duplicate (it declares `SidebarComposer`) and is not used.

## Known gaps / enhancement candidates

- The admin dashboard (`Admin\HomeController`, `admin/home.blade.php`) only shows "You are logged in!". It has no stats.
- Users who self-register through the enroll flow get no Student role.
- Nothing prevents a user from enrolling in the same course twice.
- The course create/edit forms list **all** institutions, including for Institution users. Course IDs in `massDestroy` are scoped only through `CourseScope`.
- No enrollment status workflow (accept/reject actions, notifications) beyond editing the radio field.
- Lists aren't paginated server-side, and there is no search or filtering beyond DataTables.
