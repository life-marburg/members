# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

All PHP/artisan commands run inside Docker. Composer runs locally.

```bash
# Start development environment
docker compose up -d

# Artisan commands (run in Docker)
docker compose exec web php artisan migrate
docker compose exec web php artisan tinker
docker compose exec web php artisan config:clear
docker compose exec web php artisan cache:clear

# Testing (run in Docker)
docker compose exec web ./vendor/bin/phpunit
docker compose exec web ./vendor/bin/phpunit tests/Feature/SomeTest.php
docker compose exec web ./vendor/bin/phpunit --filter test_method_name

# Composer (run locally)
composer install
composer update

# Frontend assets (uses yarn)
yarn install
yarn dev             # Development build
yarn watch           # Watch mode
yarn prod            # Production build
```

## Writing plans

If you're writing plans to plan an implementation, never commit them. They do not belong into version control.

## Git commits

- Use conventional commits when creating commits
- NEVER use `git add -A` or `git commit -am`, always add only the changed files explicitley. Multiple changes might be happening at the same time, committing everything at once will mess up the commit history.

## Architecture Overview

**Stack:** Laravel 12, Livewire 3, Jetstream 5, Filament 4, Tailwind CSS, Alpine.js

This is a members' area application for a music group (brass band/orchestra). Core functionality:
- User registration with admin approval workflow
- Personal data collection (address, phone)
- Instrument assignment per user
- Music sheet distribution via WebDAV

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

Music sheets stored in WebDAV at `/Life/Noten/`. `SheetService` handles:
- File retrieval and caching
- Parsing filenames by instrument aliases
- Naming convention: `Song.Instrument.Part.Variant.pdf`

### Key Directories

- `app/Filament/` - Filament admin panel resources, pages, and widgets
- `app/Livewire/` - Interactive components (MembersList, UpdatePersonalDataForm, etc.)
- `app/Services/` - SheetService, FriendlycaptchaService, MailcowService
- `app/Http/Middleware/` - Custom: MustHaveInstrument, MustHavePersonalData, CheckIfActive, TrackLastActiveAt
- `resources/lang/` - i18n (German primary, English fallback)

## Filament Admin Panel

Access the admin panel at `/admin`. Requires user with `admin` role or `manage life members` permission.

```bash
# Generate Filament resources
docker compose exec web php artisan make:filament-resource ModelName

# Generate relation manager
docker compose exec web php artisan make:filament-relation-manager ResourceName relationName titleColumn

# Create admin user
docker compose exec web php artisan make:filament-user
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
docker compose exec web ./vendor/bin/phpunit
```

## Docker Services

- **web** (port 8000) - Laravel Octane
- **db** (port 3306) - MariaDB 11.6.2
- **redis** - Session/cache
- **mailhog** (port 8025) - Email testing
