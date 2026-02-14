<?php

namespace App\Http\Controllers;

use App\Models\InstrumentGroup;
use App\Models\Sheet;
use App\Models\Song;
use App\Models\SongSet;
use App\Rights;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SheetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS)) {
            $instrumentGroupIds = InstrumentGroup::pluck('id');
        } else {
            $instrumentGroupIds = $user->instrumentGroups()->pluck('id');
        }

        $songs = Song::whereHas('sheets.instrument', function ($query) use ($instrumentGroupIds) {
            $query->whereIn('instrument_group_id', $instrumentGroupIds);
        })
            ->with('songSets')
            ->orderBy('title')
            ->get()
            ->map(fn ($song) => [
                'id' => $song->id,
                'title' => $song->title,
                'is_new' => $song->is_new,
                'sets' => $song->songSets->map(fn ($set) => [
                    'id' => $set->id,
                    'position' => $set->pivot->position,
                ])->toArray(),
            ]);

        return view('pages.sheets', [
            'songs' => $songs,
            'songSets' => SongSet::orderBy('title')->get(),
        ]);
    }

    public function show(Song $song)
    {
        $user = Auth::user();

        if ($user->hasPermissionTo(Rights::P_VIEW_ALL_INSTRUMENTS)) {
            $instrumentGroupIds = InstrumentGroup::pluck('id');
        } else {
            $instrumentGroupIds = $user->instrumentGroups()->pluck('id');
        }

        $sheets = $song->sheets()
            ->whereHas('instrument', function ($query) use ($instrumentGroupIds) {
                $query->whereIn('instrument_group_id', $instrumentGroupIds);
            })
            ->with(['instrument.instrumentGroup'])
            ->get()
            ->groupBy('instrument.instrumentGroup.title')
            ->map(fn ($group) => $group->groupBy('instrument.title'));

        return view('pages.sheets-show', [
            'song' => $song,
            'sheetsByGroup' => $sheets,
        ]);
    }

    public function download(Sheet $sheet)
    {
        $file = Storage::disk('sheets')->get($sheet->file_path);
        $name = $sheet->song->title.' '.$sheet->instrument->title.' '.$sheet->display_title.'.pdf';

        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ]);
    }
}
