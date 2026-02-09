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
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-gray-400"></i>
                    <a href="{{ route('storeowner.modulesetting.checkout') }}" class="ml-1 hover:text-gray-700">Review &amp; Checkout</a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 font-medium text-gray-700 md:ml-2">Checkout</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Checkout</h2>
        <div class="text-xs text-gray-500 mb-6">My Invoices / Invoice #{{ $invoiceNumber }}</div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Payment Method</h3>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center space-x-4 text-sm font-medium text-gray-600 border-b pb-3 mb-4">
                        <span class="text-green-700">Use Existing Card</span>
                        <span class="text-gray-400">Enter New Card Information Below</span>
                    </div>

                    @forelse ($paymentCards as $card)
                        <label class="flex items-center justify-between border border-green-500 rounded-md p-3 mb-3 cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="payment_card_id" class="rounded border-gray-300" checked>
                                <div class="text-sm text-gray-700">
                                    <div class="font-semibold">{{ $card->card_brand ?? 'Card' }}-{{ $card->card_last4 }}</div>
                                    <div class="text-xs text-gray-500">Visa {{ $card->card_last4 }} **** {{ $card->card_last4 }} exp {{ sprintf('%02d', $card->expiry_month) }}/{{ $card->expiry_year }}</div>
                                </div>
                            </div>
                            <div class="text-xs text-green-600 font-semibold">Active</div>
                        </label>
                    @empty
                        <div class="text-sm text-gray-500">No cards on file.</div>
                    @endforelse

                    <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs rounded p-3">
                        Any data you enter here is submitted securely and is encrypted to reduce the risk of fraud.
                        Stripe will be used for payment processing.
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 w-[75%]">
                <div class="bg-green-700 text-white rounded-lg p-5">
                    <div class="text-lg font-semibold mb-4">Invoice #{{ $invoiceNumber }}</div>
                    <div class="space-y-3 text-sm">
                        @forelse ($summaryItems as $summary)
                            <div class="flex items-center justify-between">
                                <div>{{ strtoupper($summary->module) }} Module</div>
                                <div>{{ $summary->cycle === 'yearly' ? 'Yearly' : 'Monthly' }} €{{ number_format($summary->amount, 0) }}</div>
                            </div>
                        @empty
                            <div>No items</div>
                        @endforelse
                    </div>

                    <div class="border-t border-green-600 my-4"></div>
                    <div class="flex items-center justify-between text-sm">
                        <div>Sub Total</div>
                        <div>€{{ number_format($subtotal, 2) }}</div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div>{{ number_format($vatRate * 100, 0) }}% VAT</div>
                        <div>€{{ number_format($vatAmount, 2) }}</div>
                    </div>

                    <div class="border-t border-green-600 my-4"></div>
                    <div class="flex items-center justify-between text-lg font-semibold">
                        <div>Total Due</div>
                        <div>€{{ number_format($total, 2) }}</div>
                    </div>

                    <button class="mt-4 w-full bg-white text-green-700 font-semibold py-2 rounded-md opacity-60 cursor-not-allowed">
                        Pay
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-storeowner-app-layout>
