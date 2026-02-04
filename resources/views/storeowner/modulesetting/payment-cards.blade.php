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
            <a href="#add-card-form" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                Add New Card
            </a>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($paymentCards as $card)
                            <tr>
                                <td class="px-4 py-3 text-gray-700">{{ $card->name_on_card }}</td>
                                <td class="px-4 py-3 text-gray-700">xxxx xxxx xxxx {{ $card->card_last4 }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ sprintf('%02d', $card->expiry_month) }}/{{ $card->expiry_year }}</td>
                                <td class="px-4 py-3 text-gray-700">0</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">No payment cards found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-storeowner-app-layout>
