<?php

namespace App\Http\StoreOwner\Controllers\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\StoreOwner;
use App\Models\Store;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the login form.
     */
    public function rules(): array
    {
        return [
            'emailid' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required'], // captcha required
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // CAPTCHA check
        if (! $this->verifyRecaptcha()) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Captcha verification failed.',
            ]);
        }

        $credentials = $this->only('emailid', 'password');

        // Fetch store owner by email
        $storeOwner = StoreOwner::where('emailid', $credentials['emailid'])->first();

        if (!$storeOwner) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'emailid' => trans('auth.failed'),
            ]);
        }

        // Check password (Laravel Hash or backward-compatible base64)
        $passwordValid = false;
        if (Hash::check($credentials['password'], $storeOwner->password)) {
            $passwordValid = true;
        } elseif (base64_encode($credentials['password']) === $storeOwner->password) {
            // Rehash the password with Laravel Hash
            $storeOwner->password = Hash::make($credentials['password']);
            $storeOwner->save();
            $passwordValid = true;
        }

        if (!$passwordValid) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'emailid' => trans('auth.failed'),
            ]);
        }

        // Check owner status
        if ($storeOwner->status !== 'Active') {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'emailid' => 'Your account is not active. Please activate it before login.',
            ]);
        }

        // Check store status
        $activeStore = Store::where('storeownerid', $storeOwner->ownerid)
            ->where('status', 'Active')
            ->first();

        if (!$activeStore) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'emailid' => 'Your store is not verified. Please contact admin.',
            ]);
        }

        // Log in store owner
        Auth::guard('storeowner')->login($storeOwner, $this->boolean('remember'));

        // Store storeid in session
        session(['storeid' => $activeStore->storeid]);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'emailid' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Rate limiting key for the login request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('emailid')) . '|' . $this->ip() . '|storeowner');
    }

    /**
     * Verify Google reCAPTCHA v2
     */
    private function verifyRecaptcha(): bool
    {
        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $this->input('g-recaptcha-response'),
                'remoteip' => $this->ip(),
            ]
        );

        return $response->json('success') === true;
    }
}
