<?php

namespace App\Http\StoreOwner\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\PaidModule;
use App\Models\PaymentCard;
use App\Models\Store;
use App\Models\UserGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Carbon\Carbon;
use Stripe\StripeClient;

class ModuleSettingController extends Controller
{
    /**
     * Display a listing of the modules.
     */
    public function index(): View
    {
        $user = auth('storeowner')->user();
        $ownerid = $user->ownerid;
        $storeid = session('storeid', 0);
        
        // Get all stores for this owner (for multi-store check)
        $multistore = Store::where('storeownerid', $ownerid)
            ->where('status', 'Active')
            ->get();
        $storecount = $multistore->count();
        
        $curDate = Carbon::now();

        // Get latest paid_module per module (for this store)
        $latestPaidModules = PaidModule::with('module.dependencies')
            ->where('storeid', $storeid)
            ->orderBy('insertdatetime', 'desc')
            ->get()
            ->groupBy('moduleid')
            ->map->first()
            ->values();

        $this->autoRenewExpiredModules($latestPaidModules, $storeid);

        $latestPaidModules = PaidModule::with('module.dependencies')
            ->where('storeid', $storeid)
            ->orderBy('insertdatetime', 'desc')
            ->get()
            ->groupBy('moduleid')
            ->map->first()
            ->values();

        // Installed modules (active, not expired)
        $installedModules = $latestPaidModules->filter(function (PaidModule $pm) use ($curDate) {
            if (! $pm->purchase_date || ! $pm->expire_date) {
                return false;
            }

            return $pm->purchase_date->startOfDay() <= $curDate
                && $pm->expire_date->endOfDay() >= $curDate;
        })->values();

        $installedModules = $installedModules->sortBy(function (PaidModule $pm) {
            $name = strtolower($pm->module->module ?? '');
            return $name === 'employee' ? 0 : 1;
        })->values();

        $installedModuleIds = $installedModules->pluck('moduleid')->all();

        // Available modules (not installed)
        $availableModules = Module::with('dependencies')
            ->where('status', 'Enable')
            ->whereNotIn('moduleid', $installedModuleIds)
            ->get();

        // Renewal due list (within 60 days)
        $renewalsDue = $latestPaidModules->filter(function (PaidModule $pm) use ($curDate) {
            if (! $pm->expire_date) {
                return false;
            }

            $daysRemaining = $curDate->diffInDays($pm->expire_date, false);
            return $daysRemaining <= 60;
        })->map(function (PaidModule $pm) use ($curDate) {
            $pm->days_remaining = $curDate->diffInDays($pm->expire_date, false);
            return $pm;
        })->values();

        $renewalsDue = $renewalsDue->sortBy(function (PaidModule $pm) {
            $name = strtolower($pm->module->module ?? '');
            return $name === 'employee' ? 0 : 1;
        })->values();

        // Billing history (all paid modules)
        $billingItems = PaidModule::with('module')
            ->where('storeid', $storeid)
            ->orderBy('purchase_date', 'desc')
            ->get();

        $paymentCards = PaymentCard::where('storeid', $storeid)
            ->where('ownerid', $ownerid)
            ->orderBy('insertdate', 'desc')
            ->get();
        
        // Get all installed modules (latest by insertdatetime, grouped by moduleid)
        $allinstallModule = DB::select("
            SELECT pm1.* 
            FROM stoma_paid_module pm1
            JOIN (
                SELECT moduleid, MAX(insertdatetime) as timestamp 
                FROM stoma_paid_module 
                WHERE storeid = ?
                GROUP BY moduleid
            ) pm2 
            ON pm1.moduleid = pm2.moduleid 
            AND pm1.insertdatetime = pm2.timestamp 
            WHERE pm1.storeid = ?
        ", [$storeid, $storeid]);
        
        // Calculate time differences for "Last Updated"
        $diff = [];
        if (count($allinstallModule) > 0) {
            foreach ($allinstallModule as $i => $pm) {
                $startdate = Carbon::now();
                $enddate = Carbon::parse($pm->insertdatetime);
                $interval = $startdate->diff($enddate);
                
                if ($interval->y > 0) {
                    $diff[] = $interval->y . ' Year ago';
                } elseif ($interval->m > 0) {
                    $diff[] = $interval->m . ' Month ago';
                } elseif ($interval->d > 0) {
                    $diff[] = $interval->d . ' days ago';
                } elseif ($interval->h > 0) {
                    $diff[] = $interval->h . ' hour ago';
                } elseif ($interval->i > 0) {
                    $diff[] = $interval->i . ' minute ago';
                } else {
                    $diff[] = '';
                }
            }
        }
        
        // Get cart modules (if cart table exists)
        $cartModule = [];
        try {
            $cartModules = DB::table('stoma_cart')
                ->where('store_id', $storeid)
                ->where('owner_id', $ownerid)
                ->pluck('module_id')
                ->toArray();
            $cartModule = $cartModules;
        } catch (\Exception $e) {
            // Cart table might not exist yet
            $cartModule = [];
        }
        
        return view('storeowner.modulesetting.index', compact(
            'installedModules',
            'availableModules',
            'renewalsDue',
            'billingItems',
            'paymentCards',
            'allinstallModule',
            'diff',
            'storecount',
            'cartModule'
        ));
    }

    /**
     * Download billing invoice PDF for a paid module.
     */
    public function downloadInvoice(int $pmid)
    {
        $user = auth('storeowner')->user();
        $storeid = session('storeid', 0);

        $paidModule = PaidModule::with('module')
            ->where('pmid', $pmid)
            ->where('storeid', $storeid)
            ->firstOrFail();

        $store = Store::with('storeOwner')->find($storeid);
        $owner = $store?->storeOwner ?? $user;

        $invoiceNumber = $paidModule->transactionid ?: ('INV-' . $paidModule->pmid);
        $filename = 'invoice-' . $invoiceNumber . '.pdf';

        $pdf = Pdf::loadView('storeowner.modulesetting.invoice-pdf', [
            'paidModule' => $paidModule,
            'store' => $store,
            'owner' => $owner,
            'invoiceNumber' => $invoiceNumber,
        ]);

        return $pdf->download($filename);
    }

    /**
     * Show the cart review & checkout page.
     */
    public function checkout(): View
    {
        $user = auth('storeowner')->user();
        $storeid = session('storeid', 0);

        $curDate = Carbon::now();
        $latestPaidModules = PaidModule::with('module')
            ->where('storeid', $storeid)
            ->orderBy('insertdatetime', 'desc')
            ->get()
            ->groupBy('moduleid')
            ->map->first()
            ->values();

        $installedModules = $latestPaidModules->filter(function (PaidModule $pm) use ($curDate) {
            if (! $pm->purchase_date || ! $pm->expire_date) {
                return false;
            }

            return $pm->purchase_date->startOfDay() <= $curDate
                && $pm->expire_date->endOfDay() >= $curDate;
        })->values();

        $installedModuleIds = $installedModules->pluck('moduleid')->all();

        $availableModules = Module::where('status', 'Enable')
            ->whereNotIn('moduleid', $installedModuleIds)
            ->get();

        $summaryItems = $availableModules->map(function (Module $module) {
            return (object) [
                'module' => $module->module ?? 'Module',
                'cycle' => 'monthly',
                'amount' => (float) ($module->price_1months ?? 0),
            ];
        });

        $subtotal = $summaryItems->sum('amount');
        $vatRate = 0.23;
        $vatAmount = $subtotal * $vatRate;
        $total = $subtotal + $vatAmount;

        return view('storeowner.modulesetting.checkout', compact(
            'availableModules',
            'summaryItems',
            'subtotal',
            'vatRate',
            'vatAmount',
            'total'
        ));
    }

    /**
     * Show the checkout payment page.
     */
    public function checkoutPayment(): View
    {
        $user = auth('storeowner')->user();
        $ownerid = $user->ownerid;
        $storeid = session('storeid', 0);

        $curDate = Carbon::now();
        $latestPaidModules = PaidModule::with('module')
            ->where('storeid', $storeid)
            ->orderBy('insertdatetime', 'desc')
            ->get()
            ->groupBy('moduleid')
            ->map->first()
            ->values();

        $installedModules = $latestPaidModules->filter(function (PaidModule $pm) use ($curDate) {
            if (! $pm->purchase_date || ! $pm->expire_date) {
                return false;
            }

            return $pm->purchase_date->startOfDay() <= $curDate
                && $pm->expire_date->endOfDay() >= $curDate;
        })->values();

        $installedModuleIds = $installedModules->pluck('moduleid')->all();

        $availableModules = Module::where('status', 'Enable')
            ->whereNotIn('moduleid', $installedModuleIds)
            ->get();

        $summaryItems = $availableModules->map(function (Module $module) {
            return (object) [
                'module' => $module->module ?? 'Module',
                'cycle' => 'monthly',
                'amount' => (float) ($module->price_1months ?? 0),
            ];
        });

        $subtotal = $summaryItems->sum('amount');
        $vatRate = 0.23;
        $vatAmount = $subtotal * $vatRate;
        $total = $subtotal + $vatAmount;

        $paymentCards = PaymentCard::where('storeid', $storeid)
            ->where('ownerid', $ownerid)
            ->orderBy('insertdate', 'desc')
            ->get();

        $invoiceNumber = 'INV-' . str_pad((string) $storeid, 6, '0', STR_PAD_LEFT);

        return view('storeowner.modulesetting.checkout-payment', compact(
            'paymentCards',
            'summaryItems',
            'subtotal',
            'vatRate',
            'vatAmount',
            'total',
            'invoiceNumber'
        ));
    }

    /**
     * View module access for a user group (AJAX).
     */
    public function view(Request $request)
    {
        $usergroupid = $request->input('usergroupid');
        $storeid = session('storeid', 0);
        
        // Get module access data for this user group
        $curDate = date('Y-m-d');
        
        // Get user group name
        $userGroup = UserGroup::find($usergroupid);
        if (!$userGroup) {
            return response()->json(['error' => 'User group not found'], 404);
        }
        
        $usergroup = DB::table('stoma_paid_module as pm')
            ->join('stoma_module as m', 'm.moduleid', '=', 'pm.moduleid')
            ->leftJoin('stoma_module_access as ma', function($join) use ($storeid, $usergroupid) {
                $join->on('ma.moduleid', '=', 'pm.moduleid')
                     ->where('ma.storeid', '=', $storeid)
                     ->where('ma.usergroupid', '=', $usergroupid);
            })
            ->where('pm.storeid', $storeid)
            ->whereDate('pm.purchase_date', '<=', $curDate)
            ->whereDate('pm.expire_date', '>=', $curDate)
            ->select('ma.level', 'm.module', 'm.moduleid')
            ->groupBy('m.moduleid', 'ma.level', 'm.module')
            ->get()
            ->map(function($item) use ($userGroup) {
                return (object) [
                    'moduleid' => $item->moduleid,
                    'module' => $item->module,
                    'level' => $item->level ?? 'None',
                    'groupname' => $userGroup->groupname,
                    'usergroupid' => $userGroup->usergroupid,
                ];
            });
        
        // If no modules found from paid_module, get from module_access directly
        if ($usergroup->isEmpty()) {
            $usergroup = DB::table('stoma_module_access as ma')
                ->join('stoma_module as m', 'm.moduleid', '=', 'ma.moduleid')
                ->where('ma.storeid', $storeid)
                ->where('ma.usergroupid', $usergroupid)
                ->select('ma.level', 'm.module', 'm.moduleid')
                ->get()
                ->map(function($item) use ($userGroup) {
                    return (object) [
                        'moduleid' => $item->moduleid,
                        'module' => $item->module,
                        'level' => $item->level ?? 'None',
                        'groupname' => $userGroup->groupname,
                        'usergroupid' => $userGroup->usergroupid,
                    ];
                });
        }
        
        return view('storeowner.modulesetting.view', compact('usergroup'));
    }

    /**
     * Show the form for editing module access levels.
     */
    public function edit($usergroupid): View
    {
        $usergroupid = base64_decode($usergroupid);
        $storeid = session('storeid', 0);
        
        // Get user group
        $userGroup = UserGroup::find($usergroupid);
        if (!$userGroup) {
            abort(404, 'User group not found');
        }
        
        // Get module access data for this user group
        $curDate = date('Y-m-d');
        
        $usergroup = DB::table('stoma_paid_module as pm')
            ->join('stoma_module as m', 'm.moduleid', '=', 'pm.moduleid')
            ->leftJoin('stoma_module_access as ma', function($join) use ($storeid, $usergroupid) {
                $join->on('ma.moduleid', '=', 'pm.moduleid')
                     ->where('ma.storeid', '=', $storeid)
                     ->where('ma.usergroupid', '=', $usergroupid);
            })
            ->where('pm.storeid', $storeid)
            ->whereDate('pm.purchase_date', '<=', $curDate)
            ->whereDate('pm.expire_date', '>=', $curDate)
            ->select('ma.level', 'm.module', 'm.moduleid')
            ->groupBy('m.moduleid', 'ma.level', 'm.module')
            ->get()
            ->map(function($item) use ($userGroup) {
                return (object) [
                    'moduleid' => $item->moduleid,
                    'module' => $item->module,
                    'level' => $item->level ?? 'None',
                    'groupname' => $userGroup->groupname,
                    'usergroupid' => $userGroup->usergroupid,
                ];
            });
        
        // If no modules found from paid_module, get from module_access directly
        if ($usergroup->isEmpty()) {
            $usergroup = DB::table('stoma_module_access as ma')
                ->join('stoma_module as m', 'm.moduleid', '=', 'ma.moduleid')
                ->where('ma.storeid', $storeid)
                ->where('ma.usergroupid', $usergroupid)
                ->select('ma.level', 'm.module', 'm.moduleid')
                ->get()
                ->map(function($item) use ($userGroup) {
                    return (object) [
                        'moduleid' => $item->moduleid,
                        'module' => $item->module,
                        'level' => $item->level ?? 'None',
                        'groupname' => $userGroup->groupname,
                        'usergroupid' => $userGroup->usergroupid,
                    ];
                });
        }
        
        $title = $userGroup->groupname;
        
        return view('storeowner.modulesetting.edit', compact('usergroup', 'title'));
    }

    /**
     * Update module access levels.
     */
    public function update(Request $request): RedirectResponse
    {
        $usergroupid = base64_decode($request->input('usergroupid'));
        $totalmodule = $request->input('totalmodule');
        $accesslevel = $request->input('accesslevel', []);
        
        $storeid = session('storeid', 0);
        
        // Get all module IDs
        $moduleids = [];
        for ($i = 0; $i < $totalmodule; $i++) {
            $moduleids[] = $request->input('hdnmodule' . $i);
        }
        
        // Update module access levels
        for ($i = 0; $i < count($moduleids); $i++) {
            if (isset($moduleids[$i]) && isset($accesslevel[$i])) {
                DB::table('stoma_module_access')
                    ->where('moduleid', $moduleids[$i])
                    ->where('usergroupid', $usergroupid)
                    ->where('storeid', $storeid)
                    ->update([
                        'level' => $accesslevel[$i],
                        'editdate' => now(),
                        'editip' => $request->ip(),
                    ]);
            }
        }
        
        return redirect()->route('storeowner.modulesetting.index')
            ->with('success', 'Module Setting Updated Successfully.');
    }

    /**
     * Handle module installation request.
     */
    public function install(Request $request): RedirectResponse
    {
        $moduleid = base64_decode($request->input('moduleid'));
        $status = $request->input('status'); // For multi-store discount
        $install = $request->input('install'); // For single store
        $plan = $request->input('plan', 'monthly');
        
        $storeid = session('storeid', 0);
        $user = auth('storeowner')->user();
        
        if ($install !== 'Yes' && $status !== 'Yes') {
            return redirect()->route('storeowner.modulesetting.index')
                ->with('info', 'Installation cancelled.');
        }
        
        // Check if module exists
        $module = Module::with('dependencies')->find($moduleid);
        if (!$module) {
            return redirect()->route('storeowner.modulesetting.index')
                ->with('error', 'Module not found.');
        }

        $activeModuleIds = $this->getActiveModuleIds($storeid);
        $missingDependencies = $module->dependencies
            ->whereNotIn('moduleid', $activeModuleIds)
            ->pluck('module')
            ->all();

        if (! empty($missingDependencies)) {
            return redirect()->route('storeowner.modulesetting.index')
                ->with('error', 'Please install required modules first: ' . implode(', ', $missingDependencies) . '.');
        }
        
        // Check if module is already installed (active, not expired)
        $curDate = date('Y-m-d');
        $existingPaidModule = PaidModule::where('storeid', $storeid)
            ->where('moduleid', $moduleid)
            ->whereDate('purchase_date', '<=', $curDate)
            ->whereDate('expire_date', '>=', $curDate)
            ->first();
        
        if ($existingPaidModule) {
            return redirect()->route('storeowner.modulesetting.index')
                ->with('info', 'Module is already installed.');
        }
        
        // Calculate purchase and expire dates
        $purchaseDate = Carbon::now()->startOfDay();
        
        // If module has free_days, use that; otherwise default to 30 days (1 month)
        if ($module->free_days > 0) {
            $expireDate = Carbon::now()->addDays($module->free_days)->endOfDay();
            $isTrial = 1;
            $paidAmount = '0.00';
        } else {
            $months = $plan === 'yearly' ? 12 : 1;
            $expireDate = Carbon::now()->addMonths($months)->endOfDay();
            $isTrial = 0;
            $paidAmount = $plan === 'yearly'
                ? ($module->price_12months ?? '0.00')
                : ($module->price_1months ?? '0.00');
        }
        
        // Apply discount for multi-store owners (20% discount)
        if ($status === 'Yes') {
            // Multi-store discount logic
            $multistore = Store::where('storeownerid', $user->ownerid)
                ->where('status', 'Active')
                ->count();
            
            if ($multistore > 1 && $paidAmount > 0) {
                $discount = ($paidAmount * 20) / 100;
                $paidAmount = $paidAmount - $discount;
            }
        }
        
        // Create paid_module entry
        PaidModule::create([
            'storeid' => $storeid,
            'moduleid' => $moduleid,
            'purchase_date' => $purchaseDate,
            'expire_date' => $expireDate,
            'paid_amount' => $paidAmount,
            'status' => 'Enable',
            'insertdatetime' => now(),
            'insertip' => $request->ip(),
            'isTrial' => $isTrial,
            'auto_renew' => 0,
            'billing_cycle' => $plan === 'yearly' ? 'yearly' : 'monthly',
        ]);
        
        // TODO: Send email notification (similar to CI's buymodule controller)
        // TODO: Implement payment gateway integration for paid modules
        
        return redirect()->route('storeowner.modulesetting.index')
            ->with('success', 'Module installed successfully.');
    }

    /**
     * Handle bulk module installation request.
     */
    public function installSelected(Request $request): RedirectResponse
    {
        $moduleIds = $request->input('modules', []);
        if (empty($moduleIds)) {
            return redirect()->route('storeowner.modulesetting.index')
                ->with('error', 'Please select at least one module to install.');
        }

        $storeid = session('storeid', 0);
        $user = auth('storeowner')->user();
        $selectedIds = array_map('intval', $moduleIds);
        $planSelections = $request->input('plan', []);

        $activeModuleIds = $this->getActiveModuleIds($storeid);
        $modules = Module::with('dependencies')
            ->whereIn('moduleid', $selectedIds)
            ->get()
            ->keyBy('moduleid');

        $missing = [];
        foreach ($modules as $module) {
            $requiredIds = $module->dependencies->pluck('moduleid')->all();
            foreach ($requiredIds as $depId) {
                if (! in_array($depId, $activeModuleIds, true) && ! in_array($depId, $selectedIds, true)) {
                    $missing[$module->module][] = $module->dependencies
                        ->firstWhere('moduleid', $depId)
                        ?->module;
                }
            }
        }

        if (! empty($missing)) {
            $messages = [];
            foreach ($missing as $moduleName => $deps) {
                $messages[] = $moduleName . ' requires ' . implode(', ', array_filter($deps));
            }

            return redirect()->route('storeowner.modulesetting.index')
                ->with('error', 'Please install required modules first. ' . implode(' | ', $messages));
        }

        foreach ($modules as $module) {
            $plan = $planSelections[$module->moduleid] ?? 'monthly';

            // Skip if already installed
            $existingPaidModule = PaidModule::where('storeid', $storeid)
                ->where('moduleid', $module->moduleid)
                ->whereDate('purchase_date', '<=', Carbon::now())
                ->whereDate('expire_date', '>=', Carbon::now())
                ->first();

            if ($existingPaidModule) {
                continue;
            }

            $purchaseDate = Carbon::now()->startOfDay();
            if ($module->free_days > 0) {
                $expireDate = Carbon::now()->addDays($module->free_days)->endOfDay();
                $isTrial = 1;
                $paidAmount = '0.00';
            } else {
                $months = $plan === 'yearly' ? 12 : 1;
                $expireDate = Carbon::now()->addMonths($months)->endOfDay();
                $isTrial = 0;
                $paidAmount = $plan === 'yearly'
                    ? ($module->price_12months ?? '0.00')
                    : ($module->price_1months ?? '0.00');
            }

            PaidModule::create([
                'storeid' => $storeid,
                'moduleid' => $module->moduleid,
                'purchase_date' => $purchaseDate,
                'expire_date' => $expireDate,
                'paid_amount' => $paidAmount,
                'status' => 'Enable',
                'insertdatetime' => now(),
                'insertip' => $request->ip(),
                'isTrial' => $isTrial,
                'auto_renew' => 0,
                'billing_cycle' => $plan === 'yearly' ? 'yearly' : 'monthly',
            ]);
        }

        return redirect()->route('storeowner.modulesetting.index')
            ->with('success', 'Selected modules installed successfully.');
    }

    public function updateAutoRenew(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pmid' => ['required', 'integer', 'exists:stoma_paid_module,pmid'],
            'auto_renew' => ['required', 'in:0,1'],
        ]);

        PaidModule::where('pmid', $validated['pmid'])->update([
            'auto_renew' => (int) $validated['auto_renew'],
        ]);

        return redirect()->route('storeowner.modulesetting.index', ['tab' => 'installed'])
            ->with('success', 'Auto renew updated.');
    }

    public function renewModule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pmid' => ['required', 'integer', 'exists:stoma_paid_module,pmid'],
            'tab' => ['nullable', 'string'],
        ]);

        $storeid = session('storeid', 0);
        $user = auth('storeowner')->user();
        $tab = $request->input('tab') === 'renewals' ? 'renewals' : 'installed';

        $pm = PaidModule::with('module')
            ->where('pmid', $validated['pmid'])
            ->where('storeid', $storeid)
            ->first();

        if (! $pm || ! $pm->module) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'Module not found.');
        }

        $moduleName = strtolower($pm->module->module ?? '');
        if ($moduleName === 'employee') {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'Employee module does not require renewal.');
        }

        if (! $pm->payment_card_id) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'Please select a payment card before renewing.');
        }

        $card = PaymentCard::where('cardid', $pm->payment_card_id)
            ->where('ownerid', $user->ownerid)
            ->first();

        if (! $card || ! $card->stripe_payment_method_id || ! $card->stripe_customer_id) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'Selected payment card is not linked to Stripe.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (! $stripeSecret) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'Stripe is not configured.');
        }

        $billingCycle = $pm->billing_cycle === 'yearly' ? 'yearly' : 'monthly';
        $months = $billingCycle === 'yearly' ? 12 : 1;
        $amount = $billingCycle === 'yearly'
            ? ($pm->module->price_12months ?? $pm->paid_amount)
            : ($pm->module->price_1months ?? $pm->paid_amount);
        $amount = (float) $amount;

        if ($amount <= 0) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'This module has no renewal price configured.');
        }

        $currentExpiry = $pm->expire_date ? Carbon::parse($pm->expire_date) : null;
        $startDate = $currentExpiry && $currentExpiry->endOfDay()->greaterThan(now())
            ? $currentExpiry->copy()->addDay()->startOfDay()
            : now()->startOfDay();
        $newExpiry = $startDate->copy()->addMonths($months)->endOfDay();

        $stripe = new StripeClient($stripeSecret);
        try {
            $stripe->paymentIntents->create([
                'amount' => (int) round($amount * 100),
                'currency' => 'eur',
                'customer' => $card->stripe_customer_id,
                'payment_method' => $card->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'description' => 'Module renewal: ' . ($pm->module->module ?? 'Module'),
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', $e->getError()->message ?? 'Card payment failed.');
        } catch (\Throwable $e) {
            return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
                ->with('error', 'Payment failed. Please try again or use another card.');
        }

        PaidModule::create([
            'storeid' => $storeid,
            'moduleid' => $pm->module->moduleid,
            'purchase_date' => $startDate->copy()->startOfDay(),
            'expire_date' => $newExpiry,
            'paid_amount' => $amount,
            'status' => 'Enable',
            'insertdatetime' => now(),
            'insertip' => $request->ip(),
            'isTrial' => 0,
            'auto_renew' => $pm->auto_renew ? 1 : 0,
            'billing_cycle' => $billingCycle,
            'payment_card_id' => $pm->payment_card_id,
        ]);

        return redirect()->route('storeowner.modulesetting.index', ['tab' => $tab])
            ->with('success', 'Module renewed successfully.');
    }

    public function storePaymentCard(Request $request): RedirectResponse
    {
        if (! Session::has('payment_card_address')) {
            return redirect()->route('storeowner.modulesetting.payment-cards', ['modal' => 'address'])
                ->with('error', 'Please complete billing address first.');
        }

        $this->createStripePaymentCard($request, Session::get('payment_card_address', []));
        Session::forget('payment_card_address');

        return redirect()->route('storeowner.modulesetting.index', ['tab' => 'installed'])
            ->with('success', 'Payment card added.');
    }

    public function paymentCards(): View
    {
        $user = auth('storeowner')->user();
        $ownerid = $user->ownerid;
        $storeid = session('storeid', 0);

        $paymentCards = PaymentCard::where('storeid', $storeid)
            ->where('ownerid', $ownerid)
            ->orderBy('insertdate', 'desc')
            ->get();

        return view('storeowner.modulesetting.payment-cards', compact('paymentCards'));
    }

    public function storePaymentCardAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_type' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'town_city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
        ]);

        if ($request->filled('pmid')) {
            Session::put('payment_card_pmid', (int) $request->input('pmid'));
        }
        Session::put('payment_card_address', $validated);

        return redirect()->route('storeowner.modulesetting.payment-cards', ['modal' => 'details']);
    }

    public function storePaymentCardDetails(Request $request): RedirectResponse
    {
        if (! Session::has('payment_card_address')) {
            return redirect()->route('storeowner.modulesetting.payment-cards', ['modal' => 'address'])
                ->with('error', 'Please complete billing address first.');
        }

        $card = $this->createStripePaymentCard($request, Session::get('payment_card_address', []));
        Session::forget('payment_card_address');

        if (Session::has('payment_card_pmid')) {
            $pmid = (int) Session::pull('payment_card_pmid');
            PaidModule::where('pmid', $pmid)->update([
                'payment_card_id' => $card->cardid,
            ]);
        }

        return redirect()->route('storeowner.modulesetting.payment-cards')
            ->with('success', 'Payment card added.');
    }

    private function getActiveModuleIds(int $storeid): array
    {
        $curDate = Carbon::now();

        return PaidModule::where('storeid', $storeid)
            ->whereDate('purchase_date', '<=', $curDate)
            ->whereDate('expire_date', '>=', $curDate)
            ->pluck('moduleid')
            ->unique()
            ->all();
    }

    private function autoRenewExpiredModules($latestPaidModules, int $storeid): void
    {
        $now = Carbon::now();

        foreach ($latestPaidModules as $pm) {
            if (! $pm->auto_renew) {
                continue;
            }

            if (! $pm->expire_date || $pm->expire_date->endOfDay() >= $now) {
                continue;
            }

            $module = $pm->module;
            if (! $module) {
                continue;
            }

            $billingCycle = $pm->billing_cycle === 'yearly' ? 'yearly' : 'monthly';
            $months = $billingCycle === 'yearly' ? 12 : 1;
            $paidAmount = $billingCycle === 'yearly'
                ? ($module->price_12months ?? $pm->paid_amount)
                : ($module->price_1months ?? $pm->paid_amount);

            PaidModule::create([
                'storeid' => $storeid,
                'moduleid' => $module->moduleid,
                'purchase_date' => $now->copy()->startOfDay(),
                'expire_date' => $now->copy()->addMonths($months)->endOfDay(),
                'paid_amount' => $paidAmount,
                'status' => 'Enable',
                'insertdatetime' => now(),
                'insertip' => request()->ip(),
                'isTrial' => 0,
                'auto_renew' => 1,
                'billing_cycle' => $billingCycle,
            ]);
        }
    }

    private function createStripePaymentCard(Request $request, array $billingAddress): PaymentCard
    {
        $validated = $request->validate([
            'name_on_card' => ['required', 'string', 'max:255'],
            'payment_method_id' => ['required', 'string', 'max:255'],
        ]);

        $storeid = session('storeid', 0);
        $user = auth('storeowner')->user();
        $ownerid = $user->ownerid;
        $stripeSecret = config('services.stripe.secret');

        if (! $stripeSecret) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method_id' => 'Stripe is not configured.',
            ]);
        }

        $stripe = new StripeClient($stripeSecret);
        $paymentMethodId = $validated['payment_method_id'];

        $customerId = PaymentCard::where('ownerid', $ownerid)
            ->whereNotNull('stripe_customer_id')
            ->value('stripe_customer_id');

        if (! $customerId) {
            $customer = $stripe->customers->create([
                'name' => trim(($billingAddress['first_name'] ?? '') . ' ' . ($billingAddress['surname'] ?? '')) ?: $validated['name_on_card'],
                'email' => $user->emailid ?? null,
                'address' => [
                    'line1' => trim(($billingAddress['house_number'] ?? '') . ' ' . ($billingAddress['street'] ?? '')),
                    'line2' => $billingAddress['area'] ?? null,
                    'city' => $billingAddress['town_city'] ?? null,
                    'state' => $billingAddress['county'] ?? null,
                    'postal_code' => $billingAddress['postcode'] ?? null,
                ],
            ]);
            $customerId = $customer->id;
        }

        try {
            $stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $customerId,
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            if ($e->getStripeCode() !== 'resource_already_attached') {
                throw $e;
            }
        }

        $stripe->customers->update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);

        $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);
        $cardDetails = $paymentMethod->card ?? null;

        if (! $cardDetails) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method_id' => 'Invalid payment method.',
            ]);
        }

        $brand = ucfirst((string) $cardDetails->brand);
        $last4 = (string) $cardDetails->last4;

        $card = PaymentCard::create([
            'storeid' => $storeid,
            'ownerid' => $ownerid,
            'name_on_card' => $validated['name_on_card'],
            'card_last4' => $last4,
            'card_brand' => $brand,
            'expiry_month' => (int) $cardDetails->exp_month,
            'expiry_year' => (int) $cardDetails->exp_year,
            'stripe_payment_method_id' => $paymentMethodId,
            'stripe_customer_id' => $customerId,
            'status' => 'Active',
            'insertdate' => now(),
            'insertip' => $request->ip(),
        ]);

        return $card;
    }

    public function updatePaymentCard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pmid' => ['required', 'integer', 'exists:stoma_paid_module,pmid'],
            'payment_card_id' => ['required', 'integer', 'exists:stoma_payment_card,cardid'],
        ]);

        PaidModule::where('pmid', $validated['pmid'])->update([
            'payment_card_id' => $validated['payment_card_id'],
        ]);

        return redirect()->route('storeowner.modulesetting.index', ['tab' => 'installed'])
            ->with('success', 'Payment method updated.');
    }
}
