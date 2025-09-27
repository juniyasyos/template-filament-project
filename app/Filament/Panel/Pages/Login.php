<?php

namespace App\Filament\Panel\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\App as AppFacade;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (AppFacade::environment('local')) {
            // Prefill credentials for local development only.
            $this->form->fill([
                'email' => 'admin@gmail.com',
                'password' => 'password',
            ]);
        }
    }
}
