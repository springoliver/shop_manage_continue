@section('page_header', 'Modules')

<x-storeowner-app-layout>
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex text-gray-500 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('storeowner.dashboard') }}" class="inline-flex items-center hover:text-gray-700">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 font-medium text-gray-700 md:ml-2">Modules</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
            <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">&times;</button>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
            <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">&times;</button>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @php
        $renewalsDueCount = $renewalsDue->count();
    @endphp

    @if ($renewalsDueCount > 0)
        <div class="mb-6 bg-green-700 text-white rounded-lg px-6 py-4 flex items-center justify-between">
            <div>
                <div class="font-semibold text-lg">You have {{ $renewalsDueCount }} modules due for renewal</div>
                <div class="text-sm opacity-90">Click here to renew modules</div>
            </div>
            <button type="button" class="px-4 py-2 border border-white rounded-md" onclick="showModuleTab('tab-renewals')">
                Renew Modules
            </button>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap">
                <button class="module-tab px-6 py-3 text-sm font-medium border-b-2 border-gray-800 text-gray-800" data-tab="tab-not-installed">
                    Not Installed
                </button>
                <button class="module-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-800" data-tab="tab-installed">
                    Installed Modules
                </button>
                <button class="module-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-800" data-tab="tab-renewals">
                    Renewals Due
                    @if ($renewalsDueCount > 0)
                        <span class="ml-2 inline-flex items-center justify-center text-xs bg-red-500 text-white rounded-full h-5 w-5">{{ $renewalsDueCount }}</span>
                    @endif
                </button>
                <button class="module-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-800" data-tab="tab-billing">
                    Billing
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Not Installed -->
            <div id="tab-not-installed" class="module-tab-panel">
                <form method="POST" action="{{ route('storeowner.modulesetting.install-selected') }}" id="install-selected-form">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800 text-white text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input type="checkbox" id="select-all-modules" class="rounded border-gray-300">
                                    </th>
                                    <th class="px-4 py-3 text-left">Modules</th>
                                    <th class="px-4 py-3 text-left">Info</th>
                                    <th class="px-4 py-3 text-left">Price Monthly</th>
                                    <th class="px-4 py-3 text-left">Price Yearly</th>
                                    <th class="px-4 py-3 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($availableModules as $module)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="modules[]" value="{{ $module->moduleid }}" class="module-select rounded border-gray-300">
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $module->module }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <a href="#" class="text-gray-700 hover:underline" onclick="event.preventDefault(); openModuleInfoModal('module-info{{ $module->moduleid }}')">
                                                More info &gt;&gt;
                                            </a>
                                            <!-- @if ($module->dependencies->isNotEmpty())
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Requires: {{ $module->dependencies->pluck('module')->join(', ') }}
                                                </div>
                                            @endif -->
                                        </td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center text-sm text-gray-700">
                                                <input type="radio" name="plan[{{ $module->moduleid }}]" value="monthly" class="plan-radio" data-module="{{ $module->moduleid }}" checked>
                                                <span class="ml-2">€{{ number_format($module->price_1months ?? 0, 2) }} per month</span>
                                            </label>
                                        </td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center text-sm text-gray-700">
                                                <input type="radio" name="plan[{{ $module->moduleid }}]" value="yearly" class="plan-radio" data-module="{{ $module->moduleid }}">
                                                <span class="ml-2">€{{ number_format($module->price_12months ?? 0, 2) }} per year</span>
                                            </label>
                                        </td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('storeowner.modulesetting.install') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="moduleid" value="{{ base64_encode($module->moduleid) }}">
                                                <input type="hidden" name="install" value="Yes">
                                                <input type="hidden" name="plan" class="single-plan-{{ $module->moduleid }}" value="monthly">
                                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                    Install
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">All modules are installed.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800">
                            Install all selected Modules
                        </button>
                    </div>
                </form>
            </div>

            <!-- Installed Modules -->
            <div id="tab-installed" class="module-tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800 text-white text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" id="select-all-installed" class="rounded border-gray-300">
                                </th>
                                <th class="px-4 py-3 text-left">Modules</th>
                                <th class="px-4 py-3 text-left">Auto Renew</th>
                                <th class="px-4 py-3 text-left">Payment Method</th>
                                <th class="px-4 py-3 text-left">Renewal Term</th>
                                <th class="px-4 py-3 text-left">Due Date</th>
                                <th class="px-4 py-3 text-left">Expires</th>
                                <th class="px-4 py-3 text-left"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($installedModules as $pm)
                                @php
                                    $expiresIn = $pm->expire_date ? (int) now()->diffInDays($pm->expire_date, false) : null;
                                    $moduleName = strtolower($pm->module->module ?? '');
                                    $isEmployeesModule = $moduleName === 'employee';
                                    $renewalTerm = $isEmployeesModule
                                        ? 'STANDARD / CORE MODULE'
                                        : '1 yr for €' . number_format(($pm->paid_amount ?? 0), 0);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" class="installed-select rounded border-gray-300">
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $pm->module->module ?? 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if (!$isEmployeesModule)
                                            <form method="POST" action="{{ route('storeowner.modulesetting.auto-renew') }}" class="inline auto-renew-form">
                                                @csrf
                                                <input type="hidden" name="pmid" value="{{ $pm->pmid }}">
                                                <input type="hidden" name="auto_renew" value="{{ $pm->auto_renew ? 1 : 0 }}">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only auto-renew-toggle" {{ $pm->auto_renew ? 'checked' : '' }} />
                                                    <span class="w-10 h-5 rounded-full relative transition auto-renew-track {{ $pm->auto_renew ? 'bg-green-500' : 'bg-gray-300' }}">
                                                        <span class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition auto-renew-thumb {{ $pm->auto_renew ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                                    </span>
                                                    <span class="ml-2 text-xs font-semibold text-gray-600 auto-renew-label">{{ $pm->auto_renew ? 'ON' : 'OFF' }}</span>
                                                </label>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if (!$isEmployeesModule)
                                            <select class="border border-gray-300 rounded-md pl-2 pr-8 py-1 text-sm payment-card-select">
                                                <option value="">Select card</option>
                                                @foreach ($paymentCards as $card)
                                                    <option value="{{ $card->cardid }}">
                                                        {{ $card->card_brand ?? 'Card' }} **** {{ $card->card_last4 }} ({{ sprintf('%02d', $card->expiry_month) }}/{{ $card->expiry_year }})
                                                    </option>
                                                @endforeach
                                                <option value="add_new">+ Add new card</option>
                                            </select>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $renewalTerm }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $isEmployeesModule ? '-' : ($pm->expire_date?->format('m/d/Y') ?? '-') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($isEmployeesModule)
                                            
                                        @elseif (is_null($expiresIn))
                                            -
                                        @elseif ($expiresIn < 0)
                                            <span class="text-red-600">Expired</span>
                                        @elseif ($expiresIn <= 30)
                                            <span class="text-red-600">{{ $expiresIn }} days</span>
                                        @else
                                            <span class="text-gray-700">{{ $expiresIn }} days</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($isEmployeesModule)
                                            <span class="px-3 py-1 rounded font-semibold bg-green-500 text-white">Freemium</span>
                                        @else
                                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded">Renew</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">No installed modules.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($installedModules->isNotEmpty())
                    <div class="mt-4">
                        <button type="button" class="px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800">
                            Renew all selected Modules
                        </button>
                    </div>
                @endif
            </div>

            <!-- Renewals Due -->
            <div id="tab-renewals" class="module-tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800 text-white text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Modules</th>
                                <th class="px-4 py-3 text-left">Renewal Term</th>
                                <th class="px-4 py-3 text-left">Due Date</th>
                                <th class="px-4 py-3 text-left">Expires</th>
                                <th class="px-4 py-3 text-left"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($renewalsDue as $pm)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $pm->module->module ?? 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $pm->isTrial ? 'Freemium' : 'Standard / Core Module' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $pm->expire_date?->format('m/d/Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($pm->days_remaining < 0)
                                            <span class="text-red-600">Expired</span>
                                        @elseif ($pm->days_remaining <= 30)
                                            <span class="text-red-600">{{ $pm->days_remaining }} days</span>
                                        @else
                                            <span class="text-gray-700">{{ $pm->days_remaining }} days</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded">Renew</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">No renewals due.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Billing -->
            <div id="tab-billing" class="module-tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800 text-white text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Order Ref</th>
                                <th class="px-4 py-3 text-left">Description</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($billingItems as $pm)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">{{ $pm->transactionid ?? $pm->pmid }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $pm->module->module ?? 'Module' }} module</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $pm->purchase_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700">€{{ number_format($pm->paid_amount ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No billing history.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for Module Info -->
    @foreach ($availableModules as $module)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="module-info{{ $module->moduleid }}" onclick="if(event.target === this) closeModuleInfoModal('module-info{{ $module->moduleid }}')">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-lg" onclick="event.stopPropagation()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $module->module }}</h3>
                        <button onclick="closeModuleInfoModal('module-info{{ $module->moduleid }}')" class="text-gray-400 hover:text-gray-600 text-xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-4 text-gray-700 leading-relaxed [&_p]:mb-2 [&_p]:last:mb-0">
                        {!! $module->module_detailed_info ?? $module->module_description ?? 'No description available.' !!}
                    </div>
                    @if ($module->dependencies->isNotEmpty())
                        <div class="text-sm text-gray-600">
                            Requires: {{ $module->dependencies->pluck('module')->join(', ') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($installedModules as $pm)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="module-info{{ $pm->moduleid }}-installed" onclick="if(event.target === this) closeModuleInfoModal('module-info{{ $pm->moduleid }}-installed')">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-lg" onclick="event.stopPropagation()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $pm->module->module ?? 'Module' }}</h3>
                        <button onclick="closeModuleInfoModal('module-info{{ $pm->moduleid }}-installed')" class="text-gray-400 hover:text-gray-600 text-xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-4 text-gray-700 leading-relaxed [&_p]:mb-2 [&_p]:last:mb-0">
                        {!! $pm->module->module_detailed_info ?? $pm->module->module_description ?? 'No description available.' !!}
                    </div>
                    @if ($pm->module && $pm->module->dependencies->isNotEmpty())
                        <div class="text-sm text-gray-600">
                            Requires: {{ $pm->module->dependencies->pluck('module')->join(', ') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function showModuleTab(tabId) {
            document.querySelectorAll('.module-tab-panel').forEach(panel => panel.classList.add('hidden'));
            document.getElementById(tabId)?.classList.remove('hidden');
            document.querySelectorAll('.module-tab').forEach(btn => {
                btn.classList.remove('border-gray-800', 'text-gray-800');
                btn.classList.add('border-transparent', 'text-gray-600');
            });
            const activeButton = document.querySelector(`[data-tab="${tabId}"]`);
            if (activeButton) {
                activeButton.classList.add('border-gray-800', 'text-gray-800');
                activeButton.classList.remove('border-transparent', 'text-gray-600');
            }
        }

        document.querySelectorAll('.module-tab').forEach(button => {
            button.addEventListener('click', () => showModuleTab(button.dataset.tab));
        });

        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab');
        if (initialTab === 'installed') {
            showModuleTab('tab-installed');
        } else if (initialTab === 'renewals') {
            showModuleTab('tab-renewals');
        } else if (initialTab === 'billing') {
            showModuleTab('tab-billing');
        }

        document.getElementById('select-all-modules')?.addEventListener('change', function () {
            document.querySelectorAll('.module-select').forEach(cb => {
                cb.checked = this.checked;
            });
        });

        document.getElementById('select-all-installed')?.addEventListener('change', function () {
            document.querySelectorAll('.installed-select').forEach(cb => {
                cb.checked = this.checked;
            });
        });

        document.querySelectorAll('.auto-renew-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const label = this.closest('label')?.querySelector('.auto-renew-label');
                const track = this.closest('label')?.querySelector('.auto-renew-track');
                const thumb = this.closest('label')?.querySelector('.auto-renew-thumb');
                const form = this.closest('form');
                const hidden = form?.querySelector('input[name="auto_renew"]');
                if (!label) return;
                label.textContent = this.checked ? 'ON' : 'OFF';
                if (track && thumb) {
                    track.classList.toggle('bg-green-500', this.checked);
                    track.classList.toggle('bg-gray-300', !this.checked);
                    thumb.style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';
                }
                if (hidden && form) {
                    hidden.value = this.checked ? '1' : '0';
                    form.submit();
                }
            });
        });

        // Add new card button triggers the billing modal

        document.querySelectorAll('.plan-radio').forEach(radio => {
            radio.addEventListener('change', function () {
                const moduleId = this.dataset.module;
                const target = document.querySelector(`.single-plan-${moduleId}`);
                if (target) {
                    target.value = this.value;
                }
            });
        });

        function openModuleInfoModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModuleInfoModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        document.querySelectorAll('.payment-card-select').forEach(select => {
            select.addEventListener('change', function () {
                if (this.value === 'add_new') {
                    window.location.href = "{{ route('storeowner.modulesetting.payment-cards') }}";
                    this.value = '';
                }
            });
        });
    </script>
</x-storeowner-app-layout>
