# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

All PHP/artisan commands run inside Docker. You can use the `aa` alias. Composer runs locally.

```bash
# Start development environment
docker compose up -d

# Artisan commands (run in Docker)
aa migrate
aa tinker
aa config:clear
aa cache:clear

# Testing (run in Docker)
aa test
aa test tests/Feature/SomeTest.php
aa test --filter test_method_name

# Composer (run locally)
composer install
composer update

# Frontend assets (Vite + Tailwind CSS + Alpine.js)
pnpm install         # Install dependencies
pnpm dev             # Start Vite dev server with HMR (hot module replacement)
pnpm build           # Production build (outputs to public/build/)
```

## Writing plans

If you're writing plans to plan an implementation, never commit them. They do not belong into version control.

## Git commits

- Use conventional commits when creating commits
- NEVER use `git add -A` or `git commit -am`, always add only the changed files explicitley. Multiple changes might be happening at the same time, committing everything at once will mess up the commit history.
- NEVER discard unrelated changes that you did not do. These are always in-process changes made by someone else and will be committed separatly.
- Before comitting, ALWAYS run the lint with `composer lint:fix` and fix any remaining issues. Do not commit files that need linting, that will fail in CI.

## Architecture Overview

**Stack:** Laravel 13, Livewire 4, Jetstream 5, Filament 5, PHPUnit 12, Tailwind CSS, Alpine.js

This is a members' area application for a music group (brass band/orchestra). Core functionality:
- User registration with admin approval workflow
- Personal data collection (address, phone)
- Instrument assignment per user
- Music sheet distribution via WebDAV
- Admin-triggered ZIP backups of all sheets, delivered via signed-URL email link

### User Lifecycle

1. User registers → `STATUS_NEW` (0)
2. Admin activates → `STATUS_UNLOCKED` (1)
3. Middleware enforces: must set instrument (`MustHaveInstrument`) and personal data (`MustHavePersonalData`)
4. Optionally locked → `STATUS_LOCKED` (2)

### Key Models & Relationships

- **User** → hasOne PersonalData, belongsToMany InstrumentGroup, hasMany AdditionalEmails
- **InstrumentGroup** → hasMany Instrument (groups like "Brass", "Strings")
- **Instrument** → has `aliases` array for matching sheet filenames
- **Page** → Simple CMS pages with path-based routing

### Authorization

Uses Spatie/Laravel-Permission. Constants defined in `app/Rights.php`:
- `P_EDIT_PAGES` - Edit CMS pages
- `P_MANAGE_MEMBERS` - Manage members
- `P_VIEW_ALL_INSTRUMENTS` - View all instrument parts
- `P_DELETE_ACCOUNTS` - Delete accounts
- `R_ADMIN` - Admin role

### Sheet/Partition System

Music sheets are stored on the local `sheets` disk (see `config/filesystems.php`). The legacy WebDAV `cloud` disk + `SheetService::getSheetStructureFromWebdav()` + `RefreshSheetsCache` are vestigial and slated for removal. `SheetService` handles:
- File retrieval and caching
- Parsing filenames by instrument aliases
- Naming convention: `Song.Instrument.Part.Variant.pdf`

### Sheet Backups

Admin-triggered ZIP backups of every sheet PDF + a `manifest.csv`:
- Filament resource at `app/Filament/Resources/SheetBackups/` (admin-only via `R_ADMIN`).
- `CreateSheetBackupJob` (in `app/Jobs/`) builds the ZIP on the `sheet-backups` local disk, store-mode (no compression — PDFs already are). Single try, 30-min timeout.
- `SheetBackupReady` / `SheetBackupFailed` notifications send the admin a signed download link via the `sheet-backups.download` route.
- `sheet-backups:cleanup` (scheduled daily) prunes rows older than 7 days plus orphan files on the disk.
- Production must use a real queue driver (`database` or `redis`); the default `sync` runs the job inline on the admin's HTTP request.

### Key Directories

- `app/Filament/` - Filament admin panel resources, pages, and widgets
- `app/Livewire/` - Interactive components (MembersList, UpdatePersonalDataForm, etc.)
- `app/Services/` - SheetService, FriendlycaptchaService, MailcowService
- `app/Jobs/` - Queued jobs (CreateSheetBackupJob)
- `app/Http/Middleware/` - Custom: MustHaveInstrument, MustHavePersonalData, CheckIfActive, TrackLastActiveAt
- `resources/lang/` - i18n. App-level strings live in `de.json` only — there is no `en.json`; English is the source string used as the lookup key. Framework-level strings live in `de/` and `en/` PHP files.

## Filament Admin Panel

Access the admin panel at `/admin`. Requires user with `admin` role or `manage life members` permission.

```bash
# Generate Filament resources
aa make:filament-resource ModelName

# Generate relation manager
aa make:filament-relation-manager ResourceName relationName titleColumn

# Create admin user
aa make:filament-user
```

### Admin Panel Features
- Member management (list, edit, delete)
- Status management (activate, lock, unlock)
- Instrument group assignment
- Additional email management
- Member export

## Testing

Tests use SQLite in-memory database. Captcha is disabled in test environment.

```bash
aa test
```

## Docker Services

- **web** (port 8000) - Laravel Octane
- **db** (port 3306) - MariaDB 11.6.2
- **redis** - Session/cache
- **mailhog** (port 8025) - Email testing

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- filament/filament (FILAMENT) - v5
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `pnpm run build`, `pnpm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `pnpm run build` or ask the user to run `pnpm run dev` or `composer run dev`.

=== octane/core rules ===

# Octane

- Octane boots the application once and reuses it across requests, so singletons persist between requests.
- The Laravel container's `scoped` method may be used as a safe alternative to `singleton`.
- Never inject the container, request, or config repository into a singleton's constructor; use a resolver closure or `bind()` instead:

```php
// Bad
$this->app->singleton(Service::class, fn (Application $app) => new Service($app['request']));

// Good
$this->app->singleton(Service::class, fn () => new Service(fn () => request()));
```

- Never append to static properties, as they accumulate in memory across requests.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
