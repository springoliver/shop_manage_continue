@php
    $editing = isset($catalogProduct);
    $selectedGroup = old('catalog_product_groupid', $catalogProduct->catalog_product_groupid ?? '');
    $selectedCategory = old('catalog_product_categoryid', $catalogProduct->catalog_product_categoryid ?? '');
@endphp

@if ($editing)
    <input type="hidden" name="catalog_product_id" value="{{ base64_encode($catalogProduct->catalog_product_id) }}">
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Group</label>
        <select name="catalog_product_groupid" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            <option value="">Select Group</option>
            @foreach ($groups as $group)
                <option value="{{ $group->catalog_product_groupid }}" @selected((string) $selectedGroup === (string) $group->catalog_product_groupid)>
                    {{ $group->catalog_product_group_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select name="catalog_product_categoryid" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            <option value="">Select Category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->catalog_product_categoryid }}" @selected((string) $selectedCategory === (string) $category->catalog_product_categoryid)>
                    {{ $category->catalog_product_category_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
        <input name="catalog_product_name" value="{{ old('catalog_product_name', $catalogProduct->catalog_product_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Price</label>
        <input name="catalog_product_price" value="{{ old('catalog_product_price', $catalogProduct->catalog_product_price ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Income Sum</label>
        <input name="income_sum" value="{{ old('income_sum', $catalogProduct->income_sum ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Profit %</label>
        <input name="profit_percentage" value="{{ old('profit_percentage', $catalogProduct->profit_percentage ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="catalog_product_desc" class="w-full border border-gray-300 rounded-md px-3 py-2" rows="3">{{ old('catalog_product_desc', $catalogProduct->catalog_product_desc ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-semibold text-gray-700">Recipe Ingredients</h3>
        <button type="button" id="addIngredientRow" class="px-3 py-1 bg-gray-200 rounded text-sm">Add Row</button>
    </div>
    <p class="text-xs text-gray-500 mb-2">Ingredients limit: {{ $ingredientsLimit }}</p>
    <div id="ingredientRows" class="space-y-2">
        @php
            $oldStoreProducts = old('recipe_store_product_id', []);
            $oldPercentages = old('recipe_percentage', []);
            $oldPrices = old('recipe_price', []);
            $rows = [];
            if (count($oldStoreProducts) > 0) {
                foreach ($oldStoreProducts as $idx => $spId) {
                    $rows[] = [
                        'store_product_id' => $spId,
                        'percentage' => $oldPercentages[$idx] ?? '',
                        'price' => $oldPrices[$idx] ?? '',
                    ];
                }
            } elseif (!empty($ingredients ?? [])) {
                foreach ($ingredients as $ing) {
                    $rows[] = [
                        'store_product_id' => $ing->store_product_id,
                        'percentage' => $ing->percentage,
                        'price' => $ing->price,
                    ];
                }
            }
        @endphp

        @foreach ($rows as $row)
            <div class="ingredient-row grid grid-cols-1 md:grid-cols-12 gap-2">
                <select name="recipe_store_product_id[]" class="md:col-span-6 border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Select Store Product</option>
                    @foreach ($storeProducts as $storeProduct)
                        <option value="{{ $storeProduct->productid }}" @selected((string) $row['store_product_id'] === (string) $storeProduct->productid)>
                            {{ $storeProduct->product_name }}
                        </option>
                    @endforeach
                </select>
                <input name="recipe_percentage[]" value="{{ $row['percentage'] }}" placeholder="%"
                    class="md:col-span-2 border border-gray-300 rounded-md px-3 py-2">
                <input name="recipe_price[]" value="{{ $row['price'] }}" placeholder="Price"
                    class="md:col-span-3 border border-gray-300 rounded-md px-3 py-2">
                <button type="button" class="removeIngredient md:col-span-1 px-2 py-2 bg-red-100 text-red-700 rounded">X</button>
            </div>
        @endforeach
    </div>
</div>

<script>
    (() => {
        const addBtn = document.getElementById('addIngredientRow');
        const rowsEl = document.getElementById('ingredientRows');
        if (!addBtn || !rowsEl) return;

        const rowTemplate = `@php($options = '<option value="">Select Store Product</option>')@foreach ($storeProducts as $storeProduct)@php($options .= '<option value="' . e($storeProduct->productid) . '">' . e($storeProduct->product_name) . '</option>')@endforeach<div class="ingredient-row grid grid-cols-1 md:grid-cols-12 gap-2"><select name="recipe_store_product_id[]" class="md:col-span-6 border border-gray-300 rounded-md px-3 py-2">{!! $options !!}</select><input name="recipe_percentage[]" placeholder="%" class="md:col-span-2 border border-gray-300 rounded-md px-3 py-2"><input name="recipe_price[]" placeholder="Price" class="md:col-span-3 border border-gray-300 rounded-md px-3 py-2"><button type="button" class="removeIngredient md:col-span-1 px-2 py-2 bg-red-100 text-red-700 rounded">X</button></div>`;

        addBtn.addEventListener('click', () => {
            rowsEl.insertAdjacentHTML('beforeend', rowTemplate);
        });

        rowsEl.addEventListener('click', (event) => {
            const btn = event.target.closest('.removeIngredient');
            if (!btn) return;
            const row = btn.closest('.ingredient-row');
            if (row) row.remove();
        });
    })();
</script>
