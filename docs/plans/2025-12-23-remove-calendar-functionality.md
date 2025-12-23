# Remove Calendar Functionality Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Remove all calendar-related functionality from the Laravel application, including the CalDAV proxy endpoint, informational page, navigation links, and translations.

**Architecture:** The calendar feature is a minimal read-only proxy to an external Nextcloud CalDAV server. It consists of one controller, two routes, one view, navigation links, and one translation entry. No database models, migrations, or Livewire components exist for calendar.

**Tech Stack:** Laravel 12, Blade templates, German translations

---

## Summary of Files to Modify/Delete

| Action | File |
|--------|------|
| Delete | `app/Http/Controllers/CalendarController.php` |
| Modify | `routes/web.php` (lines 3, 44, 74-76) |
| Delete | `resources/views/pages/calendar.blade.php` |
| Modify | `resources/views/navigation-menu.blade.php` (lines 24-26, 134-136) |
| Modify | `resources/lang/de.json` (line 750) |

---

### Task 1: Remove CalendarController

**Files:**
- Delete: `app/Http/Controllers/CalendarController.php`

**Step 1: Delete the controller file**

```bash
rm app/Http/Controllers/CalendarController.php
```

**Step 2: Verify deletion**

Run: `ls app/Http/Controllers/CalendarController.php`
Expected: `ls: cannot access 'app/Http/Controllers/CalendarController.php': No such file or directory`

**Step 3: Commit**

```bash
git add -A && git commit -m "chore: remove CalendarController"
```

---

### Task 2: Remove Calendar Routes

**Files:**
- Modify: `routes/web.php:3,44,74-76`

**Step 1: Remove the CalendarController import**

Remove line 3:
```php
use App\Http\Controllers\CalendarController;
```

**Step 2: Remove the calendar view route**

Remove line 44:
```php
    Route::view('/calendar', 'pages.calendar')->name('calendar');
```

**Step 3: Remove the CalDAV endpoint route**

Remove lines 74-76:
```php
Route::get('/calendar/caldav/internal', [CalendarController::class, 'getCalDAVCalendarOutput'])
    ->name('caldav.calendar.internal')
    ->middleware('auth.basic');
```

**Step 4: Run route cache clear and verify routes are removed**

Run: `docker compose exec web php artisan route:clear && docker compose exec web php artisan route:list | grep -i calendar`
Expected: No output (no calendar routes found)

**Step 5: Commit**

```bash
git add routes/web.php && git commit -m "chore: remove calendar routes"
```

---

### Task 3: Remove Calendar View

**Files:**
- Delete: `resources/views/pages/calendar.blade.php`

**Step 1: Delete the calendar view file**

```bash
rm resources/views/pages/calendar.blade.php
```

**Step 2: Verify deletion**

Run: `ls resources/views/pages/calendar.blade.php`
Expected: `ls: cannot access 'resources/views/pages/calendar.blade.php': No such file or directory`

**Step 3: Commit**

```bash
git add -A && git commit -m "chore: remove calendar view template"
```

---

### Task 4: Remove Navigation Links

**Files:**
- Modify: `resources/views/navigation-menu.blade.php:24-26,134-136`

**Step 1: Remove desktop navigation link**

Remove lines 24-26:
```blade
                    <x-nav-link href="{{ route('calendar') }}" :active="request()->routeIs('calendar')">
                        {{ __('Calendar') }}
                    </x-nav-link>
```

**Step 2: Remove mobile/responsive navigation link**

Remove lines 134-136 (will be ~131-133 after previous removal):
```blade
                <x-responsive-nav-link href="{{ route('calendar') }}" :active="request()->routeIs('calendar')">
                    {{ __('Calendar') }}
                </x-responsive-nav-link>
```

**Step 3: Verify navigation renders without errors**

Run: `docker compose exec web php artisan view:clear && docker compose exec web php artisan view:cache`
Expected: Views compile without errors

**Step 4: Commit**

```bash
git add resources/views/navigation-menu.blade.php && git commit -m "chore: remove calendar navigation links"
```

---

### Task 5: Remove Translation Entry

**Files:**
- Modify: `resources/lang/de.json:750`

**Step 1: Remove the Calendar translation**

Remove line 750:
```json
    "Calendar": "Kalender",
```

**Step 2: Verify JSON is still valid**

Run: `docker compose exec web php -r "json_decode(file_get_contents('resources/lang/de.json')); echo json_last_error() === JSON_ERROR_NONE ? 'Valid JSON' : 'Invalid JSON';"`
Expected: `Valid JSON`

**Step 3: Commit**

```bash
git add resources/lang/de.json && git commit -m "chore: remove calendar translation"
```

---

### Task 6: Run Tests to Verify No Regressions

**Step 1: Clear all caches**

Run: `docker compose exec web php artisan config:clear && docker compose exec web php artisan cache:clear && docker compose exec web php artisan route:clear && docker compose exec web php artisan view:clear`

**Step 2: Run the test suite**

Run: `docker compose exec web ./vendor/bin/phpunit`
Expected: All tests pass (no failures related to calendar removal)

**Step 3: Final commit (squash or amend if preferred)**

```bash
git add -A && git commit -m "feat: remove calendar functionality

Removed the following calendar-related components:
- CalendarController and CalDAV proxy endpoint
- Calendar routes (/calendar and /calendar/caldav/internal)
- Calendar view template
- Navigation links (desktop and mobile)
- German translation entry

The calendar functionality proxied to an external Nextcloud CalDAV
server. Members can still access the calendar directly via the
main website at life-marburg.de/kalender.html"
```
