<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Rights;
use App\Services\SheetService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SheetController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS)) {
            $instruments = Instrument::all();
        } else {
            $instruments = Auth::user()
                ->instrumentGroups
                ->map(fn($g) => $g->instruments)
                ->flatten();
        }

        return view('pages.sheets', [
            'instruments' => $instruments->sortBy('title'),
        ]);
    }

    public function show(SheetService $sheetService, Instrument $instrument)
    {
        return view('pages.sheets-show', [
            'instrument' => $instrument,
            'sheets' => $sheetService->getSheetsForInstrument($instrument),
        ]);
    }

    public function download(string $sheet, Instrument $instrument, string $variant)
    {
        $file = Storage::disk('cloud')->get(SheetService::getSheetDownloadPath($sheet, $instrument, $variant));
        $name = $sheet . ' ' . $instrument->title . ' ' . $variant . '. Stimme.pdf';

        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
