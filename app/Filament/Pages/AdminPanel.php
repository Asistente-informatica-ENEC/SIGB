<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdminPanel extends Page
{
    public static ?string $navigationLabel = 'Gestión de usuarios';
    protected static ?string $title = 'Gestión de usuarios';
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    public static ?string $navigationGroup = 'Configuración';
    protected static string $view = 'filament.pages.admin-panel';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }
}
