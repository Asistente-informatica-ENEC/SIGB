<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class UserProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $title = 'Cambio de contraseña';
    public static ?string $navigationLabel = 'Mi perfil';
    public static ?string $navigationIcon = 'heroicon-o-user-circle';
    public static ?string $navigationGroup = 'Configuración';
    public static ?int $navigationSort = 99;


    protected static string $view = 'filament.pages.user-profile';

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            // Aquí puedes llenar otros datos si fueran necesarios
        ]);
    }

    // Método form con firma correcta
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('formData') // Importantísimo para enlazar los datos
            ->model($this->getFormModel());
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('current_password')
                ->label('Contraseña actual')
                ->password()
                ->required()
                ->minLength(8)
                ->maxLength(255)
                ->autocomplete('current-password'),

            Forms\Components\TextInput::make('password')
                ->label('Nueva contraseña')
                ->password()
                ->minLength(8)
                ->maxLength(255)
                ->nullable()
                ->autocomplete('new-password'),

            Forms\Components\TextInput::make('password_confirmation')
                ->label('Confirmar contraseña')
                ->password()
                ->same('password')
                ->nullable()
                ->autocomplete('new-password'),
        ];
    }

    protected function getFormModel(): \App\Models\User
    {
        return auth()->user();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        // Validar contraseña actual
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->title('La contraseña actual es incorrecta')
                ->danger()
                ->send();
            return;
        }

        if (!empty($data['password'])) {
            if ($data['password'] !== $data['password_confirmation']) {
                Notification::make()
                    ->title('Las contraseñas no coinciden')
                    ->danger()
                    ->send();
                return;
            }

            $user->password = Hash::make($data['password']);
        } else {
            Notification::make()
                ->title('Debe ingresar una nueva contraseña')
                ->warning()
                ->send();
            return;
        }

        $user->save();

        Notification::make()
            ->title('Perfil actualizado correctamente')
            ->success()
            ->send();

        // Opcional: limpiar el formulario después de guardar
        $this->form->fill([]);
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\ButtonAction::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->color('primary'),
        ];
    }
}
