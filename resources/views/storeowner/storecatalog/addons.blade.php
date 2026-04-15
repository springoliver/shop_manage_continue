<x-storeowner-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Addons</h1>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="mb-4 flex justify-between items-center flex-wrap gap-4">
        <input type="text" id="addonSearchbox" placeholder="Search addons..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Show:</label>
            <select id="addonPerPageSelect" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 min-w-[68px] text-sm">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-700">entries</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4">Add / Update Addon</h2>
            <form method="POST" action="{{ route('storeowner.storecatalog.update-addon') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="addonid" id="addon_id_edit">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Addon name</label>
                    <input name="addon" id="addon_name_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Price</label>
                    <input name="price" id="addon_price_edit" type="number" min="0" step="1" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Save Addon</button>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden lg:col-span-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Addon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="addonTableBody">
                    @forelse($addons as $addon)
                        <tr class="addon-row" data-addon="{{ strtolower($addon->addon) }}" data-price="{{ strtolower((string) $addon->price) }}" data-status="{{ strtolower($addon->addon_status) }}">
                            <td class="px-4 py-3">{{ $addon->addon }}</td>
                            <td class="px-4 py-3">{{ $addon->price }}</td>
                            <td class="px-4 py-3">
                                <button type="button" onclick="window.openStatusModal('confirm-addon-status{{ $addon->addonid }}')"
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $addon->addon_status === 'Enable' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $addon->addon_status }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                <button type="button" class="text-gray-600 hover:text-gray-900 edit-addon-btn" title="Edit"
                                    data-id="{{ base64_encode($addon->addonid) }}"
                                    data-name="{{ $addon->addon }}"
                                    data-price="{{ $addon->price }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('storeowner.storecatalog.addons.delete', base64_encode($addon->addonid)) }}" onsubmit="return confirm('Delete this addon?')" class="inline">
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
                        <tr id="noAddonRow"><td colspan="4" class="px-4 py-6 text-center text-gray-500">No addons found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="addonShowingStart">1</span> to <span id="addonShowingEnd">0</span> of <span id="addonTotalEntries">0</span> entries
                </div>
                <div id="addonPaginationControls"></div>
            </div>
        </div>
    </div>
    @foreach($addons as $addon)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="confirm-addon-status{{ $addon->addonid }}" onclick="if(event.target===this) window.closeStatusModal('confirm-addon-status{{ $addon->addonid }}')">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Change Status</h3>
                        <button onclick="window.closeStatusModal('confirm-addon-status{{ $addon->addonid }}')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" action="{{ route('storeowner.storecatalog.addons.change-status') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center"><input type="radio" name="addon_status" value="Enable" {{ $addon->addon_status === 'Enable' ? 'checked' : '' }} class="mr-2"><span>Enable</span></label>
                                <label class="flex items-center"><input type="radio" name="addon_status" value="Disable" {{ $addon->addon_status === 'Disable' ? 'checked' : '' }} class="mr-2"><span>Disable</span></label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <input type="hidden" name="addonid" value="{{ base64_encode($addon->addonid) }}">
                            <button type="button" onclick="window.closeStatusModal('confirm-addon-status{{ $addon->addonid }}')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <script>
        document.querySelectorAll('.edit-addon-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('addon_id_edit').value = btn.dataset.id;
                document.getElementById('addon_name_edit').value = btn.dataset.name;
                document.getElementById('addon_price_edit').value = btn.dataset.price;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        let addonCurrentPage = 1, addonPerPage = 10, addonAllRows = [], addonFilteredRows = [];
        function updateAddonDisplay() {
            const term = (document.getElementById('addonSearchbox')?.value || '').toLowerCase();
            addonFilteredRows = term ? addonAllRows.filter(r => r.textContent.toLowerCase().includes(term)) : [...addonAllRows];
            const totalPages = Math.ceil(addonFilteredRows.length / addonPerPage) || 1;
            if (addonCurrentPage > totalPages) addonCurrentPage = totalPages;
            const start = (addonCurrentPage - 1) * addonPerPage;
            const end = Math.min(start + addonPerPage, addonFilteredRows.length);
            addonAllRows.forEach(r => r.style.display = 'none');
            const noRow = document.getElementById('noAddonRow');
            if (noRow) noRow.style.display = addonFilteredRows.length ? 'none' : '';
            for (let i = start; i < end; i++) if (addonFilteredRows[i]) addonFilteredRows[i].style.display = '';
            document.getElementById('addonShowingStart').textContent = addonFilteredRows.length ? start + 1 : 0;
            document.getElementById('addonShowingEnd').textContent = end;
            document.getElementById('addonTotalEntries').textContent = addonFilteredRows.length;
            const p = document.getElementById('addonPaginationControls'); p.innerHTML = '';
            if (totalPages > 1) {
                const maxVisible = 5;
                let startPage = Math.max(1, addonCurrentPage - Math.floor(maxVisible / 2));
                let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                if (endPage - startPage < maxVisible - 1) {
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }
                const addBtn = (label, page, active = false) => {
                    const b = document.createElement('button');
                    b.textContent = label;
                    b.className = `px-3 py-2 text-sm border border-gray-300 rounded-md ${active ? 'bg-gray-800 text-white' : 'hover:bg-gray-100'}`;
                    b.onclick = () => { addonCurrentPage = page; updateAddonDisplay(); };
                    p.appendChild(b);
                };
                if (startPage > 1) {
                    addBtn('1', 1, addonCurrentPage === 1);
                    if (startPage > 2) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                }
                for (let i = startPage; i <= endPage; i++) {
                    addBtn(String(i), i, i === addonCurrentPage);
                }
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                    addBtn(String(totalPages), totalPages, addonCurrentPage === totalPages);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            addonAllRows = Array.from(document.querySelectorAll('tr.addon-row'));
            document.getElementById('addonSearchbox')?.addEventListener('keyup', () => { addonCurrentPage = 1; updateAddonDisplay(); });
            document.getElementById('addonPerPageSelect')?.addEventListener('change', e => { addonPerPage = parseInt(e.target.value); addonCurrentPage = 1; updateAddonDisplay(); });
            updateAddonDisplay();
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
