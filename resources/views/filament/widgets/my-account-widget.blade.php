{{-- resources/views/filament/widgets/my-account-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img
                    src="{{ auth()->user()->avatar_url ?? asset('images/logo2.png') }}"
                    alt="{{ auth()->user()->name }}"
                    class="w-16 h-16 rounded-full object-cover border border-gray-300 dark:border-gray-600"
                />

                <div>
                    <h2 class="text-lg font-bold leading-6 text-gray-900 dark:text-white">
                        Bienvenido/a,
                        <span class="block text-base font-medium text-gray-600 dark:text-gray-300">
                            {{ auth()->user()->name }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </div>

            <form action="{{ filament()->getLogoutUrl() }}" method="post" onsubmit="return confirm('¿Estás seguro de que deseas salir?')">
                @csrf
                <x-filament::button
                    type="submit"
                    icon="heroicon-m-arrow-right-on-rectangle"
                    size="sm"
                    color="danger"
                >
                    Cerrar sesión
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
