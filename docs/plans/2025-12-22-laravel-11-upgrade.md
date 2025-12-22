# Laravel 11 Upgrade Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Upgrade this Laravel 10.48.29 application to Laravel 11 with all dependencies updated and tested.

**Architecture:** This is a straightforward upgrade. We upgrade Livewire v2→v3 first (required by Jetstream 5), then upgrade Laravel framework and related packages. The application uses standard Laravel 10 structure which is fully supported in Laravel 11 (no need to migrate to the new slim structure).

**Tech Stack:** Laravel 11, PHP 8.2+, Livewire 3, Sanctum 4, Jetstream 5, MySQL, Redis

**Environment:** Docker-based. All `php artisan` and `composer` commands must run inside the `life-members-web-1` container:
```bash
docker exec life-members-web-1 <command>
```

---

## Pre-Upgrade Checklist

Before starting, ensure:
- [ ] Git working tree is clean (`git status` shows no changes)
- [ ] All tests pass (`docker exec life-members-web-1 php artisan test`)
- [ ] Database backup created
- [ ] Create a new branch for the upgrade

---

## Phase 1: Preparation

### Task 1: Create Upgrade Branch

**Files:**
- None (git operations only)

**Step 1: Ensure clean working directory**

Run: `git status`
Expected: "nothing to commit, working tree clean"

**Step 2: Create and checkout upgrade branch**

```bash
git checkout -b upgrade/laravel-11
```

**Step 3: Verify branch**

Run: `git branch --show-current`
Expected: `upgrade/laravel-11`

---

### Task 2: Run Existing Tests as Baseline

**Files:**
- None

**Step 1: Run the test suite**

Run: `docker exec life-members-web-1 php artisan test`
Expected: All tests pass (document count for reference)

**Step 2: Document baseline**

Note the number of passing tests for comparison after upgrade.

---

## Phase 2: Livewire 2 → 3 Upgrade

Livewire 3 is required for Jetstream 5, which is required for Laravel 11.

### Task 3: Update Livewire to v3

**Files:**
- Modify: `composer.json`

**Step 1: Update composer.json**

Change:
```json
"livewire/livewire": "^2.12"
```

To:
```json
"livewire/livewire": "^3.0"
```

**Step 2: Run composer update**

Run: `docker exec life-members-web-1 composer update livewire/livewire --with-all-dependencies`

**Step 3: Clear caches**

```bash
docker exec life-members-web-1 php artisan view:clear
docker exec life-members-web-1 php artisan cache:clear
```

---

### Task 4: Update Livewire Component Syntax (If Any Custom Components Exist)

**Files:**
- Check: `app/Http/Livewire/` or `app/Livewire/`
- Check: `resources/views/livewire/`

**Step 1: Check for custom Livewire components**

Run: `find app -name "*.php" -path "*/Livewire/*" 2>/dev/null | head -20`

If none found, skip to Task 5.

**Step 2: Update component namespace** (if components exist)

Livewire 3 changes:
- Namespace changes from `App\Http\Livewire` to `App\Livewire`
- `wire:model` is now deferred by default (use `wire:model.live` for immediate)
- `$this->emit()` becomes `$this->dispatch()`
- `$this->emitTo()` becomes `$this->dispatch()->to()`

**Step 3: Update Blade directives** (if used)

- `@livewireStyles` → `@livewireStyles` (unchanged)
- `@livewireScripts` → `@livewireScripts` (unchanged)
- Check for `wire:model` bindings that need `.live` modifier

**Step 4: Commit Livewire upgrade**

```bash
git add .
git commit -m "chore: upgrade Livewire v2 to v3"
```

---

## Phase 3: Laravel Framework Upgrade

### Task 5: Update Laravel Framework

**Files:**
- Modify: `composer.json`

**Step 1: Update framework version**

Change in `composer.json`:
```json
"laravel/framework": "^10.48"
```

To:
```json
"laravel/framework": "^11.0"
```

**Step 2: Update related packages**

```json
{
    "laravel/jetstream": "^5.0",
    "laravel/sanctum": "^4.0",
    "nunomaduro/collision": "^8.1"
}
```

**Step 3: Run composer update**

```bash
docker exec life-members-web-1 composer update --with-all-dependencies
```

---

### Task 6: Publish Sanctum Migrations

**Files:**
- Create: `database/migrations/*_sanctum_*.php`

**Step 1: Publish Sanctum migrations**

```bash
docker exec life-members-web-1 php artisan vendor:publish --tag=sanctum-migrations
```

**Step 2: Run migrations**

```bash
docker exec life-members-web-1 php artisan migrate
```

---

### Task 7: Update Sanctum Configuration

**Files:**
- Modify: `config/sanctum.php`

**Step 1: Update middleware configuration**

Add/update in `config/sanctum.php`:

```php
'middleware' => [
    'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],
```

---

### Task 8: Review Migration Files for Breaking Changes

**Files:**
- Check: `database/migrations/*.php`

**Step 1: Search for float/double with precision arguments**

Run: `grep -rn "->double\|->float" database/migrations/`

**Step 2: Update if found**

Before:
```php
$table->double('amount', 8, 2);
$table->float('value', 8, 2);
```

After:
```php
$table->double('amount');
$table->float('value', precision: 53);
```

**Step 3: Search for unsigned floating point types**

Run: `grep -rn "unsignedDecimal\|unsignedDouble\|unsignedFloat" database/migrations/`

If found, update:
```php
// Before
$table->unsignedDouble('amount');

// After
$table->double('amount')->unsigned();
```

---

### Task 9: Check for Column Modifications

**Files:**
- Check: `database/migrations/*.php`

**Step 1: Search for ->change() calls**

Run: `grep -rn "->change()" database/migrations/`

**Step 2: Update modification migrations**

In Laravel 11, when modifying a column, you must include ALL modifiers:

Before (Laravel 10 - implicit retention):
```php
$table->integer('votes')->nullable()->change();
```

After (Laravel 11 - explicit):
```php
$table->integer('votes')
    ->unsigned()           // Retain from original
    ->default(1)           // Retain from original
    ->comment('The count') // Retain from original
    ->nullable()           // New change
    ->change();
```

**Alternative:** Squash migrations to avoid updating old migration files:
```bash
docker exec life-members-web-1 php artisan schema:dump
```

---

### Task 10: Update Cache Key Prefix (If Used)

**Files:**
- Modify: `config/cache.php`

**Step 1: Check current prefix**

Run: `grep -n "prefix" config/cache.php`

**Step 2: Add colon if needed**

Laravel 11 no longer auto-appends `:` to cache prefixes.

Before:
```php
'prefix' => env('CACHE_PREFIX', 'life_members_cache'),
```

After (if you need the colon):
```php
'prefix' => env('CACHE_PREFIX', 'life_members_cache:'),
```

---

### Task 11: Clear All Caches and Test

**Files:**
- None

**Step 1: Clear all caches**

```bash
docker exec life-members-web-1 php artisan optimize:clear
docker exec life-members-web-1 php artisan config:clear
docker exec life-members-web-1 php artisan route:clear
docker exec life-members-web-1 php artisan view:clear
docker exec life-members-web-1 php artisan cache:clear
```

**Step 2: Run migrations**

```bash
docker exec life-members-web-1 php artisan migrate
```

**Step 3: Run test suite**

```bash
docker exec life-members-web-1 php artisan test
```

**Step 4: Commit Laravel upgrade**

```bash
git add .
git commit -m "chore: upgrade Laravel 10 to Laravel 11"
```

---

## Phase 4: Cleanup and Verification

### Task 12: Remove Doctrine DBAL (Optional)

**Files:**
- Modify: `composer.json`

**Step 1: Check if Doctrine DBAL is used**

Run: `grep -rn "getDoctrineConnection\|getDoctrineSchemaManager" app/`

If no results, it's safe to remove.

**Step 2: Remove package**

```bash
docker exec life-members-web-1 composer remove doctrine/dbal
```

---

### Task 13: Update Dev Dependencies

**Files:**
- Modify: `composer.json`

**Step 1: Update collision package**

Already done in Task 5, verify:
```json
"nunomaduro/collision": "^8.1"
```

**Step 2: Run composer update**

```bash
docker exec life-members-web-1 composer update --dev
```

---

### Task 14: Verify Third-Party Package Compatibility

**Files:**
- None

**Step 1: Check singlequote/laravel-webdav**

Run: `docker exec life-members-web-1 composer show singlequote/laravel-webdav`

Check if there's a Laravel 11 compatible version. If not, search for alternatives or verify it still works.

**Step 2: Test all package functionality**

- Test Excel exports (maatwebsite/excel)
- Test permissions (spatie/laravel-permission)
- Test WebDAV functionality
- Test Captcha functionality

---

### Task 15: Full Application Test

**Files:**
- None

**Step 1: Test the application**

Access the application through your configured Docker URL/port and verify it loads.

**Step 2: Test critical paths**

- [ ] User registration
- [ ] User login
- [ ] User logout
- [ ] Password reset
- [ ] Two-factor authentication
- [ ] Email notifications
- [ ] Scheduled commands (`docker exec life-members-web-1 php artisan schedule:list`)

**Step 3: Run all tests**

```bash
docker exec life-members-web-1 php artisan test
```

**Step 4: Run static analysis (if configured)**

```bash
docker exec life-members-web-1 ./vendor/bin/phpstan analyse
```

---

### Task 16: Final Commit and PR

**Files:**
- None

**Step 1: Commit any remaining changes**

```bash
git add .
git commit -m "chore: post-upgrade cleanup and fixes"
```

**Step 2: Push branch**

```bash
git push -u origin upgrade/laravel-11
```

**Step 3: Create pull request**

Create PR with summary of all changes made during upgrade.

---

## Rollback Plan

If critical issues are encountered:

1. **Immediate rollback:**
   ```bash
   git checkout main
   ```

2. **If migrations were run:**
   ```bash
   docker exec life-members-web-1 php artisan migrate:rollback --step=N
   ```
   Where N is the number of new migrations run during upgrade.

3. **Restore composer.lock:**
   ```bash
   git checkout main -- composer.lock
   docker exec life-members-web-1 composer install
   ```

---

## Summary of Version Changes

| Package | Before | After |
|---------|--------|-------|
| laravel/framework | ^10.48 | ^11.0 |
| livewire/livewire | ^2.12 | ^3.0 |
| laravel/jetstream | ^3.3 | ^5.0 |
| laravel/sanctum | ^3.3 | ^4.0 |
| nunomaduro/collision | ^7.11 | ^8.1 |

---

## References

- [Laravel 11 Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Livewire 3 Upgrade Guide](https://livewire.laravel.com/docs/upgrading)
- [Sanctum 4 Upgrade](https://github.com/laravel/sanctum/blob/4.x/UPGRADE.md)
