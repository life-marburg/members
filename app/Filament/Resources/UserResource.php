<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Profile Information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('Phone')),
                        Forms\Components\TextInput::make('mobile_phone')
                            ->required()
                            ->label(__('Mobile Phone')),
                        Forms\Components\TextInput::make('street')
                            ->required()
                            ->label(__('Street and Housenumber')),
                        Forms\Components\TextInput::make('zip')
                            ->required()
                            ->label(__('Zip Code')),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->label(__('City')),
                    ]),
                Forms\Components\Section::make(__('Admin Settings'))
                    ->description(__('Set the user\'s state, admin status or instrument here.'))
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                User::STATUS_NEW => __('New'),
                                User::STATUS_UNLOCKED => __('Active'),
                                User::STATUS_LOCKED => __('Locked'),
                            ])
                            ->required(),
                        Forms\Components\Select::make('instrumentGroups')
                            ->label(__('Instrument Groups'))
                            ->multiple()
                            ->relationship('instrumentGroups', 'title')
                            ->preload(),
                        Forms\Components\Select::make('disable_after_days')
                            ->label(__('Disable after inactivity'))
                            ->options([
                                'null' => __('Never'),
                                14 => __('After :n days', ['n' => 14]),
                                90 => __('After :n days', ['n' => 90]),
                            ]),
                        Forms\Components\Checkbox::make('is_admin')
                            ->label(__('Is Admin'))
                            ->reactive(),
                        Forms\Components\Checkbox::make('can_view_all_instruments')
                            ->label(__('Can view sheets for all instruments'))
                            ->helperText(fn(Closure $get) => $get('is_admin') ? __('Already implied since this user has admin permissions.') : '')
                            ->disabled(fn(Closure $get) => $get('is_admin')),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('allEmails')
                    ->sortable(['email'])
                    ->label(__('Email')),
                Tables\Columns\TextColumn::make('instrumentGroups.title')
                    ->sortable(query: fn(Builder $query, string $direction): Builder => $query->orderBy('instrument_groups.title', $direction))
                    ->label(__('Instrument')),
                Tables\Columns\TextColumn::make('fullAddress')
                    ->sortable(['personal_data.street'])
                    ->label(__('Address')),
                Tables\Columns\TextColumn::make('personalData.mobile_phone')
                    ->label(__('Phone')),
                BadgeColumn::make('status')
                    ->enum([
                        User::STATUS_NEW => __('New'),
                        User::STATUS_UNLOCKED => __('Active'),
                        User::STATUS_LOCKED => __('Locked'),
                    ])
                    ->colors([
                        'warning' => User::STATUS_NEW,
                        'success' => User::STATUS_UNLOCKED,
                        'danger' => User::STATUS_LOCKED,
                    ]),
                Tables\Columns\ViewColumn::make('isAdmin')
                    ->label(__('Admin'))
                    ->view('filament.tables.columns.is-true'),
                Tables\Columns\ViewColumn::make('canViewAllSheets')
                    ->label(__('Can view all sheets'))
                    ->view('filament.tables.columns.is-true'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
