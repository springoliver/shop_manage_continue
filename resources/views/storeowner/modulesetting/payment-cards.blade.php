@section('page_header', 'Billing - Manage Payment Methods')

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
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('storeowner.modulesetting.index', ['tab' => 'installed']) }}" class="ml-1 text-gray-500 hover:text-gray-700 md:ml-2">Modules</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 font-medium text-gray-700 md:ml-2">Add New Card</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-gray-100 border border-gray-200 rounded-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
                <h2 class="text-lg font-semibold text-gray-700">Billing - Manage Payment Methods</h2>
                <p class="text-sm text-gray-500">Payment Cards</p>
            </div>
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md" onclick="openBillingAddressModal()">
                Add New Card
            </button>
        </div>
        <div class="px-6 py-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Name on Card</th>
                            <th class="px-4 py-3 text-left">Card Number</th>
                            <th class="px-4 py-3 text-left">Expiry Date</th>
                            <th class="px-4 py-3 text-left">Assigned Services</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($paymentCards as $card)
                            <tr>
                                <td class="px-4 py-3 text-gray-700">{{ $card->name_on_card }}</td>
                                <td class="px-4 py-3 text-gray-700">xxxx xxxx xxxx {{ $card->card_last4 }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ sprintf('%02d', $card->expiry_month) }}/{{ $card->expiry_year }}</td>
                                <td class="px-4 py-3 text-gray-700">0</td>
                                <td class="px-4 py-3 text-gray-700">Actions</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No payment cards found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="billing-address-modal" onclick="if(event.target === this) closeBillingAddressModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Billing Address</h2>
                    <button onclick="closeBillingAddressModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-6">
                    No payment will be taken unless renewals are due.
                    You may however notice a €1 pre-authorisation transaction on your bank statement.
                </p>
                <form method="POST" action="{{ route('storeowner.modulesetting.payment-cards.address.store') }}">
                    @csrf
                    <input type="hidden" name="pmid" value="{{ request('pmid') }}">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Card Type *</label>
                            <select name="card_type" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                                <option value="">Please Choose...</option>
                                <option value="Visa">Visa</option>
                                <option value="Mastercard">Mastercard</option>
                                <option value="Amex">Amex</option>
                                <option value="Discover">Discover</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">First Name *</label>
                            <input type="text" name="first_name" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Surname *</label>
                            <input type="text" name="surname" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Company Name</label>
                            <input type="text" name="company_name" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">House Number / Name</label>
                            <input type="text" name="house_number" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Street *</label>
                            <input type="text" name="street" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Area</label>
                            <input type="text" name="area" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Town / City *</label>
                            <input type="text" name="town_city" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">County</label>
                            <input type="text" name="county" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Postcode *</label>
                            <input type="text" name="postcode" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" class="px-4 py-2 bg-gray-200 rounded-md" onclick="closeBillingAddressModal()">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="billing-address-data" data-address='@json(session('payment_card_address', []))'></div>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="card-details-modal" onclick="if(event.target === this) closeCardDetailsModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Add New Card</h2>
                    <button onclick="closeCardDetailsModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('storeowner.modulesetting.payment-cards.details.store') }}" id="stripe-card-form">
                    @csrf
                    <input type="hidden" name="payment_method_id" id="payment_method_id">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Card No. *</label>
                            <div id="card-number-element" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Expiration *</label>
                                <div id="card-expiry-element" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white"></div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">CVC *</label>
                                <div id="card-cvc-element" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Name (as on card) *</label>
                            <input type="text" name="name_on_card" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-red-600 hidden" id="card-errors"></div>
                    <div class="mt-6 flex items-center justify-center">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md">Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        function openBillingAddressModal() {
            document.getElementById('billing-address-modal')?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeBillingAddressModal() {
            document.getElementById('billing-address-modal')?.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openCardDetailsModal() {
            document.getElementById('card-details-modal')?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                ensureStripeElements();
            }, 50);
        }

        function closeCardDetailsModal() {
            document.getElementById('card-details-modal')?.classList.add('hidden');
            document.body.style.overflow = '';
        }

        const modalParam = new URLSearchParams(window.location.search).get('modal');
        if (modalParam === 'address') {
            openBillingAddressModal();
        }
        if (modalParam === 'details') {
            openCardDetailsModal();
        }

        const stripeKey = "{{ config('services.stripe.key') }}";
        const stripe = stripeKey ? Stripe(stripeKey) : null;
        const elements = stripe ? stripe.elements() : null;
        const elementStyle = {
            base: {
                fontSize: '14px',
                color: '#111827',
                '::placeholder': { color: '#9CA3AF' },
            },
        };
        const cardNumberElement = elements ? elements.create('cardNumber', { style: elementStyle }) : null;
        const cardExpiryElement = elements ? elements.create('cardExpiry', { style: elementStyle }) : null;
        const cardCvcElement = elements ? elements.create('cardCvc', { style: elementStyle }) : null;
        let stripeElementsMounted = false;

        const cardForm = document.getElementById('stripe-card-form');
        const cardErrors = document.getElementById('card-errors');
        const paymentMethodInput = document.getElementById('payment_method_id');
        const billingAddressElement = document.getElementById('billing-address-data');
        const billingAddress = billingAddressElement?.dataset.address
            ? JSON.parse(billingAddressElement.dataset.address)
            : {};

        function ensureStripeElements() {
            if (!stripe || !cardNumberElement) {
                if (cardErrors) {
                    cardErrors.textContent = 'Stripe is not configured. Please contact support.';
                    cardErrors.classList.remove('hidden');
                }
                return;
            }
            if (stripeElementsMounted) {
                return;
            }
            cardNumberElement.mount('#card-number-element');
            cardExpiryElement.mount('#card-expiry-element');
            cardCvcElement.mount('#card-cvc-element');
            stripeElementsMounted = true;
        }

        if (modalParam === 'details') {
            ensureStripeElements();
        }

        if (cardForm) {
            cardForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                cardErrors?.classList.add('hidden');
                cardErrors.textContent = '';

                if (!stripe || !cardNumberElement) {
                    cardErrors.textContent = 'Stripe is not configured. Please contact support.';
                    cardErrors.classList.remove('hidden');
                    return;
                }

                const nameOnCard = cardForm.querySelector('input[name="name_on_card"]')?.value || '';
                const fullName = nameOnCard || `${billingAddress.first_name || ''} ${billingAddress.surname || ''}`.trim();

                const { paymentMethod, error } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardNumberElement,
                    billing_details: {
                        name: fullName,
                        address: {
                            line1: `${billingAddress.house_number || ''} ${billingAddress.street || ''}`.trim() || undefined,
                            line2: billingAddress.area || undefined,
                            city: billingAddress.town_city || undefined,
                            state: billingAddress.county || undefined,
                            postal_code: billingAddress.postcode || undefined,
                        },
                    },
                });

                if (error) {
                    cardErrors.textContent = error.message || 'Card validation failed.';
                    cardErrors.classList.remove('hidden');
                    return;
                }

                paymentMethodInput.value = paymentMethod.id;
                cardForm.submit();
            });
        }
    </script>
</x-storeowner-app-layout>
