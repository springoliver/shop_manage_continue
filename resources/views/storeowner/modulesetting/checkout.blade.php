@section('page_header', 'Modules')

<x-storeowner-app-layout>
    <div class="mb-6">
        <nav class="flex text-gray-500 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('storeowner.dashboard') }}" class="inline-flex items-center hover:text-gray-700">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-gray-400"></i>
                    <a href="{{ route('storeowner.modulesetting.index') }}" class="ml-1 hover:text-gray-700">Modules</a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 font-medium text-gray-700 md:ml-2">Review &amp; Checkout</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div id="checkout-root" class="bg-white rounded-lg shadow-md p-6" data-vat-rate="{{ $vatRate }}">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Review &amp; Checkout</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="border border-gray-200 rounded-lg p-4">
                    @forelse ($availableModules as $module)
                        <div class="module-row grid grid-cols-[260px_1fr_1fr] items-center gap-6 py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox"
                                       class="module-check rounded border-gray-300"
                                       data-module-id="{{ $module->moduleid }}"
                                       data-module="{{ $module->module ?? 'Module' }}">
                                <div class="font-semibold text-gray-800 uppercase">{{ $module->module ?? 'Module' }}</div>
                            </div>
                            <div class="text-sm text-gray-700">
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           class="module-plan"
                                           name="plan[{{ $module->moduleid }}]"
                                           value="monthly"
                                           data-monthly="{{ $module->price_1months ?? 0 }}"
                                           data-yearly="{{ $module->price_12months ?? 0 }}"
                                           checked>
                                    <span class="ml-2">€{{ number_format($module->price_1months ?? 0, 0) }} per month</span>
                                </label>
                            </div>
                            <div class="text-sm text-gray-700">
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           class="module-plan"
                                           name="plan[{{ $module->moduleid }}]"
                                           value="yearly"
                                           data-monthly="{{ $module->price_1months ?? 0 }}"
                                           data-yearly="{{ $module->price_12months ?? 0 }}">
                                    <span class="ml-2">€{{ number_format($module->price_12months ?? 0, 0) }} per year</span>
                                    <span class="ml-2 text-xs text-red-500 savings-label hidden">10% savings</span>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-10">No modules available.</div>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-1 w-[75%]">
                <div class="bg-green-700 text-white rounded-lg p-5">
                    <div class="text-lg font-semibold mb-4">Order Summary</div>
                    <div id="order-summary-items" class="space-y-3 text-sm">
                        <div class="text-xs text-white/80">Select modules to see summary.</div>
                    </div>

                    <div class="border-t border-green-600 my-4"></div>
                    <div class="flex items-center justify-between text-sm">
                        <div>Sub Total</div>
                        <div id="summary-subtotal">€0.00</div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div>{{ number_format($vatRate * 100, 0) }}% VAT</div>
                        <div id="summary-vat">€0.00</div>
                    </div>

                    <div class="border-t border-green-600 my-4"></div>
                    <div class="flex items-center justify-between text-lg font-semibold">
                        <div>Total Due</div>
                        <div id="summary-total">€0.00</div>
                    </div>

                    <a id="checkout-link" href="{{ route('storeowner.modulesetting.checkout.payment', request()->query()) }}" class="mt-4 w-full bg-white text-green-700 font-semibold py-2 rounded-md text-center block">
                        Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const vatRate = Number(document.getElementById('checkout-root')?.dataset.vatRate || 0);
            const summaryItems = document.getElementById('order-summary-items');
            const summarySubtotal = document.getElementById('summary-subtotal');
            const summaryVat = document.getElementById('summary-vat');
            const summaryTotal = document.getElementById('summary-total');
            const params = new URLSearchParams(window.location.search);

            function applyQuerySelection() {
                const modulesParam = params.get('modules');
                const plansParam = params.get('plans');
                const allowedModules = modulesParam
                    ? new Set(modulesParam.split(',').filter(Boolean))
                    : null;
                const planMap = new Map();

                if (plansParam) {
                    plansParam.split(',').forEach(pair => {
                        const [id, cycle] = pair.split(':');
                        if (id && cycle) {
                            planMap.set(id, cycle);
                        }
                    });
                }

                document.querySelectorAll('.module-check').forEach(check => {
                    const moduleId = check.dataset.moduleId;
                    if (allowedModules) {
                        check.checked = allowedModules.has(moduleId);
                    }
                    const row = check.closest('.module-row');
                    const cycle = planMap.get(moduleId);
                    if (cycle && row) {
                        const target = row.querySelector(`.module-plan[value="${cycle}"]`);
                        if (target) {
                            target.checked = true;
                        }
                    }
                });
            }

            function recalcSummary() {
                const rows = [];
                let subtotal = 0;

                document.querySelectorAll('.module-check').forEach(check => {
                    if (!check.checked) {
                        return;
                    }
                    const row = check.closest('.module-row');
                    const moduleName = check.dataset.module || 'Module';
                    const selectedPlan = row.querySelector('.module-plan:checked');
                    const cycle = selectedPlan?.value || 'monthly';
                    const amount = cycle === 'yearly'
                        ? parseFloat(selectedPlan?.dataset.yearly || 0)
                        : parseFloat(selectedPlan?.dataset.monthly || 0);

                    subtotal += amount;
                    rows.push({
                        module: moduleName,
                        cycle,
                        amount
                    });
                });

                summaryItems.innerHTML = '';
                if (rows.length === 0) {
                    summaryItems.innerHTML = '<div class="text-xs text-white/80">Select modules to see summary.</div>';
                } else {
                    rows.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center justify-between';
                        const cycleLabel = item.cycle === 'yearly' ? 'Yearly' : 'Monthly';
                        div.innerHTML =
                            '<div>' + item.module.toUpperCase() + ' Module</div>' +
                            '<div>' + cycleLabel + ' €' + item.amount.toFixed(0) + '</div>';
                        summaryItems.appendChild(div);
                    });
                }

                const vat = subtotal * vatRate;
                const total = subtotal + vat;
                summarySubtotal.textContent = '€' + subtotal.toFixed(2);
                summaryVat.textContent = '€' + vat.toFixed(2);
                summaryTotal.textContent = '€' + total.toFixed(2);
            }

            function updateSavingsLabels() {
                document.querySelectorAll('.module-row').forEach(row => {
                    const selectedPlan = row.querySelector('.module-plan:checked');
                    const savingsLabel = row.querySelector('.savings-label');
                    if (!savingsLabel) {
                        return;
                    }
                    if (selectedPlan?.value === 'yearly') {
                        savingsLabel.classList.remove('hidden');
                    } else {
                        savingsLabel.classList.add('hidden');
                    }
                });
            }

            function updateCheckoutLink() {
                const selected = [];
                document.querySelectorAll('.module-check').forEach(check => {
                    if (!check.checked) {
                        return;
                    }
                    const row = check.closest('.module-row');
                    const moduleId = check.dataset.moduleId;
                    const selectedPlan = row.querySelector('.module-plan:checked');
                    const cycle = selectedPlan?.value || 'monthly';
                    selected.push({ id: moduleId, plan: cycle });
                });

                const params = new URLSearchParams();
                if (selected.length) {
                    params.set('modules', selected.map(item => item.id).join(','));
                    params.set('plans', selected.map(item => `${item.id}:${item.plan}`).join(','));
                }
                const checkoutLink = document.getElementById('checkout-link');
                if (checkoutLink) {
                    const baseUrl = "{{ route('storeowner.modulesetting.checkout.payment') }}";
                    checkoutLink.href = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
                }
            }

            document.querySelectorAll('.module-check').forEach(check => {
                check.addEventListener('change', () => {
                    updateSavingsLabels();
                    recalcSummary();
                    updateCheckoutLink();
                });
            });
            document.querySelectorAll('.module-plan').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateSavingsLabels();
                    recalcSummary();
                    updateCheckoutLink();
                });
            });

            applyQuerySelection();
            updateSavingsLabels();
            recalcSummary();
            updateCheckoutLink();
        </script>
    @endpush
</x-storeowner-app-layout>
