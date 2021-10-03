<?php

namespace App\Http\Controllers;

use App\Instruments;
use App\Services\SheetService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SheetController extends Controller
{
    public function index()
    {
        return view('pages.sheets', [
            'instruments' => Instruments::INSTRUMENT_GROUPS[Auth::user()->personalData->instrument]['instruments'],
        ]);
    }

    public function show(SheetService $sheetService, string $instrument)
    {
        return view('pages.sheets-show', [
            'instrument' => $instrument,
            'sheets' => $sheetService->getSheetsForInstrument(Auth::user()->personalData->instrument, $instrument),
        ]);
    }

    public function download(string $sheet, string $instrument, string $variant)
    {
        $file = Storage::disk('cloud')->get(SheetService::getSheetDownloadPath($sheet, $instrument, $variant));
        $name = $sheet . ' ' . $instrument . ' ' . $variant . '. Stimme.pdf';

        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
