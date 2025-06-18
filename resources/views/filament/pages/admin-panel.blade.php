<x-filament::page>
    <div class="flex flex-wrap gap-4">
        <x-filament::button 
            tag="a" 
            href="{{ url('/admin/change-password') }}"
            color="primary"
        >
            Cambiar Contraseña
        </x-filament::button>

        <x-filament::button 
            tag="a" 
            href="{{ url('/admin/assign-roles') }}"
            color="primary"
        >
            Asignar Roles
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ url('/admin/create-user') }}"
            color="primary"
        >
            Crear Usuario
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ url('/admin/delete-user') }}"
            color="danger"
        >
            Eliminar Usuario
        </x-filament::button>
    </div>
</x-filament::page>


