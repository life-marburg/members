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
