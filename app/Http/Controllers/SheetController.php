<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentGroup;
use App\Models\Sheet;
use App\Rights;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function show(Instrument $instrument)
    {
        $sheets = Sheet::with('song')
            ->where('instrument_id', $instrument->id)
            ->get()
            ->groupBy('song.title')
            ->map(function ($songSheets) {
                return $songSheets->sortBy('part_number')->values();
            });

        return view('pages.sheets-show', [
            'instrument' => $instrument,
            'sheets' => $sheets,
        ]);
    }

    public function download(Sheet $sheet)
    {
        $file = Storage::disk('sheets')->get($sheet->file_path);
        $name = $sheet->song->title . ' ' . $sheet->instrument->title . ' ' . $sheet->display_title . '.pdf';

        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
