<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswordCommand extends Command
{
    protected $signature = 'user:reset-password {email=admin@serwizo.com} {password?}';

    protected $description = 'Reset password and ensure active status for an administrator or user';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email [{$email}] not found.");
            return self::FAILURE;
        }

        if (!$password) {
            $password = $this->secret('Enter new password') ?: 'Admin123456!';
        }

        $user->password = Hash::make($password);
        $user->visible_password = $password;
        $user->is_active = true;
        $user->save();

        $this->info("Password for [{$email}] has been successfully reset!");
        $this->info("Account status is ACTIVE.");

        return self::SUCCESS;
    }
}
