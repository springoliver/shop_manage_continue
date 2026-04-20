@php
    $editing = isset($catalogProduct);
    $selectedGroup = old('catalog_product_groupid', $catalogProduct->catalog_product_groupid ?? '');
    $selectedCategory = old('catalog_product_categoryid', $catalogProduct->catalog_product_categoryid ?? '');
    $hasGroups = isset($groups) && count($groups) > 0;
    $hasCategories = isset($categories) && count($categories) > 0;
@endphp

@if ($editing)
    <input type="hidden" name="catalog_product_id" value="{{ base64_encode($catalogProduct->catalog_product_id) }}">
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Group</label>
        <select id="catalogProductGroupSelect" name="catalog_product_groupid" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            <option value="">Select Group</option>
            @foreach ($groups as $group)
                <option value="{{ $group->catalog_product_groupid }}" @selected((string) $selectedGroup === (string) $group->catalog_product_groupid)>
                    {{ $group->catalog_product_group_name }}
                </option>
            @endforeach
            @if (!$hasGroups)
                <option value="__add_group__">Add Group</option>
            @endif
        </select>
        @if (!$hasGroups)
            <p class="mt-1 text-xs text-red-600">
                No group found. Please <a href="{{ route('storeowner.storecatalog.settings') }}" class="underline">Add Group</a> before adding product.
            </p>
        @endif
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select id="catalogProductCategorySelect" name="catalog_product_categoryid" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            <option value="">Select Category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->catalog_product_categoryid }}" @selected((string) $selectedCategory === (string) $category->catalog_product_categoryid)>
                    {{ $category->catalog_product_category_name }}
                </option>
            @endforeach
            @if (!$hasCategories)
                <option value="__add_category__">Add Category</option>
            @endif
        </select>
        @if (!$hasCategories)
            <p class="mt-1 text-xs text-red-600">
                No category found. Please <a href="{{ route('storeowner.storecatalog.categories') }}" class="underline">Add Category</a> before adding product.
            </p>
        @endif
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
        <input name="catalog_product_name" value="{{ old('catalog_product_name', $catalogProduct->catalog_product_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Price</label>
        <input id="catalogProductPriceInput" name="catalog_product_price" value="{{ old('catalog_product_price', $catalogProduct->catalog_product_price ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Income Sum</label>
        <input id="incomeSumInput" name="income_sum" value="{{ old('income_sum', $catalogProduct->income_sum ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50" readonly>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Profit %</label>
        <input id="profitPercentageInput" name="profit_percentage" value="{{ old('profit_percentage', $catalogProduct->profit_percentage ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50" readonly>
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
                        <option value="{{ $storeProduct->productid }}" data-price="{{ $storeProduct->product_price }}" @selected((string) $row['store_product_id'] === (string) $storeProduct->productid)>
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

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">Product Photo</label>
    <input type="file" name="catalog_product_photo" accept=".jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white">
    <p class="mt-1 text-xs text-gray-500">Select .jpg, .jpeg or .png file (max 2MB).</p>
    @if ($editing && !empty($catalogProduct->catalog_product_photo))
        <p class="mt-1 text-xs text-gray-600">Current: {{ basename($catalogProduct->catalog_product_photo) }}</p>
    @endif
</div>

<script>
    (() => {
        const groupSelect = document.getElementById('catalogProductGroupSelect');
        const categorySelect = document.getElementById('catalogProductCategorySelect');
        const form = groupSelect?.closest('form');

        groupSelect?.addEventListener('change', () => {
            if (groupSelect.value !== '__add_group__') return;
            window.location.href = @json(route('storeowner.storecatalog.settings'));
        });

        categorySelect?.addEventListener('change', () => {
            if (categorySelect.value !== '__add_category__') return;
            window.location.href = @json(route('storeowner.storecatalog.categories'));
        });

        form?.addEventListener('submit', (event) => {
            const hasGroups = @json($hasGroups);
            const hasCategories = @json($hasCategories);
            const invalidGroup = !groupSelect || groupSelect.value === '' || (!hasGroups && groupSelect.value === '__add_group__');
            const invalidCategory = !categorySelect || categorySelect.value === '' || (!hasCategories && categorySelect.value === '__add_category__');
            if (!invalidGroup && !invalidCategory) return;

            event.preventDefault();
            alert('Please add/select Product Group and Category before saving product.');
        });

        const addBtn = document.getElementById('addIngredientRow');
        const rowsEl = document.getElementById('ingredientRows');
        if (!addBtn || !rowsEl) return;

        const rowTemplate = `@php($options = '<option value="">Select Store Product</option>')@foreach ($storeProducts as $storeProduct)@php($options .= '<option value="' . e($storeProduct->productid) . '" data-price="' . e($storeProduct->product_price) . '">' . e($storeProduct->product_name) . '</option>')@endforeach<div class="ingredient-row grid grid-cols-1 md:grid-cols-12 gap-2"><select name="recipe_store_product_id[]" class="md:col-span-6 border border-gray-300 rounded-md px-3 py-2">{!! $options !!}</select><input name="recipe_percentage[]" placeholder="%" class="md:col-span-2 border border-gray-300 rounded-md px-3 py-2"><input name="recipe_price[]" placeholder="Price" class="md:col-span-3 border border-gray-300 rounded-md px-3 py-2"><button type="button" class="removeIngredient md:col-span-1 px-2 py-2 bg-red-100 text-red-700 rounded">X</button></div>`;

        const productPriceInput = document.getElementById('catalogProductPriceInput');
        const incomeSumInput = document.getElementById('incomeSumInput');
        const profitPercentageInput = document.getElementById('profitPercentageInput');
        const ingredientsLimit = {{ (int) ($ingredientsLimit ?? 30) }};

        const parseNumber = (value) => {
            const cleaned = String(value ?? '').replace(/,/g, '').trim();
            const parsed = parseFloat(cleaned);
            return Number.isFinite(parsed) ? parsed : 0;
        };

        const formatNumber = (value) => {
            return (Math.round(value * 100) / 100).toFixed(2).replace(/\.00$/, '');
        };

        const recalculateIncomeAndProfit = () => {
            if (!incomeSumInput || !profitPercentageInput) return;

            const productPrice = parseNumber(productPriceInput?.value);
            const recipePriceInputs = rowsEl.querySelectorAll('input[name="recipe_price[]"]');
            let totalRecipeCost = 0;
            recipePriceInputs.forEach((input) => {
                totalRecipeCost += parseNumber(input.value);
            });

            const incomeSum = productPrice - totalRecipeCost;
            const profitPercentage = productPrice > 0 ? (incomeSum / productPrice) * 100 : 0;

            incomeSumInput.value = formatNumber(incomeSum);
            profitPercentageInput.value = formatNumber(profitPercentage);
        };

        const recalculateRowPriceFromPercentage = (rowEl) => {
            if (!rowEl) return;
            const select = rowEl.querySelector('select[name="recipe_store_product_id[]"]');
            const pctInput = rowEl.querySelector('input[name="recipe_percentage[]"]');
            const priceInput = rowEl.querySelector('input[name="recipe_price[]"]');
            if (!select || !pctInput || !priceInput) return;

            const opt = select.selectedOptions?.[0];
            const basePrice = parseNumber(opt?.getAttribute('data-price') ?? '0');
            const pct = parseNumber(pctInput.value);
            if (!basePrice) return;

            const computed = (basePrice * pct) / 100;
            priceInput.value = formatNumber(computed);
        };

        addBtn.addEventListener('click', () => {
            const currentRows = rowsEl.querySelectorAll('.ingredient-row').length;
            if (currentRows >= ingredientsLimit) {
                alert(`You can add up to ${ingredientsLimit} ingredients.`);
                return;
            }
            rowsEl.insertAdjacentHTML('beforeend', rowTemplate);
            recalculateIncomeAndProfit();
        });

        rowsEl.addEventListener('click', (event) => {
            const btn = event.target.closest('.removeIngredient');
            if (!btn) return;
            const row = btn.closest('.ingredient-row');
            if (row) row.remove();
            recalculateIncomeAndProfit();
        });

        rowsEl.addEventListener('input', (event) => {
            const isRecipePriceField = event.target.matches('input[name="recipe_price[]"]');
            if (isRecipePriceField) {
                recalculateIncomeAndProfit();
            }

            const isPercentageField = event.target.matches('input[name="recipe_percentage[]"]');
            if (isPercentageField) {
                const row = event.target.closest('.ingredient-row');
                recalculateRowPriceFromPercentage(row);
                recalculateIncomeAndProfit();
            }
        });

        rowsEl.addEventListener('change', (event) => {
            const isStoreProductSelect = event.target.matches('select[name="recipe_store_product_id[]"]');
            if (!isStoreProductSelect) return;
            const row = event.target.closest('.ingredient-row');
            if (!row) return;

            const pctInput = row.querySelector('input[name="recipe_percentage[]"]');
            if (pctInput && String(pctInput.value || '').trim() === '') {
                pctInput.value = '100';
            }
            recalculateRowPriceFromPercentage(row);
            recalculateIncomeAndProfit();
        });

        productPriceInput?.addEventListener('input', recalculateIncomeAndProfit);
        recalculateIncomeAndProfit();
    })();
</script>
