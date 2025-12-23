<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Rights;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
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

                Section::make('Personal Data')
                    ->relationship('personalData')
                    ->schema([
                        TextInput::make('street')
                            ->required()
                            ->maxLength(255),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('zip')
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
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

                Section::make('Status & Permissions')
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
                            ->live(),
                        Toggle::make('can_view_all_instruments')
                            ->label('Can View All Instrument Sheets')
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Toggle $component, ?User $record) {
                                if ($record) {
                                    $component->state($record->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS));
                                }
                            })
                            ->visible(fn (Get $get): bool => !$get('is_admin')),
                        Select::make('disable_after_days')
                            ->label('Disable After Inactivity')
                            ->options([
                                '' => 'Never',
                                14 => 'After 14 days',
                                90 => 'After 90 days',
                            ])
                            ->visible(fn (Get $get): bool => !$get('is_admin')),
                    ]),
            ]);
    }
}
