<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- Civil Servant Temporary Credentials Badge --}}
        <div 
            x-data="{
                fillCredentials(email, pwd) {
                    const emailInput = document.querySelector('input[name=email]');
                    const pwdInput = document.querySelector('input[name=password]');
                    if (emailInput && pwdInput) {
                        emailInput.value = email;
                        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                        emailInput.dispatchEvent(new Event('change', { bubbles: true }));
                        pwdInput.value = pwd;
                        pwdInput.dispatchEvent(new Event('input', { bubbles: true }));
                        pwdInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }"
            class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-slate-800 dark:text-zinc-200 text-xs shadow-2xs space-y-2.5"
        >
            <div class="flex items-center gap-2">
                <span class="size-6 rounded-md bg-[#C8102E] text-white flex items-center justify-center font-bold text-xs shrink-0">
                    🏛️
                </span>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-xs">
                        Beamtinnen- & Beamten-Portal (Bundesservice)
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                        Temporäre Zugangsdaten für das interne Sachbearbeiter-Portal:
                    </p>
                </div>
            </div>

            <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-red-100 dark:border-red-900/40 space-y-1 font-mono text-[11px]">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">E-Mail:</span>
                    <strong class="text-slate-900 dark:text-white">beamter@bka.gv.at</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Passwort:</span>
                    <strong class="text-slate-900 dark:text-white">Bundeskanzleramt2030!</strong>
                </div>
            </div>

            <button 
                type="button" 
                @click="fillCredentials('beamter@bka.gv.at', 'Bundeskanzleramt2030!')"
                class="w-full py-1.5 px-3 rounded-lg bg-[#C8102E] hover:bg-red-700 text-white font-bold text-xs transition-colors shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer"
            >
                <span>🔑</span>
                <span>Beamten-Zugangsdaten 1-Klick einfügen</span>
            </button>
        </div>

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
