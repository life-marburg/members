<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentGroup;
use App\Rights;
use App\Services\SheetService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SheetController extends Controller
{
    public function index()
    {
        $with = ['instruments' => function ($query) {
            $query->orderBy('title');
        }];

        if (Auth::user()->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS)) {
            $groups = InstrumentGroup::with($with)->get();
        } else {
            $groups = Auth::user()
                ->instrumentGroups()
                ->with($with)
                ->get();
        }

        return view('pages.sheets', [
            'groups' => $groups->sortBy('title'),
        ]);
    }

    public function show(SheetService $sheetService, Instrument $instrument)
    {
        return view('pages.sheets-show', [
            'instrument' => $instrument,
            'sheets' => $sheetService->getSheetsForInstrument($instrument),
        ]);
    }

    public function download(string $sheet, string $instrumentFileName, string $variant)
    {
        $file = Storage::disk('cloud')->get(SheetService::getSheetDownloadPath($sheet, $instrumentFileName, $variant));
        $name = $sheet . ' ' . Str::headline($instrumentFileName) . ' ' . $variant . '. Stimme.pdf';

        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
