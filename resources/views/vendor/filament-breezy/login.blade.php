<x-filament-breezy::auth-card action="authenticate">

    <div class="flex w-full justify-center">
        <x-filament::brand />
    </div>

    <div>
        <h2 class="text-center text-2xl font-bold tracking-tight">
            {{ __('filament::login.heading') }}
        </h2>
        @if (config('filament-breezy.enable_registration'))
            <p class="mt-2 text-center text-sm">
                {{ __('filament-breezy::default.or') }}
                <a class="text-primary-600" href="{{ route(config('filament-breezy.route_group_prefix') . 'register') }}">
                    {{ strtolower(__('filament-breezy::default.registration.heading')) }}
                </a>
            </p>
        @endif
    </div>

    {{ $this->form }}

    <x-filament::button type="submit" class="w-full" form="authenticate">
        {{ __('filament::login.buttons.submit.label') }}
    </x-filament::button>

    {{-- <div class="text-center">
        <a class="text-primary-600 hover:text-primary-700" href="{{route(config('filament-breezy.route_group_prefix').'password.request')}}">{{ __('filament-breezy::default.login.forgot_password_link') }}</a>
    </div> --}}
</x-filament-breezy::auth-card>
