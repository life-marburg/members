<?php

namespace App\Http\Controllers;

use App\Models\InstrumentGroup;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class InstrumentController extends Controller
{
    public function setInstrument(): Factory|View|Application
    {
        return view('profile.set-instrument', [
            'instruments' => InstrumentGroup::with('instruments')
                ->where('is_user_selectable', true)
                ->get(),
        ]);
    }

    public function saveInstrument(Request $request): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'instrument' => [
                'required',
                'exists:instrument_groups,id',
                function ($attribute, $value, $fail) {
                    if (! InstrumentGroup::whereId($value)->where('is_user_selectable', true)->exists()) {
                        $fail('The selected value is not an available option.');
                    }
                },
            ],
        ]);

        $request->user()->instrumentGroups()->attach($request->input('instrument'));

        return redirect(route('dashboard'));
    }
}
