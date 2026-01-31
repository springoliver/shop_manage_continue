<?php

namespace App\Http\StoreOwner\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreOwner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    /**
     * Activate store owner account.
     *
     * @param Request $request
     * @param string $token
     * @return RedirectResponse
     */
    public function activate(string $token): RedirectResponse
    {
        try {
            $ownerid = base64_decode($token);
            
            if (!$ownerid || !is_numeric($ownerid)) {
                return redirect()->route('storeowner.login')
                    ->with('error', 'Invalid activation link.');
            }

            $storeOwner = StoreOwner::find($ownerid);

            if (!$storeOwner) {
                return redirect()->route('storeowner.login')
                    ->with('error', 'Invalid activation link.');
            }

            $updatedOwner = false;
            if ($storeOwner->status !== 'Active') {
                // Activate the account
                $storeOwner->update([
                    'status' => 'Active',
                    'editdate' => now(),
                    'editip' => request()->ip(),
                ]);
                $updatedOwner = true;
            }

            // Activate any pending stores for this owner
            Store::where('storeownerid', $storeOwner->ownerid)
                ->whereIn('status', ['Pending Setup', 'Pending Activation'])
                ->update([
                    'status' => 'Active',
                    'editdate' => now(),
                    'editip' => request()->ip(),
                    'editby' => 0,
                ]);

            return redirect()->route('storeowner.login')
                ->with('success', $updatedOwner
                    ? 'Your account is activated successfully. You can login to your account with your email and password.'
                    : 'Your account is already activated. You can login to your account with your email and password.'
                );
        } catch (\Exception $e) {
            return redirect()->route('storeowner.login')
                ->with('error', 'There occurred some error in activating your account!');
        }
    }
}

