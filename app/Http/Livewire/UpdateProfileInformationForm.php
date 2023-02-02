<?php

namespace App\Http\Livewire;

use App\Models\AdditionalEmails;
use App\Models\User;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm as LaravelUpdateProfileInformationForm;

class UpdateProfileInformationForm extends LaravelUpdateProfileInformationForm
{
    public function render()
    {
        return view('livewire.update-profile-information-form');
    }
    public function mount()
    {
        parent::mount();
        $this->state['additional_emails'] = \Auth::user()->additionalEmails()->get()->toArray();
    }

    public function updateProfileInformation(UpdatesUserProfileInformation $updater)
    {
        \Auth::user()->additionalEmails()->delete();

        $newAdditionalEmails = [];

        foreach ($this->state['additional_emails'] as $email) {
            $mail = new AdditionalEmails(['email' => $email['email']]);
            \Auth::user()->additionalEmails()->save($mail);
            $newAdditionalEmails[] = $mail->toArray();
        }

//        dd($newAdditionalEmails->());

        $this->state['additional_emails'] = $newAdditionalEmails;

        parent::updateProfileInformation($updater);
    }
}
