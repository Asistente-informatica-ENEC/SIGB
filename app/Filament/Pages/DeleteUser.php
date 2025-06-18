<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DeleteUser extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Eliminar Usuario';
    protected static ?string $title = 'Eliminar Usuario';
    protected static ?string $navigationIcon = 'heroicon-o-user-minus';
    protected static ?string $navigationGroup = 'Administración';
    protected static string $view = 'filament.pages.delete-user';

    public ?int $user_id = null;

    // ✅ Esta propiedad soluciona el error del formulario
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Seleccionar usuario a eliminar')
                ->options(
                    User::where('id', '!=', auth()->id()) // evitar que se borre a sí mismo
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->required(),
        ])->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $user = User::find($data['user_id']);

        if (!$user) {
            Notification::make()
                ->title('Usuario no encontrado')
                ->danger()
                ->send();
            return;
        }

        $user->delete();

        Notification::make()
            ->title("Usuario {$user->name} eliminado correctamente")
            ->success()
            ->send();

        $this->reset('user_id');
        $this->form->fill();
    }
}
