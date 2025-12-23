# Filament Member Management Migration Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add Filament admin panel and migrate existing member management (list, approve, edit, delete) from Livewire components to Filament resources.

**Architecture:** Install Filament 4.x as a separate `/admin` panel with its own authentication guard. Create a UserResource with custom actions for status management (approve/lock/unlock). Use relation managers for PersonalData and InstrumentGroups. Preserve existing Livewire components until migration is verified.

**Tech Stack:** Filament 4.x, Laravel 12, Spatie Laravel-Permission (already installed)

---

## Prerequisites Check

Before starting, verify:
- PHP 8.2+ (required by Filament 4.x)
- Laravel 12 (already in place per CLAUDE.md)
- Node.js for asset compilation

---

## Task 1: Install Filament

**Files:**
- Modify: `composer.json`
- Create: `app/Providers/Filament/AdminPanelProvider.php`
- Modify: `config/app.php`

**Step 1: Require Filament package**

Run inside Docker:
```bash
docker compose exec web composer require filament/filament:"^4.0"
```

**Step 2: Run Filament installer**

```bash
docker compose exec web php artisan filament:install --panels
```

When prompted:
- Panel ID: `admin`
- Panel path: `admin`

**Step 3: Verify installation**

```bash
docker compose exec web php artisan route:list | grep filament
```

Expected: Routes for `/admin/*` should appear.

**Step 4: Commit**

```bash
git add -A && git commit -m "feat: install Filament 4.x admin panel"
```

---

## Task 2: Configure Admin Panel Authentication

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php`
- Modify: `app/Models/User.php`

**Step 1: Make User implement FilamentUser**

Edit `app/Models/User.php`:

```php
<?php

namespace App\Models;

// ... existing imports
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    // ... existing code

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole(\App\Rights::R_ADMIN) ||
                   $this->hasPermissionTo(\App\Rights::P_MANAGE_MEMBERS);
        }

        return false;
    }
}
```

**Step 2: Verify admin access**

Run the app and navigate to `/admin`. Only users with R_ADMIN role or P_MANAGE_MEMBERS permission should access.

**Step 3: Commit**

```bash
git add -A && git commit -m "feat: configure Filament admin panel authentication"
```

---

## Task 3: Create UserResource with Table

**Files:**
- Create: `app/Filament/Resources/UserResource.php`
- Create: `app/Filament/Resources/UserResource/Pages/ListUsers.php`
- Create: `app/Filament/Resources/UserResource/Pages/EditUser.php`
- Create: `app/Filament/Resources/UserResource/Pages/CreateUser.php`

**Step 1: Generate resource scaffold**

```bash
docker compose exec web php artisan make:filament-resource User --generate
```

**Step 2: Replace generated UserResource with custom implementation**

Edit `app/Filament/Resources/UserResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Rights;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Members';

    protected static ?string $modelLabel = 'Member';

    protected static ?string $pluralModelLabel = 'Members';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('instrumentGroups.title')
                    ->badge()
                    ->separator(', ')
                    ->label('Instruments'),
                TextColumn::make('personalData.city')
                    ->label('City')
                    ->sortable(),
                TextColumn::make('personalData.mobile_phone')
                    ->label('Mobile'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        User::STATUS_NEW => 'New',
                        User::STATUS_UNLOCKED => 'Active',
                        User::STATUS_LOCKED => 'Locked',
                        default => 'Unknown',
                    })
                    ->color(fn (int $state): string => match ($state) {
                        User::STATUS_NEW => 'warning',
                        User::STATUS_UNLOCKED => 'success',
                        User::STATUS_LOCKED => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->hasRole(Rights::R_ADMIN)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        User::STATUS_NEW => 'New',
                        User::STATUS_UNLOCKED => 'Active',
                        User::STATUS_LOCKED => 'Locked',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === User::STATUS_NEW)
                    ->action(function (User $record): void {
                        $record->update(['status' => User::STATUS_UNLOCKED]);
                        $record->notify(new \App\Notifications\UserStatusChanged());
                    }),
                Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === User::STATUS_UNLOCKED)
                    ->action(fn (User $record) => $record->update(['status' => User::STATUS_LOCKED])),
                Action::make('unlock')
                    ->label('Unlock')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === User::STATUS_LOCKED)
                    ->action(fn (User $record) => $record->update(['status' => User::STATUS_UNLOCKED])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(function (User $user) {
                            $user->update(['status' => User::STATUS_UNLOCKED]);
                            $user->notify(new \App\Notifications\UserStatusChanged());
                        })),
                    BulkAction::make('lock')
                        ->label('Lock Selected')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => User::STATUS_LOCKED])),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->can(Rights::P_DELETE_ACCOUNTS)),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Will be added in Task 5
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

**Step 3: Verify table displays**

Navigate to `/admin/users` and verify the members table shows with all columns.

**Step 4: Commit**

```bash
git add -A && git commit -m "feat: add UserResource with table and status actions"
```

---

## Task 4: Create UserResource Edit Form

**Files:**
- Modify: `app/Filament/Resources/UserResource.php`

**Step 1: Add form method to UserResource**

Add this method to `UserResource.php`:

```php
public static function form(Schema $schema): Schema
{
    return $schema
        ->components([
            \Filament\Forms\Components\Section::make('Account Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ]),

            \Filament\Forms\Components\Section::make('Personal Data')
                ->relationship('personalData')
                ->schema([
                    TextInput::make('street')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            TextInput::make('zip')
                                ->required()
                                ->maxLength(20),
                            TextInput::make('city')
                                ->required()
                                ->maxLength(255),
                        ]),
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            TextInput::make('phone')
                                ->tel()
                                ->maxLength(50),
                            TextInput::make('mobile_phone')
                                ->tel()
                                ->required()
                                ->maxLength(50),
                        ]),
                ]),

            \Filament\Forms\Components\Section::make('Status & Permissions')
                ->schema([
                    Select::make('status')
                        ->options([
                            User::STATUS_NEW => 'New',
                            User::STATUS_UNLOCKED => 'Active',
                            User::STATUS_LOCKED => 'Locked',
                        ])
                        ->required(),
                    Select::make('instrumentGroups')
                        ->relationship('instrumentGroups', 'title')
                        ->multiple()
                        ->preload()
                        ->label('Instrument Groups'),
                    Toggle::make('is_admin')
                        ->label('Administrator')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Toggle $component, ?User $record) {
                            if ($record) {
                                $component->state($record->hasRole(Rights::R_ADMIN));
                            }
                        })
                        ->afterStateUpdated(function ($state, User $record) {
                            if ($state) {
                                $record->assignRole(Rights::R_ADMIN);
                                $record->update(['disable_after_days' => null]);
                            } else {
                                $record->removeRole(Rights::R_ADMIN);
                                $record->update(['disable_after_days' => 90]);
                            }
                        })
                        ->live(),
                    Toggle::make('can_view_all_instruments')
                        ->label('Can View All Instrument Sheets')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Toggle $component, ?User $record) {
                            if ($record) {
                                $component->state($record->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS));
                            }
                        })
                        ->afterStateUpdated(function ($state, User $record) {
                            if ($state) {
                                $record->givePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
                            } else {
                                $record->revokePermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS);
                            }
                        })
                        ->visible(fn (\Filament\Forms\Get $get): bool => !$get('is_admin')),
                    Select::make('disable_after_days')
                        ->label('Disable After Inactivity')
                        ->options([
                            '' => 'Never',
                            14 => 'After 14 days',
                            90 => 'After 90 days',
                        ])
                        ->visible(fn (\Filament\Forms\Get $get): bool => !$get('is_admin')),
                ]),
        ]);
}
```

**Step 2: Test edit form**

Navigate to `/admin/users/{id}/edit` and verify form loads with user data.

**Step 3: Commit**

```bash
git add -A && git commit -m "feat: add UserResource edit form with personal data and permissions"
```

---

## Task 5: Add InstrumentGroups Relation Manager

**Files:**
- Create: `app/Filament/Resources/UserResource/RelationManagers/InstrumentGroupsRelationManager.php`
- Modify: `app/Filament/Resources/UserResource.php`

**Step 1: Generate relation manager**

```bash
docker compose exec web php artisan make:filament-relation-manager UserResource instrumentGroups title
```

**Step 2: Configure relation manager**

Edit `app/Filament/Resources/UserResource/RelationManagers/InstrumentGroupsRelationManager.php`:

```php
<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstrumentGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'instrumentGroups';

    protected static ?string $title = 'Instrument Groups';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Group'),
                TextColumn::make('instruments.title')
                    ->badge()
                    ->label('Instruments'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
```

**Step 3: Register relation manager in UserResource**

Update `getRelations()` in `UserResource.php`:

```php
public static function getRelations(): array
{
    return [
        RelationManagers\InstrumentGroupsRelationManager::class,
    ];
}
```

**Step 4: Add import statement**

Add to top of `UserResource.php`:
```php
use App\Filament\Resources\UserResource\RelationManagers;
```

**Step 5: Test relation manager**

Navigate to edit page and verify instrument groups can be attached/detached.

**Step 6: Commit**

```bash
git add -A && git commit -m "feat: add InstrumentGroups relation manager to UserResource"
```

---

## Task 6: Add AdditionalEmails Relation Manager

**Files:**
- Create: `app/Filament/Resources/UserResource/RelationManagers/AdditionalEmailsRelationManager.php`
- Modify: `app/Filament/Resources/UserResource.php`

**Step 1: Generate relation manager**

```bash
docker compose exec web php artisan make:filament-relation-manager UserResource additionalEmails email
```

**Step 2: Configure relation manager**

Edit the generated file:

```php
<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdditionalEmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'additionalEmails';

    protected static ?string $title = 'Additional Emails';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
```

**Step 3: Register in UserResource**

Update `getRelations()`:

```php
public static function getRelations(): array
{
    return [
        RelationManagers\InstrumentGroupsRelationManager::class,
        RelationManagers\AdditionalEmailsRelationManager::class,
    ];
}
```

**Step 4: Commit**

```bash
git add -A && git commit -m "feat: add AdditionalEmails relation manager to UserResource"
```

---

## Task 7: Add Export Action

**Files:**
- Create: `app/Filament/Exports/UserExport.php` (or reuse existing)
- Modify: `app/Filament/Resources/UserResource.php`

**Step 1: Add export header action**

In `UserResource.php`, modify the table method to add a header action:

```php
->headerActions([
    \Filament\Tables\Actions\ExportAction::make()
        ->exporter(\App\Filament\Exports\UserExporter::class),
])
```

**Step 2: Create exporter class**

```bash
docker compose exec web php artisan make:filament-exporter User
```

**Step 3: Configure exporter**

Edit `app/Filament/Exports/UserExporter.php`:

```php
<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('personalData.street')->label('Street'),
            ExportColumn::make('personalData.zip')->label('ZIP'),
            ExportColumn::make('personalData.city')->label('City'),
            ExportColumn::make('personalData.phone')->label('Phone'),
            ExportColumn::make('personalData.mobile_phone')->label('Mobile'),
            ExportColumn::make('instrumentGroups.title')->label('Instruments'),
            ExportColumn::make('status')
                ->formatStateUsing(fn (int $state): string => match ($state) {
                    User::STATUS_NEW => 'New',
                    User::STATUS_UNLOCKED => 'Active',
                    User::STATUS_LOCKED => 'Locked',
                    default => 'Unknown',
                }),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Your member export has completed. ' . number_format($export->successful_rows) . ' rows exported.';
    }
}
```

**Step 4: Commit**

```bash
git add -A && git commit -m "feat: add member export functionality to Filament"
```

---

## Task 8: Add Delete User Page Action

**Files:**
- Modify: `app/Filament/Resources/UserResource/Pages/EditUser.php`

**Step 1: Add delete action with password confirmation**

Edit `app/Filament/Resources/UserResource/Pages/EditUser.php`:

```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Rights;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()->can(Rights::P_DELETE_ACCOUNTS))
                ->form([
                    TextInput::make('password')
                        ->label('Your Password')
                        ->password()
                        ->required()
                        ->helperText('Enter your password to confirm deletion'),
                ])
                ->action(function (array $data) {
                    if (!Hash::check($data['password'], auth()->user()->password)) {
                        $this->addError('mountedActionsData.0.password', 'Invalid password');
                        $this->halt();
                    }

                    $this->record->delete();

                    return redirect()->to(UserResource::getUrl('index'));
                }),
        ];
    }
}
```

**Step 2: Commit**

```bash
git add -A && git commit -m "feat: add password-protected delete action to user edit page"
```

---

## Task 9: Add Navigation and Polish

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php`
- Modify: `app/Filament/Resources/UserResource.php`

**Step 1: Configure panel branding**

Edit `AdminPanelProvider.php`:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->brandName('Life Members Admin')
        ->colors([
            'primary' => \Filament\Support\Colors\Color::Blue,
        ])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
        ->pages([
            \Filament\Pages\Dashboard::class,
        ])
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
        ->widgets([
            // Dashboard widgets
        ])
        ->middleware([
            // ...existing middleware
        ])
        ->authMiddleware([
            \Filament\Http\Middleware\Authenticate::class,
        ]);
}
```

**Step 2: Add navigation badge for pending members**

In `UserResource.php`, add:

```php
public static function getNavigationBadge(): ?string
{
    $count = static::getModel()::where('status', User::STATUS_NEW)->count();
    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    return 'warning';
}
```

**Step 3: Commit**

```bash
git add -A && git commit -m "feat: add panel branding and pending member badge"
```

---

## Task 10: Write Tests

**Files:**
- Create: `tests/Feature/Filament/UserResourceTest.php`

**Step 1: Create test file**

```php
<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use App\Rights;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use function Pest\Livewire\livewire;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions and roles
        Permission::create(['name' => Rights::P_MANAGE_MEMBERS]);
        Permission::create(['name' => Rights::P_DELETE_ACCOUNTS]);
        Permission::create(['name' => Rights::P_VIEW_ALL_INSTRUMENTS]);
        Role::create(['name' => Rights::R_ADMIN])->givePermissionTo(Permission::all());

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Rights::R_ADMIN);
    }

    public function test_admin_can_access_user_list(): void
    {
        $this->actingAs($this->admin);

        livewire(ListUsers::class)
            ->assertSuccessful();
    }

    public function test_admin_can_activate_new_user(): void
    {
        $this->actingAs($this->admin);

        $newUser = User::factory()->create(['status' => User::STATUS_NEW]);

        livewire(ListUsers::class)
            ->callAction(TestAction::make('activate')->table($newUser));

        $this->assertEquals(User::STATUS_UNLOCKED, $newUser->fresh()->status);
    }

    public function test_admin_can_edit_user(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create();

        livewire(EditUser::class, ['record' => $user->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertNotified();

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get('/admin')
            ->assertForbidden();
    }
}
```

**Step 2: Run tests**

```bash
docker compose exec web ./vendor/bin/phpunit tests/Feature/Filament/UserResourceTest.php
```

**Step 3: Commit**

```bash
git add -A && git commit -m "test: add Filament UserResource tests"
```

---

## Task 11: Update Documentation

**Files:**
- Modify: `CLAUDE.md`

**Step 1: Add Filament section to CLAUDE.md**

Add to the Development Commands section:

```markdown
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
```

**Step 2: Commit**

```bash
git add -A && git commit -m "docs: add Filament admin panel documentation"
```

---

## Task 12: Clean Up (Optional - After Verification)

After verifying Filament works correctly, optionally remove or deprecate:

**Files to consider removing:**
- `app/Livewire/MembersList.php`
- `app/Livewire/UserUpdateMeta.php`
- `app/Livewire/AdminDeleteUserForm.php`
- `resources/views/livewire/members-list.blade.php`
- `resources/views/livewire/user-update-meta.blade.php`
- `resources/views/livewire/admin-delete-user-form.blade.php`
- `resources/views/pages/members/index.blade.php`
- `resources/views/pages/members/edit.blade.php`

**Routes to remove from `routes/web.php`:**
```php
Route::middleware(['can:' . Rights::P_MANAGE_MEMBERS])
    ->resource('members', MemberController::class);
```

**Controller to remove:**
- `app/Http/Controllers/MemberController.php`

**Note:** Keep `UpdatePersonalDataForm.php` if it's used for user self-service editing.

---

## Summary

| Task | Description | Est. Complexity |
|------|-------------|-----------------|
| 1 | Install Filament | Low |
| 2 | Configure authentication | Low |
| 3 | Create UserResource table | Medium |
| 4 | Create UserResource form | Medium |
| 5 | InstrumentGroups relation manager | Low |
| 6 | AdditionalEmails relation manager | Low |
| 7 | Export action | Low |
| 8 | Delete with password confirmation | Medium |
| 9 | Navigation and polish | Low |
| 10 | Write tests | Medium |
| 11 | Update documentation | Low |
| 12 | Clean up old code | Low |

Total: 12 tasks
