<?php

namespace App\Http\Controllers;

use App\Instruments;
use App\Models\InstrumentGroup;
use App\Models\User;
use App\Notifications\UserIsWaitingForActivation;
use App\Rights;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class InstrumentController extends Controller
{
    public function setInstrument(): Factory|View|Application
    {
        return view('profile.set-instrument', [
            'instruments' => InstrumentGroup::with('instruments')->get(),
        ]);
    }

    public function saveInstrument(Request $request): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'instrument' => ['required', 'exists:instrument_groups,id']
        ]);

        $request->user()->instrumentGroups()->attach($request->input('instrument'));

        /** @var User[] $admins */
        $admins = Role::findByName(Rights::R_ADMIN, 'web')->users()->get();
        foreach ($admins as $admin) {
            $admin->notify(new UserIsWaitingForActivation($request->user()));
        }

        return redirect(route('dashboard'));
    }
}
