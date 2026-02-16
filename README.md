# Live v2

[![Build Status](https://drone.kolaente.de/api/badges/Websites/life-v2/status.svg)](https://drone.kolaente.de/Websites/life-v2)

All new and fancy live members' area.

## Initial Setup

### Creating the First Admin User

When no admin user exists yet, use tinker to assign the admin role:

```bash
docker compose exec web php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
$user->status = \App\Models\User::STATUS_UNLOCKED;
$user->save();
$user->assignRole(\App\Rights::R_ADMIN);
```
