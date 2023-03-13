<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->tableSortColumn) {
            'fullAddress' => User::select('users.*')
                ->distinct()
                ->join('personal_data', 'users.id', '=', 'personal_data.user_id')
                ->leftJoin('additional_emails', 'users.id', '=', 'additional_emails.user_id'),
            'instrumentGroups.title' => User::select('users.*')
                ->distinct()
                ->join('personal_data', 'users.id', '=', 'personal_data.user_id')
                ->leftJoin('additional_emails', 'users.id', '=', 'additional_emails.user_id')
                ->leftJoin('user_instrument_group', 'users.id', '=', 'user_instrument_group.user_id')
                ->leftJoin('instrument_groups', 'user_instrument_group.instrument_group_id', '=', 'instrument_groups.id'),
            default => User::with(['personalData', 'instrumentGroups', 'additionalEmails']),
        };
    }
}
