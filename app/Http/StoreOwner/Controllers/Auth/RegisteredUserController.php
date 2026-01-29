<?php

namespace App\Http\StoreOwner\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StoreOwner;
use App\Models\Store;
use App\Models\StoreType;
use App\Models\UserGroup;
use App\Models\Department;
use App\Models\Module;
use App\Models\PaidModule;
use App\Models\EmailFormat;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $storeTypes = StoreType::where('status', 'Enable')->orderBy('store_type')->get();
        $userGroups = UserGroup::where('storeid', 0)->orderBy('groupname')->get();
        
        return view('storeowner.auth.register', compact('storeTypes', 'userGroups'));
    }

    /**
     * Handle an incoming registration request for store owner.
     */
    public function store(Request $request): RedirectResponse
    {
        $blockedDomains = [
            'gmail.com',
            'yahoo.com',
            'hotmail.com',
            'outlook.com',
            'live.com',
            'icloud.com',
            'aol.com',
            'msn.com',
            'protonmail.com',
            'yandex.com',
            'gmx.com',
        ];

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'emailid' => [
                'required',
                'string',
                'regex:/^([^@\s]+@[^@\s]+|[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/',
                'max:255',
                'unique:stoma_storeowner,emailid',
                function ($attribute, $value, $fail) use ($blockedDomains) {
                    $domain = strtolower((string) strrchr($value, '@'));
                    $domain = $domain ? ltrim($domain, '@') : '';
                    if ($domain && in_array($domain, $blockedDomains, true)) {
                        $fail('Please use a business email address (personal email domains are not allowed).');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'dateofbirth' => ['required', 'date'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address1' => ['required', 'string', 'max:255'],
            'address_lat' => ['nullable', 'string'],
            'address_lng' => ['nullable', 'string'],
            'address_formatted_address' => ['nullable', 'string'],
            'address_state' => ['nullable', 'string'],
            'address_city' => ['nullable', 'string'],
            'address_zipcode' => ['nullable', 'string'],
            'accept_terms' => ['required', 'accepted'],
        ], [
            'emailid.regex' => 'Please enter a business email or a business domain.',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        // Hash the password using Laravel's Hash
        $validated['password'] = Hash::make($validated['password']);

        // Map address fields from Google Places API
        if ($request->filled('address_city')) {
            $validated['city'] = $request->input('address_city');
        }
        if ($request->filled('address_state')) {
            $validated['state'] = $request->input('address_state');
        }
        if ($request->filled('address_zipcode')) {
            $validated['zipcode'] = $request->input('address_zipcode');
        }
        if ($request->filled('address_formatted_address')) {
            $validated['address2'] = $request->input('address_formatted_address');
        }

        // Set signup metadata
        $validated['signupdate'] = now();
        $validated['signupip'] = $request->ip();
        $validated['signupby'] = 0;
        $validated['editdate'] = now();
        $validated['editip'] = $request->ip();
        $validated['editby'] = 0;
        $validated['status'] = 'Pending Setup';
        $validated['accept_terms'] = 'Yes';

        $storeOwner = StoreOwner::create($validated);

        $this->sendActivationEmail($storeOwner);
        // Redirect to store registration page
        return redirect()->route('storeowner.register.store', ['ownerid' => $storeOwner->ownerid])
            ->with('success', 'Owner registered successfully. Please complete store registration.');
    }

    /**
     * Display the store registration view.
     */
    public function createStore(Request $request): View
    {
        $ownerid = $request->query('ownerid');
        
        if (!$ownerid || !StoreOwner::where('ownerid', $ownerid)->exists()) {
            abort(404, 'Owner not found');
        }

        $storeTypes = StoreType::where('status', 'Enable')->orderBy('store_type')->get();
        
        return view('storeowner.auth.register-store', compact('ownerid', 'storeTypes'));
    }

    /**
     * Handle store registration (step 2).
     */
    public function storeRegister(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ownerid' => ['required', 'exists:stoma_storeowner,ownerid'],
            'store_name' => ['required', 'string', 'max:255', 'unique:stoma_store,store_name'],
            'address1' => ['required', 'string', 'max:255'],
            'address_lat1' => ['nullable', 'string'],
            'address_lng1' => ['nullable', 'string'],
            'address_formatted_address1' => ['nullable', 'string'],
            'address_street_number1' => ['nullable', 'string'],
            'address_street1' => ['nullable', 'string'],
            'address_state1' => ['nullable', 'string'],
            'address_country1' => ['nullable', 'string'],
            'address_city1' => ['nullable', 'string'],
            'address_zipcode1' => ['nullable', 'string'],
            'address_location_type1' => ['nullable', 'string'],
            'weburl' => ['required', 'url', 'max:255'],
            'store_email' => ['required', 'email', 'max:255'],
        ]);

        $storeTypeId = StoreType::where('status', 'Enable')
            ->orderBy('store_type')
            ->value('typeid');

        if (! $storeTypeId) {
            return redirect()->back()->withErrors([
                'store_name' => 'No active store types are configured. Please contact admin.',
            ])->withInput();
        }

        // Prepare store data
        $storeData = [
            'storeownerid' => $validated['ownerid'],
            'store_name' => $validated['store_name'],
            'typeid' => $storeTypeId,
            'full_google_address' => $validated['address1'],
            'latitude' => $validated['address_lat1'] ?? null,
            'longitude' => $validated['address_lng1'] ?? null,
            'website_url' => $validated['weburl'],
            'store_email' => $validated['store_email'],
            'logofile' => null,
            'insertdate' => now(),
            'insertip' => $request->ip(),
            'insertby' => 0,
            'editdate' => now(),
            'editip' => $request->ip(),
            'editby' => 0,
            'status' => 'Pending Setup',
        ];

        $store = Store::create($storeData);
        $storeid = $store->storeid;

        // Copy departments from store type to store
        // Get departments by storetypeid (matching CI logic)
        $departments = Department::where('storetypeid', $storeTypeId)
            ->where('status', 'Enable')
            ->get();

        foreach ($departments as $department) {
            Department::create([
                'department' => $department->department,
                'storetypeid' => $department->storetypeid,
                'storeid' => $storeid,
                'roster_max_time' => $department->roster_max_time,
                'status' => $department->status,
            ]);
        }

        // TODO: Copy sale_target data if needed (skipping for now)
        
        // Create paid_module entries for modules with free_days > 0
        // Only create if they don't already exist for this store
        $modules = Module::where('status', 'Enable')->get();
        if ($modules->count() > 0) {
            foreach ($modules as $module) {
                if ($module->free_days > 0) {
                    // Check if paid_module entry already exists for this store and module
                    $existingPaidModule = PaidModule::where('storeid', $storeid)
                        ->where('moduleid', $module->moduleid)
                        ->first();
                    
                    if (!$existingPaidModule) {
                        $purchaseDate = Carbon::now()->startOfDay();
                        $expireDate = Carbon::now()->addDays($module->free_days)->endOfDay();
                        
                        PaidModule::create([
                            'storeid' => $storeid,
                            'moduleid' => $module->moduleid,
                            'purchase_date' => $purchaseDate,
                            'expire_date' => $expireDate,
                            'paid_amount' => '0.00',
                            'status' => 'Enable',
                            'insertdatetime' => now(),
                            'insertip' => $request->ip(),
                            'isTrial' => 1,
                        ]);
                    }
                }
            }
        }
        
        // TODO: Send notification email to admin

        return redirect()->route('storeowner.login')
            ->with('success', 'You are registered successfully. You can login your account after activation.');
    }

    private function sendActivationEmail(StoreOwner $storeOwner): void
    {
        if (! filter_var($storeOwner->emailid, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $emailFormat = EmailFormat::find(1);
        if (! $emailFormat) {
            return;
        }

        $activationLink = route('storeowner.activate', [
            'token' => base64_encode((string) $storeOwner->ownerid),
        ]);

        $sitename = config('app.name', 'MaxiManage.com');
        $replacements = [
            '%username%' => trim($storeOwner->firstname . ' ' . $storeOwner->lastname),
            '%useremail%' => $storeOwner->emailid,
            '%sitename%' => $sitename,
            '%userlink%' => $activationLink,
            '%activationlink%' => $activationLink,
        ];

        $subject = strtr($emailFormat->varsubject, $replacements);
        $body = strtr($emailFormat->varmailformat, $replacements);

        Mail::send([], [], function ($message) use ($storeOwner, $subject, $body) {
            $message->to($storeOwner->emailid)
                ->subject($subject)
                ->setBody($body, 'text/html');
        });
    }
}

