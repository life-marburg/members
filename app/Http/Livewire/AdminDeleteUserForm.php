<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Rights;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Livewire\Component;

// Copied and adjusted from \Laravel\Jetstream\Http\Livewire\DeleteUserForm

class AdminDeleteUserForm extends Component
{
    /**
     * Indicates if user deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingUserDeletion = false;

    /**
     * The user's current password.
     *
     * @var string
     */
    public $password = '';

    /**
     * The user to delete.
     *
     * @var User
     */
    public User $user;

    /**
     * Confirm that the user would like to delete this account.
     *
     * @return void
     */
    public function confirmUserDeletion()
    {
        $this->resetErrorBag();

        $this->password = '';

        $this->dispatchBrowserEvent('confirming-delete-user');

        $this->confirmingUserDeletion = true;
    }

    public function deleteUser(Request $request, DeletesUsers $deleter, StatefulGuard $auth)
    {
        $this->resetErrorBag();

        if (!Hash::check($this->password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        if (!Auth::user()->hasPermissionTo(Rights::P_DELETE_ACCOUNTS)) {
            throw ValidationException::withMessages([
                '' => [__('You don\'t have the right to do this.')],
            ]);
        }

        $deleter->delete($this->user->fresh());

        return redirect(route('members.index'))
            ->with('success', __('The user :user has been deleted successfully!', ['user' => $this->user->name]));
    }

    public function render(): Factory|View|Application
    {
        return view('pages.members.admin-delete-user-form');
    }
}
