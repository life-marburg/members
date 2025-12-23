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
            ExportColumn::make('personalData.street')->label(__('Street')),
            ExportColumn::make('personalData.zip')->label(__('ZIP')),
            ExportColumn::make('personalData.city')->label(__('City')),
            ExportColumn::make('personalData.phone')->label(__('Phone')),
            ExportColumn::make('personalData.mobile_phone')->label(__('Mobile')),
            ExportColumn::make('instrumentGroups.title')->label(__('Instruments')),
            ExportColumn::make('status')
                ->formatStateUsing(fn (int $state): string => match ($state) {
                    User::STATUS_NEW => __('New'),
                    User::STATUS_UNLOCKED => __('Active'),
                    User::STATUS_LOCKED => __('Locked'),
                    default => __('Unknown'),
                }),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return __('Your member export has completed. :count rows exported.', [
            'count' => number_format($export->successful_rows),
        ]);
    }
}
