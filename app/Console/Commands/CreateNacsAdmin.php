<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNacsAdmin extends Command
{
    protected $signature = 'nacs:create-admin {--name=} {--email=}';

    protected $description = 'Create or update an authorized NACS-Phil administrator account';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Administrator name'));
        $email = strtolower((string) ($this->option('email') ?: $this->ask('Administrator email')));
        $password = (string) $this->secret('Password (minimum 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(compact('name', 'email', 'password', 'confirmation'), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'password' => ['required', 'string', 'min:12', 'same:confirmation'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info('NACS-Phil administrator account is ready.');
        return self::SUCCESS;
    }
}
