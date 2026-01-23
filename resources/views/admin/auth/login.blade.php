<!-- REQUIRED: Google reCAPTCHA script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<x-guest-layout>
    <div class="w-full max-w-md bg-white/95 rounded-lg shadow-lg p-8">
        <h2 class="text-center mb-6 font-medium text-lg">
            Enter Email and Password to Continue
        </h2>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Display all validation errors (email, password, captcha) -->
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                    :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4 relative">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full pr-16" type="password"
                    name="password" required autocomplete="current-password" />
                <button type="button" onclick="togglePassword()" 
                        class="absolute right-3 top-9 text-sm text-gray-600 focus:outline-none">
                    Show
                </button>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me 
            <div class="mt-4 flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                    {{ __('Remember Me') }}
                </label>
            </div>-->

            @unless (! config('services.recaptcha.enabled') || app()->environment('local'))
                <!-- Google reCAPTCHA -->
                <div class="mt-4">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                    @error('g-recaptcha-response')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endunless

            <!-- Submit Button -->
            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const btn = event.currentTarget;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                btn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                btn.textContent = 'Show';
            }
        }
    </script>
</x-guest-layout>
