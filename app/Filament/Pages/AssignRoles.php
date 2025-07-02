<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Role;

class AssignRoles extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $title = 'Asignar Rol';
    protected static string $view = 'filament.pages.assign-roles';

    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; // No mostrar en menú lateral
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Seleccionar usuario')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\CheckboxList::make('roles')
                ->label('Asignar roles')
                ->options(Role::pluck('name', 'name'))
                ->columns(2)
                ->required(),
        ])->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $user = User::findOrFail($data['user_id']);
        $user->syncRoles($data['roles']);
        $user->save();

        Notification::make()
            ->title("Roles actualizados para {$user->name}")
            ->success()
            ->send();

        $this->reset('data');
        $this->form->fill();
    }
}
