<?php

namespace App\Filament\Resources\ExternalLinks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Link Details'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The text displayed in the navigation menu')),

                        TextInput::make('url')
                            ->label(__('URL'))
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->helperText(__('The URL the link points to')),

                        Select::make('target')
                            ->label(__('Target'))
                            ->options([
                                '_blank' => __('New tab'),
                                '_self' => __('Same tab'),
                            ])
                            ->default('_blank')
                            ->required(),

                        Toggle::make('show_external_icon')
                            ->label(__('Show external link icon'))
                            ->default(true)
                            ->helperText(__('Display an icon indicating this opens externally')),

                        TextInput::make('position')
                            ->label(__('Position'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('Lower numbers appear first')),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->helperText(__('Only active links are shown in the navigation')),
                    ])
                    ->columns(2),
            ]);
    }
}
