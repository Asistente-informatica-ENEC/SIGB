<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CreateUser extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Crear Usuario';
    protected static ?string $title = 'Crear Nuevo Usuario';
    protected static ?string $navigationIcon = 'heroicon-o-user-add';
    protected static ?string $navigationGroup = 'Administración';
    protected static string $view = 'filament.pages.create-user';

    public ?string $name = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public static function canAccess(): bool
    {
        // Solo administrador puede acceder, puedes adaptar esta condición según tu lógica
        return auth()->user()->hasRole('administrador');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Si quieres que no aparezca en menú lateral, devuelve false
        return false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Nombre completo')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Correo electrónico')
                ->email()
                ->required()
                ->unique(User::class, 'email'),

            Forms\Components\TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->required()
                ->minLength(8)
                ->same('password_confirmation'),

            Forms\Components\TextInput::make('password_confirmation')
                ->label('Confirmar contraseña')
                ->password()
                ->required(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function submit()
    {
        $data = $this->form->getState();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        Notification::make()
            ->title('Usuario creado correctamente')
            ->success()
            ->send();

        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->form->fill();
    }
}
