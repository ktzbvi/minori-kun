<?php

namespace App\Console\Commands;

use App\Enums\AccountState;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UpsertAdmin extends Command
{
    protected $signature = 'admin:upsert {email : Admin email address}';

    protected $description = 'Create or rotate the single-role Admin account without storing credentials.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $password = (string) $this->secret('Password');
        $validation = Validator::make(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::query()->where('email', $email)->first();
        if ($existing && $existing->role !== UserRole::Admin) {
            $this->error('That email already belongs to another portal role.');

            return self::FAILURE;
        }

        User::query()->updateOrCreate(['email' => $email], [
            'role' => UserRole::Admin,
            'password' => Hash::make($password),
            'account_state' => AccountState::Active,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ]);

        $this->info('Admin account is ready.');

        return self::SUCCESS;
    }
}
