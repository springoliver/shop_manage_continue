<x-storeowner-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Payment Methods</h1>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    <div class="mb-4 flex justify-between items-center flex-wrap gap-4">
        <input type="text" id="paymentSearchbox" placeholder="Search payment methods..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Show:</label>
            <select id="paymentPerPageSelect" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 min-w-[68px] text-sm">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-700">entries</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4">Add / Update Payment Method</h2>
            <form method="POST" action="{{ route('storeowner.storecatalog.update-payment-method') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="payment_methodid" id="payment_id_edit">
                <div><label class="block text-sm text-gray-700 mb-1">Payment Method</label><input name="payment_method" id="payment_name_edit" class="w-full border border-gray-300 rounded px-3 py-2" required></div>
                <div><label class="block text-sm text-gray-700 mb-1">Email</label><input name="email" id="payment_email_edit" type="email" class="w-full border border-gray-300 rounded px-3 py-2"></div>
                <div><label class="block text-sm text-gray-700 mb-1">Merchant ID</label><input name="merchantid" id="payment_merchant_edit" type="number" class="w-full border border-gray-300 rounded px-3 py-2"></div>
                <div><label class="block text-sm text-gray-700 mb-1">Currency</label><input name="currency" id="payment_currency_edit" class="w-full border border-gray-300 rounded px-3 py-2"></div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Mode</label>
                    <select name="mode" id="payment_mode_edit" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="Live Mode">Live Mode</option>
                        <option value="Test Mode">Test Mode</option>
                    </select>
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Save Method</button>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden lg:col-span-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Merchant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="paymentTableBody">
                    @forelse($paymentMethods as $payment)
                        <tr class="payment-row">
                            <td class="px-4 py-3">{{ $payment->payment_method }}</td>
                            <td class="px-4 py-3">{{ $payment->email }}</td>
                            <td class="px-4 py-3">{{ $payment->merchantid }}</td>
                            <td class="px-4 py-3">{{ $payment->currency }}</td>
                            <td class="px-4 py-3">
                                <button type="button" onclick="window.openStatusModal('confirm-payment-mode{{ $payment->payment_methodid }}')"
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $payment->mode === 'Live Mode' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $payment->mode }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" onclick="window.openStatusModal('confirm-payment-status{{ $payment->payment_methodid }}')"
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $payment->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $payment->status }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                <button type="button" class="text-gray-600 hover:text-gray-900 edit-payment-btn" title="Edit"
                                    data-id="{{ base64_encode($payment->payment_methodid) }}"
                                    data-method="{{ $payment->payment_method }}"
                                    data-email="{{ $payment->email }}"
                                    data-merchant="{{ $payment->merchantid }}"
                                    data-currency="{{ $payment->currency }}"
                                    data-mode="{{ $payment->mode }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('storeowner.storecatalog.payment-methods.delete', base64_encode($payment->payment_methodid)) }}" onsubmit="return confirm('Delete this payment method?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="noPaymentRow"><td colspan="7" class="px-4 py-6 text-center text-gray-500">No payment methods found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="paymentShowingStart">1</span> to <span id="paymentShowingEnd">0</span> of <span id="paymentTotalEntries">0</span> entries
                </div>
                <div id="paymentPaginationControls"></div>
            </div>
        </div>
    </div>
    @foreach($paymentMethods as $payment)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="confirm-payment-mode{{ $payment->payment_methodid }}" onclick="if(event.target===this) window.closeStatusModal('confirm-payment-mode{{ $payment->payment_methodid }}')">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Change Mode</h3>
                        <button onclick="window.closeStatusModal('confirm-payment-mode{{ $payment->payment_methodid }}')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" action="{{ route('storeowner.storecatalog.payment-methods.change-mode') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mode</label>
                            <div class="space-y-2">
                                <label class="flex items-center"><input type="radio" name="mode" value="Live Mode" {{ $payment->mode === 'Live Mode' ? 'checked' : '' }} class="mr-2"><span>Live Mode</span></label>
                                <label class="flex items-center"><input type="radio" name="mode" value="Test Mode" {{ $payment->mode === 'Test Mode' ? 'checked' : '' }} class="mr-2"><span>Test Mode</span></label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <input type="hidden" name="payment_methodid" value="{{ base64_encode($payment->payment_methodid) }}">
                            <button type="button" onclick="window.closeStatusModal('confirm-payment-mode{{ $payment->payment_methodid }}')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="confirm-payment-status{{ $payment->payment_methodid }}" onclick="if(event.target===this) window.closeStatusModal('confirm-payment-status{{ $payment->payment_methodid }}')">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Change Status</h3>
                        <button onclick="window.closeStatusModal('confirm-payment-status{{ $payment->payment_methodid }}')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" action="{{ route('storeowner.storecatalog.payment-methods.change-status') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center"><input type="radio" name="status" value="Active" {{ $payment->status === 'Active' ? 'checked' : '' }} class="mr-2"><span>Active</span></label>
                                <label class="flex items-center"><input type="radio" name="status" value="Inactive" {{ $payment->status === 'Inactive' ? 'checked' : '' }} class="mr-2"><span>Inactive</span></label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <input type="hidden" name="payment_methodid" value="{{ base64_encode($payment->payment_methodid) }}">
                            <button type="button" onclick="window.closeStatusModal('confirm-payment-status{{ $payment->payment_methodid }}')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <script>
        document.querySelectorAll('.edit-payment-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('payment_id_edit').value = btn.dataset.id;
                document.getElementById('payment_name_edit').value = btn.dataset.method;
                document.getElementById('payment_email_edit').value = btn.dataset.email;
                document.getElementById('payment_merchant_edit').value = btn.dataset.merchant;
                document.getElementById('payment_currency_edit').value = btn.dataset.currency;
                document.getElementById('payment_mode_edit').value = btn.dataset.mode;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        let paymentCurrentPage = 1, paymentPerPage = 10, paymentAllRows = [], paymentFilteredRows = [];
        function updatePaymentDisplay() {
            const term = (document.getElementById('paymentSearchbox')?.value || '').toLowerCase();
            paymentFilteredRows = term ? paymentAllRows.filter(r => r.textContent.toLowerCase().includes(term)) : [...paymentAllRows];
            const totalPages = Math.ceil(paymentFilteredRows.length / paymentPerPage) || 1;
            if (paymentCurrentPage > totalPages) paymentCurrentPage = totalPages;
            const start = (paymentCurrentPage - 1) * paymentPerPage;
            const end = Math.min(start + paymentPerPage, paymentFilteredRows.length);
            paymentAllRows.forEach(r => r.style.display = 'none');
            const noRow = document.getElementById('noPaymentRow');
            if (noRow) noRow.style.display = paymentFilteredRows.length ? 'none' : '';
            for (let i = start; i < end; i++) if (paymentFilteredRows[i]) paymentFilteredRows[i].style.display = '';
            document.getElementById('paymentShowingStart').textContent = paymentFilteredRows.length ? start + 1 : 0;
            document.getElementById('paymentShowingEnd').textContent = end;
            document.getElementById('paymentTotalEntries').textContent = paymentFilteredRows.length;
            const p = document.getElementById('paymentPaginationControls'); p.innerHTML = '';
            if (totalPages > 1) {
                const maxVisible = 5;
                let startPage = Math.max(1, paymentCurrentPage - Math.floor(maxVisible / 2));
                let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                if (endPage - startPage < maxVisible - 1) {
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }
                const addBtn = (label, page, active = false) => {
                    const b = document.createElement('button');
                    b.textContent = label;
                    b.className = `px-3 py-2 text-sm border border-gray-300 rounded-md ${active ? 'bg-gray-800 text-white' : 'hover:bg-gray-100'}`;
                    b.onclick = () => { paymentCurrentPage = page; updatePaymentDisplay(); };
                    p.appendChild(b);
                };
                if (startPage > 1) {
                    addBtn('1', 1, paymentCurrentPage === 1);
                    if (startPage > 2) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                }
                for (let i = startPage; i <= endPage; i++) {
                    addBtn(String(i), i, i === paymentCurrentPage);
                }
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                    addBtn(String(totalPages), totalPages, paymentCurrentPage === totalPages);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            paymentAllRows = Array.from(document.querySelectorAll('tr.payment-row'));
            document.getElementById('paymentSearchbox')?.addEventListener('keyup', () => { paymentCurrentPage = 1; updatePaymentDisplay(); });
            document.getElementById('paymentPerPageSelect')?.addEventListener('change', e => { paymentPerPage = parseInt(e.target.value); paymentCurrentPage = 1; updatePaymentDisplay(); });
            updatePaymentDisplay();
        });
        window.openStatusModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.style.setProperty('display', 'block', 'important');
            document.body.style.overflow = 'hidden';
        };
        window.closeStatusModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.style.setProperty('display', 'none', 'important');
            document.body.style.overflow = '';
        };
    </script>
</x-storeowner-app-layout>
