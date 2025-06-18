<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class ChangePassword extends Page
{
    public static ?string $navigationLabel = 'Gestión de usuarios';
    protected static ?string $title = 'Cambiar contraseña';
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    public static ?string $navigationGroup = 'Configuración';
    protected static string $view = 'filament.pages.change-password';

    public ?int $user_id = null;
    public ?string $new_password = null;
    public ?string $new_password_confirmation = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('cambiar contraseñas');
    }

    // <-- Aquí está la modificación:
    public static function shouldRegisterNavigation(): bool
    {
        return false; // No mostrar en menú lateral
    }

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Seleccionar usuario')
                ->options(User::all()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('new_password')
                ->label('Nueva contraseña')
                ->password()
                ->required()
                ->minLength(8)
                ->same('new_password_confirmation'),

            Forms\Components\TextInput::make('new_password_confirmation')
                ->label('Confirmar contraseña')
                ->password()
                ->required(),
        ])->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $user = User::findOrFail($data['user_id']);
        $user->password = bcrypt($data['new_password']);
        $user->save();

        Notification::make()
            ->title("Contraseña actualizada para {$user->name}")
            ->success()
            ->send();

        $this->reset('user_id', 'new_password', 'new_password_confirmation');
        $this->form->fill();
    }
}
