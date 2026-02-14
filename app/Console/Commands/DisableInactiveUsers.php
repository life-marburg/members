<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AccountAlmostInactive;
use Illuminate\Console\Command;

class DisableInactiveUsers extends Command
{
    protected $signature = 'users:disable-inactive';

    protected $description = 'Disables all users if they did not use their account within the period specified in their account.';

    public function handle()
    {
        /** @var User[] $users */
        $users = User::whereNotNull('disable_after_days')->get();

        foreach ($users as $user) {
            if ($user->last_active_at !== null && $user->last_active_at->clone()->addDays($user->disable_after_days)->isPast()) {
                $user->status = User::STATUS_LOCKED;
                $user->save();
                $this->info('Locked account of user '.$user->id);

                continue;
            }

            if ($user->last_active_at !== null && $user->last_active_at->clone()->addDays($user->disable_after_days - 3)->isPast()) {
                $user->notify(new AccountAlmostInactive);
                $this->info('Sent account almost inactive notification to user '.$user->id);
            }
        }

        return Command::SUCCESS;
    }
}
