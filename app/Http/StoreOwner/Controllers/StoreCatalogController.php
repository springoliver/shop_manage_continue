<?php

namespace App\Http\StoreOwner\Controllers;

use App\Http\Controllers\Controller;
use App\Http\StoreOwner\Traits\HandlesEmployeeAccess;
use App\Models\CatalogProduct;
use App\Models\CatalogProductCategory;
use App\Models\CatalogProductGroup;
use App\Models\CatalogProductIngredient;
use App\Models\CatalogProductAddon;
use App\Models\CatalogProductModifier;
use App\Models\CatalogProductPaymentMethod;
use App\Models\CatalogProductSetting;
use App\Models\Department;
use App\Models\StoreProduct;
use App\Models\TaxSetting;
use App\Services\StoreOwner\ModuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StoreCatalogController extends Controller
{
    use HandlesEmployeeAccess;

    public function __construct(private readonly ModuleService $moduleService)
    {
    }

    protected function checkModuleAccess(): RedirectResponse|null
    {
        $storeid = $this->getStoreId();
        if (!$storeid) {
            return redirect()->route('storeowner.modulesetting.index')->with('error', 'Store not found');
        }

        if (!$this->moduleService->isModuleInstalled($storeid, 'Catalog Products')) {
            return redirect()->route('storeowner.modulesetting.index')->with('error', 'Please Buy Module to Activate');
        }

        return null;
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $catalogProducts = DB::table('stoma_catalog_products as cp')
            ->select(
                'cp.*',
                'cg.catalog_product_group_name',
                'cc.catalog_product_category_name'
            )
            ->leftJoin('stoma_catalog_product_group as cg', 'cg.catalog_product_groupid', '=', 'cp.catalog_product_groupid')
            ->leftJoin('stoma_catalog_product_category as cc', 'cc.catalog_product_categoryid', '=', 'cp.catalog_product_categoryid')
            ->where('cp.storeid', $storeid)
            ->orderByDesc('cp.catalog_product_id')
            ->get();

        return view('storeowner.storecatalog.index', compact('catalogProducts'));
    }

    public function byCategory(string $catalog_product_categoryid): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $categoryId = (int) base64_decode($catalog_product_categoryid);

        $category = CatalogProductCategory::where('storeid', $storeid)->findOrFail($categoryId);
        $catalogProducts = DB::table('stoma_catalog_products as cp')
            ->select(
                'cp.*',
                'cg.catalog_product_group_name',
                'cc.catalog_product_category_name'
            )
            ->leftJoin('stoma_catalog_product_group as cg', 'cg.catalog_product_groupid', '=', 'cp.catalog_product_groupid')
            ->leftJoin('stoma_catalog_product_category as cc', 'cc.catalog_product_categoryid', '=', 'cp.catalog_product_categoryid')
            ->where('cp.storeid', $storeid)
            ->where('cp.catalog_product_categoryid', $categoryId)
            ->orderByDesc('cp.catalog_product_id')
            ->get();

        return view('storeowner.storecatalog.by_category', compact('catalogProducts', 'category'));
    }

    public function add(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        return view('storeowner.storecatalog.add', $this->formData());
    }

    public function edit(string $catalog_product_id): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $productId = (int) base64_decode($catalog_product_id);
        $catalogProduct = CatalogProduct::where('storeid', $storeid)->findOrFail($productId);
        $ingredients = CatalogProductIngredient::where('catalog_product_id', $productId)->get();

        return view('storeowner.storecatalog.edit', array_merge(
            $this->formData(),
            compact('catalogProduct', 'ingredients')
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $validated = $request->validate([
            'catalog_product_groupid' => 'required|integer',
            'catalog_product_categoryid' => 'required|integer',
            'catalog_product_name' => 'required|string|max:155',
            'catalog_product_desc' => 'nullable|string|max:555',
            'catalog_product_price' => 'required|string|max:55',
            'income_sum' => 'nullable|string|max:10',
            'profit_percentage' => 'nullable|string|max:10',
            'recipe_store_product_id.*' => 'nullable|integer',
            'recipe_percentage.*' => 'nullable|integer|min:0|max:100',
            'recipe_price.*' => 'nullable|string|max:30',
        ]);

        $userId = (int) (auth('storeowner')->id() ?? 0);

        if ($request->filled('catalog_product_id')) {
            $productId = (int) base64_decode((string) $request->input('catalog_product_id'));
            $product = CatalogProduct::where('storeid', $storeid)->findOrFail($productId);
            $product->update([
                'catalog_product_groupid' => $validated['catalog_product_groupid'],
                'catalog_product_categoryid' => $validated['catalog_product_categoryid'],
                'catalog_product_name' => $validated['catalog_product_name'],
                'catalog_product_desc' => $validated['catalog_product_desc'] ?? '',
                'catalog_product_price' => $validated['catalog_product_price'],
                'income_sum' => $validated['income_sum'] ?? '',
                'profit_percentage' => $validated['profit_percentage'] ?? '',
                'editdate' => now(),
                'editip' => $this->resolveEditIpValue('stoma_catalog_products', $request),
            ]);

            CatalogProductIngredient::where('catalog_product_id', $product->catalog_product_id)->delete();
            $this->storeIngredients($request, $product->catalog_product_id, $storeid);

            return redirect()->route('storeowner.storecatalog.index')->with('success', 'Product has been updated successfully');
        }

        $product = CatalogProduct::create([
            'catalog_product_groupid' => $validated['catalog_product_groupid'],
            'catalog_product_categoryid' => $validated['catalog_product_categoryid'],
            'catalog_product_name' => $validated['catalog_product_name'],
            'catalog_product_desc' => $validated['catalog_product_desc'] ?? '',
            'catalog_product_price' => $validated['catalog_product_price'],
            'catalog_product_status' => 'Enable',
            'income_sum' => $validated['income_sum'] ?? '',
            'profit_percentage' => $validated['profit_percentage'] ?? '',
            'storeid' => $storeid,
            'insertdate' => now(),
            'insertip' => $request->ip(),
            'insertby' => (string) $userId,
            'editdate' => now(),
            'editip' => $this->resolveEditIpValue('stoma_catalog_products', $request),
        ]);

        $this->storeIngredients($request, $product->catalog_product_id, $storeid);

        return redirect()->route('storeowner.storecatalog.index')->with('success', 'Product added successfully');
    }

    public function destroy(string $catalog_product_id): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $productId = (int) base64_decode($catalog_product_id);
        $product = CatalogProduct::where('storeid', $storeid)->findOrFail($productId);
        CatalogProductIngredient::where('catalog_product_id', $productId)->delete();
        $product->delete();

        return redirect()->route('storeowner.storecatalog.index')->with('success', 'Product has been deleted successfully');
    }

    public function changeProductStatus(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $validated = $request->validate([
            'catalog_product_id' => 'required|string',
            'catalog_product_status' => 'required|in:Enable,Disable',
        ]);

        $storeid = $this->getStoreId();
        $productId = (int) base64_decode($validated['catalog_product_id']);
        $product = CatalogProduct::where('storeid', $storeid)->findOrFail($productId);
        $product->catalog_product_status = $validated['catalog_product_status'];
        $product->save();

        return redirect()->back()->with('success', 'Status Changed Successfully !');
    }

    public function settings(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $settings = CatalogProductSetting::where('storeid', $storeid)->get();
        $groups = CatalogProductGroup::where('storeid', $storeid)->get();
        $categories = CatalogProductCategory::where('storeid', $storeid)->get();
        $taxSettings = TaxSetting::where('storeid', $storeid)->get();

        return view('storeowner.storecatalog.settings', compact('settings', 'groups', 'categories', 'taxSettings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $values = (array) $request->input('value', []);
        foreach ($values as $settingId => $value) {
            CatalogProductSetting::where('storeid', $storeid)
                ->where('settingid', $settingId)
                ->update(['value' => (string) $value]);
        }

        return redirect()->route('storeowner.storecatalog.settings')->with('success', 'Settings updated successfully');
    }

    public function categories(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $categories = DB::table('stoma_catalog_product_category as c')
            ->select('c.*', 'g.catalog_product_group_name', 't.tax_name')
            ->leftJoin('stoma_catalog_product_group as g', 'g.catalog_product_groupid', '=', 'c.catalog_product_groupid')
            ->leftJoin('stoma_tax_settings as t', 't.taxid', '=', 'c.catalog_product_taxid')
            ->where('c.storeid', $storeid)
            ->orderByDesc('c.catalog_product_categoryid')
            ->get();
        $groups = CatalogProductGroup::where('storeid', $storeid)->get();
        $taxSettings = TaxSetting::where('storeid', $storeid)->get();

        return view('storeowner.storecatalog.categories', compact('categories', 'groups', 'taxSettings'));
    }

    public function updateCategory(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $validated = $request->validate([
            'catalog_product_groupid' => 'required|integer',
            'catalog_product_category_name' => 'required|string|max:255',
            'catalog_product_category_colour' => 'nullable|string|max:11',
            'catalog_product_sell_online' => 'required|in:Enable,Disable',
            'catalog_product_taxid' => 'required|string',
            'catalog_product_categoryid' => 'nullable|string',
        ]);

        $storeid = $this->getStoreId();
        $userId = (int) (auth('storeowner')->id() ?? 0);

        if (!empty($validated['catalog_product_categoryid'])) {
            $id = (int) base64_decode($validated['catalog_product_categoryid']);
            $category = CatalogProductCategory::where('storeid', $storeid)->findOrFail($id);
            $category->update([
                'catalog_product_groupid' => (string) $validated['catalog_product_groupid'],
                'catalog_product_category_name' => $validated['catalog_product_category_name'],
                'catalog_product_category_colour' => $validated['catalog_product_category_colour'] ?: 'CCCCCC',
                'catalog_product_sell_online' => $validated['catalog_product_sell_online'],
                'catalog_product_taxid' => (string) $validated['catalog_product_taxid'],
                'editdate' => now(),
                'editip' => $this->resolveEditIpValue('stoma_catalog_product_category', $request),
                'editby' => $userId,
            ]);
            return redirect()->route('storeowner.storecatalog.categories')->with('success', 'Category updated successfully');
        }

        CatalogProductCategory::create([
            'catalog_product_groupid' => (string) $validated['catalog_product_groupid'],
            'catalog_product_category_name' => $validated['catalog_product_category_name'],
            'catalog_product_category_colour' => $validated['catalog_product_category_colour'] ?: 'CCCCCC',
            'catalog_product_sell_online' => $validated['catalog_product_sell_online'],
            'catalog_product_taxid' => (string) $validated['catalog_product_taxid'],
            'storeid' => $storeid,
            'insertdate' => now(),
            'insertip' => $request->ip(),
            'insertby' => $userId,
            'editdate' => now(),
            'editip' => $this->resolveEditIpValue('stoma_catalog_product_category', $request),
            'editby' => $userId,
        ]);

        return redirect()->route('storeowner.storecatalog.categories')->with('success', 'Category added successfully');
    }

    public function changeCategorySellStatus(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $validated = $request->validate([
            'catalog_product_categoryid' => 'required|string',
            'catalog_product_sell_online' => 'required|in:Enable,Disable',
        ]);

        $storeid = $this->getStoreId();
        $categoryId = (int) base64_decode($validated['catalog_product_categoryid']);
        $category = CatalogProductCategory::where('storeid', $storeid)->findOrFail($categoryId);
        $category->catalog_product_sell_online = $validated['catalog_product_sell_online'];
        $category->save();

        return redirect()->route('storeowner.storecatalog.categories')->with('success', 'Status Changed Successfully !');
    }

    public function deleteCategory(string $catalog_product_categoryid): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $categoryId = (int) base64_decode($catalog_product_categoryid);
        $category = CatalogProductCategory::where('storeid', $storeid)->findOrFail($categoryId);
        $category->delete();

        return redirect()->route('storeowner.storecatalog.categories')->with('success', 'Category deleted successfully');
    }

    public function groupCategoriesList(Request $request)
    {
        $storeid = $this->getStoreId();
        if (!$storeid || !$this->moduleService->isModuleInstalled($storeid, 'Catalog Products')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if (!Schema::hasTable('stoma_catalog_product_category')) {
            return response()->json(['catalog_product_category' => []]);
        }

        $groupId = (int) $request->query('group_id');
        $categories = CatalogProductCategory::where('storeid', $storeid)
            ->where('catalog_product_groupid', (string) $groupId)
            ->orderBy('catalog_product_category_name')
            ->get(['catalog_product_categoryid', 'catalog_product_category_name']);

        return response()->json(['catalog_product_category' => $categories]);
    }

    public function addons(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $storeid = $this->getStoreId();
        $addons = CatalogProductAddon::where('storeid', $storeid)->orderByDesc('addonid')->get();
        return view('storeowner.storecatalog.addons', compact('addons'));
    }

    public function updateAddon(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $validated = $request->validate([
            'addon' => 'required|string|max:55',
            'price' => 'nullable|numeric|min:0',
            'addonid' => 'nullable|string',
        ]);

        $storeid = $this->getStoreId();
        if ($request->filled('addonid')) {
            $addonId = (int) base64_decode((string) $validated['addonid']);
            $addon = CatalogProductAddon::where('storeid', $storeid)->findOrFail($addonId);
            $addon->update([
                'addon' => $validated['addon'],
                'price' => (int) ($validated['price'] ?? 0),
                'editdate' => now(),
                'editip' => $this->resolveEditIpValue('stoma_catalog_products_addons', $request),
            ]);
            return redirect()->route('storeowner.storecatalog.addons')->with('success', 'Addon updated successfully.');
        }

        CatalogProductAddon::create([
            'storeid' => $storeid,
            'addon' => $validated['addon'],
            'price' => (int) ($validated['price'] ?? 0),
            'product_categoryid' => 0,
            'product_groupid' => 0,
            'addon_status' => 'Enable',
            'insertdate' => now(),
            'insertip' => $request->ip(),
            'insertby' => (string) (auth('storeowner')->id() ?? 0),
            'editdate' => now(),
            'editip' => $this->resolveEditIpValue('stoma_catalog_products_addons', $request),
        ]);
        return redirect()->route('storeowner.storecatalog.addons')->with('success', 'Addon added successfully.');
    }

    public function changeAddonStatus(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }

        $validated = $request->validate([
            'addonid' => 'required|string',
            'addon_status' => 'required|in:Enable,Disable',
        ]);
        $storeid = $this->getStoreId();
        $addonId = (int) base64_decode($validated['addonid']);
        $addon = CatalogProductAddon::where('storeid', $storeid)->findOrFail($addonId);
        $addon->addon_status = $validated['addon_status'];
        $addon->save();
        return redirect()->route('storeowner.storecatalog.addons')->with('success', 'Status changed successfully.');
    }

    public function deleteAddon(string $addonid): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $storeid = $this->getStoreId();
        $id = (int) base64_decode($addonid);
        CatalogProductAddon::where('storeid', $storeid)->where('addonid', $id)->delete();
        return redirect()->route('storeowner.storecatalog.addons')->with('success', 'Addon deleted successfully.');
    }

    public function modifiers(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $storeid = $this->getStoreId();
        $modifiers = CatalogProductModifier::where('storeid', $storeid)->orderByDesc('modifier_id')->get();
        return view('storeowner.storecatalog.modifiers', compact('modifiers'));
    }

    public function updateModifier(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $validated = $request->validate([
            'modifier_name' => 'required|string|max:155',
            'modifier_price' => 'required|string|max:55',
            'modifier_id' => 'nullable|string',
        ]);
        $storeid = $this->getStoreId();
        if ($request->filled('modifier_id')) {
            $modifierId = (int) base64_decode((string) $validated['modifier_id']);
            $modifier = CatalogProductModifier::where('storeid', $storeid)->findOrFail($modifierId);
            $modifier->update([
                'modifier_name' => $validated['modifier_name'],
                'modifier_price' => $validated['modifier_price'],
                'editdate' => now(),
                'editip' => $this->resolveEditIpValue('stoma_catalog_products_modifiers', $request),
            ]);
            return redirect()->route('storeowner.storecatalog.modifiers')->with('success', 'Modifier updated successfully.');
        }

        CatalogProductModifier::create([
            'modifier_name' => $validated['modifier_name'],
            'modifier_price' => $validated['modifier_price'],
            'storeid' => $storeid,
            'modifier_status' => 'Enable',
            'income_sum' => null,
            'profit_percentage' => '',
            'insertdate' => now(),
            'insertip' => $request->ip(),
            'insertby' => (string) (auth('storeowner')->id() ?? 0),
            'editdate' => now(),
            'editip' => $this->resolveEditIpValue('stoma_catalog_products_modifiers', $request),
        ]);
        return redirect()->route('storeowner.storecatalog.modifiers')->with('success', 'Modifier added successfully.');
    }

    public function changeModifierStatus(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $validated = $request->validate([
            'modifier_id' => 'required|string',
            'modifier_status' => 'required|in:Enable,Disable',
        ]);
        $storeid = $this->getStoreId();
        $id = (int) base64_decode($validated['modifier_id']);
        $modifier = CatalogProductModifier::where('storeid', $storeid)->findOrFail($id);
        $modifier->modifier_status = $validated['modifier_status'];
        $modifier->save();
        return redirect()->route('storeowner.storecatalog.modifiers')->with('success', 'Status changed successfully.');
    }

    public function deleteModifier(string $modifier_id): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $storeid = $this->getStoreId();
        $id = (int) base64_decode($modifier_id);
        CatalogProductModifier::where('storeid', $storeid)->where('modifier_id', $id)->delete();
        return redirect()->route('storeowner.storecatalog.modifiers')->with('success', 'Modifier deleted successfully.');
    }

    public function paymentMethods(): View|RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $storeid = $this->getStoreId();
        $paymentMethods = CatalogProductPaymentMethod::where('storeid', $storeid)->orderByDesc('payment_methodid')->get();
        return view('storeowner.storecatalog.payment_methods', compact('paymentMethods'));
    }

    public function updatePaymentMethod(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $validated = $request->validate([
            'payment_method' => 'required|string|max:55',
            'email' => 'nullable|email|max:55',
            'merchantid' => 'nullable|integer',
            'currency' => 'nullable|string|max:11',
            'mode' => 'required|in:Live Mode,Test Mode',
            'payment_methodid' => 'nullable|string',
        ]);
        $storeid = $this->getStoreId();

        if ($request->filled('payment_methodid')) {
            $id = (int) base64_decode((string) $validated['payment_methodid']);
            $payment = CatalogProductPaymentMethod::where('storeid', $storeid)->findOrFail($id);
            $payment->update([
                'payment_method' => $validated['payment_method'],
                'email' => $validated['email'] ?? '',
                'merchantid' => (int) ($validated['merchantid'] ?? 0),
                'currency' => $validated['currency'] ?? '',
                'mode' => $validated['mode'],
                'editdate' => now(),
                'editip' => $this->resolveEditIpValue('stoma_catalog_products_payment_methods', $request),
            ]);
            return redirect()->route('storeowner.storecatalog.payment-methods')->with('success', 'Payment method updated successfully.');
        }

        CatalogProductPaymentMethod::create([
            'storeid' => $storeid,
            'payment_method' => $validated['payment_method'],
            'email' => $validated['email'] ?? '',
            'merchantid' => (int) ($validated['merchantid'] ?? 0),
            'currency' => $validated['currency'] ?? '',
            'mode' => $validated['mode'],
            'status' => 'Active',
            'insertdate' => now(),
            'insertip' => $request->ip(),
            'insertby' => (string) (auth('storeowner')->id() ?? 0),
            'editdate' => now(),
            'editip' => $this->resolveEditIpValue('stoma_catalog_products_payment_methods', $request),
        ]);
        return redirect()->route('storeowner.storecatalog.payment-methods')->with('success', 'Payment method added successfully.');
    }

    public function changePaymentMethodStatus(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $validated = $request->validate([
            'payment_methodid' => 'required|string',
            'status' => 'required|in:Active,Inactive',
        ]);
        $storeid = $this->getStoreId();
        $id = (int) base64_decode($validated['payment_methodid']);
        $payment = CatalogProductPaymentMethod::where('storeid', $storeid)->findOrFail($id);
        $payment->status = $validated['status'];
        $payment->save();
        return redirect()->route('storeowner.storecatalog.payment-methods')->with('success', 'Status changed successfully.');
    }

    public function changePaymentMethodMode(Request $request): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $validated = $request->validate([
            'payment_methodid' => 'required|string',
            'mode' => 'required|in:Live Mode,Test Mode',
        ]);
        $storeid = $this->getStoreId();
        $id = (int) base64_decode($validated['payment_methodid']);
        $payment = CatalogProductPaymentMethod::where('storeid', $storeid)->findOrFail($id);
        $payment->mode = $validated['mode'];
        $payment->save();
        return redirect()->route('storeowner.storecatalog.payment-methods')->with('success', 'Mode changed successfully.');
    }

    public function deletePaymentMethod(string $payment_methodid): RedirectResponse
    {
        if ($redirect = $this->checkModuleAccess()) {
            return $redirect;
        }
        if ($redirect = $this->checkCatalogTables()) {
            return $redirect;
        }
        $storeid = $this->getStoreId();
        $id = (int) base64_decode($payment_methodid);
        CatalogProductPaymentMethod::where('storeid', $storeid)->where('payment_methodid', $id)->delete();
        return redirect()->route('storeowner.storecatalog.payment-methods')->with('success', 'Payment method deleted successfully.');
    }

    private function formData(): array
    {
        $storeid = $this->getStoreId();

        $groups = CatalogProductGroup::where('storeid', $storeid)->orderBy('catalog_product_group_name')->get();
        $categories = CatalogProductCategory::where('storeid', $storeid)->orderBy('catalog_product_category_name')->get();
        $storeProducts = StoreProduct::where('storeid', $storeid)->orderBy('product_name')->get();
        $departments = Department::where(function ($query) use ($storeid) {
            $query->where('storeid', $storeid)->orWhere('storeid', 0);
        })->where('status', 'Enable')->get();
        $taxSettings = TaxSetting::where('storeid', $storeid)->orderBy('tax_name')->get();
        $ingredientsLimit = CatalogProductSetting::where('storeid', $storeid)->where('settingid', 1)->value('value') ?? '30';

        return compact('groups', 'categories', 'storeProducts', 'departments', 'taxSettings', 'ingredientsLimit');
    }

    private function checkCatalogTables(): ?RedirectResponse
    {
        $requiredTables = [
            'stoma_catalog_products',
            'stoma_catalog_product_category',
            'stoma_catalog_product_group',
            'stoma_catalog_product_ingredients',
            'stoma_catalog_products_settings',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return redirect()
                    ->route('storeowner.modulesetting.index')
                    ->with('error', "Catalog module database table '{$table}' is missing. Please import or migrate catalog tables first.");
            }
        }

        return null;
    }

    private function storeIngredients(Request $request, int $catalogProductId, int $storeid): void
    {
        $storeProductIds = (array) $request->input('recipe_store_product_id', []);
        $percentages = (array) $request->input('recipe_percentage', []);
        $prices = (array) $request->input('recipe_price', []);
        $insertBy = (string) (auth('storeowner')->id() ?? 0);

        foreach ($storeProductIds as $index => $storeProductId) {
            $storeProductId = (int) $storeProductId;
            if ($storeProductId <= 0) {
                continue;
            }

            CatalogProductIngredient::create([
                'storeid' => $storeid,
                'store_product_id' => $storeProductId,
                'catalog_product_id' => $catalogProductId,
                'percentage' => (int) ($percentages[$index] ?? 0),
                'price' => (string) ($prices[$index] ?? '0'),
                'insertdate' => now(),
                'insertip' => $request->ip(),
                'insertby' => $insertBy,
            ]);
        }
    }

    private function resolveEditIpValue(string $table, Request $request): int|string|null
    {
        $ip = (string) $request->ip();

        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'editip')) {
            return $ip;
        }

        $columnType = strtolower((string) Schema::getColumnType($table, 'editip'));
        $isIntegerColumn = str_contains($columnType, 'int');

        if (!$isIntegerColumn) {
            return $ip;
        }

        $ipAsLong = ip2long($ip);
        if ($ipAsLong === false) {
            return 0;
        }

        if ($ipAsLong < 0) {
            $ipAsLong += 4294967296;
        }

        return (int) $ipAsLong;
    }
}
