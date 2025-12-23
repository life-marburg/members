<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Textarea::make('two_factor_secret')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('current_team_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('profile_photo_path')
                    ->default(null),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_active_at'),
                TextInput::make('disable_after_days')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
