<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentUiCustomizationProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::registerRenderHook(
            'auth.login.form.before',
            fn () => view('auth.custom-login-image'),
        );
    }
}

