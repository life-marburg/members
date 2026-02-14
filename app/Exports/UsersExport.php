<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    public function collection(): Collection
    {
        return User::with(['personalData', 'instrumentGroups', 'additionalEmails'])
            ->orderBy('users.name')
            ->get()
            ->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                implode(', ', $user->additionalEmails->map(fn ($e) => $e->email)->toArray()),
                $user->personalData->street,
                $user->personalData->city,
                $user->personalData->zip,
                $user->personalData->phone,
                $user->personalData->mobile_phone,
                $user->instrumentGroups->implode('title', ', '),
            ]);
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('Email'),
            __('Additional Email Addresses'),
            __('Street'),
            __('City'),
            __('Zip / Postal Code'),
            __('Phone'),
            __('Mobile Phone'),
            __('Instrument'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $alphabet = $event->sheet->getHighestDataColumn();
                $totalRow = $event->sheet->getHighestDataRow();
                $startCell = '1';
                $cellRange = 'A'.$startCell.':'.$alphabet.$totalRow;

                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_HAIR,
                            'color' => ['argb' => 'CCCCCC'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
