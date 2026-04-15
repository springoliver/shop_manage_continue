<x-storeowner-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Modifiers</h1>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    <div class="mb-4 flex justify-between items-center flex-wrap gap-4">
        <input type="text" id="modifierSearchbox" placeholder="Search modifiers..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Show:</label>
            <select id="modifierPerPageSelect" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 min-w-[68px] text-sm">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-700">entries</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4">Add / Update Modifier</h2>
            <form method="POST" action="{{ route('storeowner.storecatalog.update-modifier') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="modifier_id" id="modifier_id_edit">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Modifier name</label>
                    <input name="modifier_name" id="modifier_name_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Price</label>
                    <input name="modifier_price" id="modifier_price_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Save Modifier</button>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden lg:col-span-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modifier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="modifierTableBody">
                    @forelse($modifiers as $modifier)
                        <tr class="modifier-row">
                            <td class="px-4 py-3">{{ $modifier->modifier_name }}</td>
                            <td class="px-4 py-3">{{ $modifier->modifier_price }}</td>
                            <td class="px-4 py-3">
                                <button type="button" onclick="window.openStatusModal('confirm-modifier-status{{ $modifier->modifier_id }}')"
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $modifier->modifier_status === 'Enable' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $modifier->modifier_status }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                <button type="button" class="text-gray-600 hover:text-gray-900 edit-modifier-btn" title="Edit"
                                    data-id="{{ base64_encode($modifier->modifier_id) }}"
                                    data-name="{{ $modifier->modifier_name }}"
                                    data-price="{{ $modifier->modifier_price }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('storeowner.storecatalog.modifiers.delete', base64_encode($modifier->modifier_id)) }}" onsubmit="return confirm('Delete this modifier?')" class="inline">
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
                        <tr id="noModifierRow"><td colspan="4" class="px-4 py-6 text-center text-gray-500">No modifiers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="modifierShowingStart">1</span> to <span id="modifierShowingEnd">0</span> of <span id="modifierTotalEntries">0</span> entries
                </div>
                <div id="modifierPaginationControls"></div>
            </div>
        </div>
    </div>
    @foreach($modifiers as $modifier)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="confirm-modifier-status{{ $modifier->modifier_id }}" onclick="if(event.target===this) window.closeStatusModal('confirm-modifier-status{{ $modifier->modifier_id }}')">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Change Status</h3>
                        <button onclick="window.closeStatusModal('confirm-modifier-status{{ $modifier->modifier_id }}')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" action="{{ route('storeowner.storecatalog.modifiers.change-status') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center"><input type="radio" name="modifier_status" value="Enable" {{ $modifier->modifier_status === 'Enable' ? 'checked' : '' }} class="mr-2"><span>Enable</span></label>
                                <label class="flex items-center"><input type="radio" name="modifier_status" value="Disable" {{ $modifier->modifier_status === 'Disable' ? 'checked' : '' }} class="mr-2"><span>Disable</span></label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <input type="hidden" name="modifier_id" value="{{ base64_encode($modifier->modifier_id) }}">
                            <button type="button" onclick="window.closeStatusModal('confirm-modifier-status{{ $modifier->modifier_id }}')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <script>
        document.querySelectorAll('.edit-modifier-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('modifier_id_edit').value = btn.dataset.id;
                document.getElementById('modifier_name_edit').value = btn.dataset.name;
                document.getElementById('modifier_price_edit').value = btn.dataset.price;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        let modifierCurrentPage = 1, modifierPerPage = 10, modifierAllRows = [], modifierFilteredRows = [];
        function updateModifierDisplay() {
            const term = (document.getElementById('modifierSearchbox')?.value || '').toLowerCase();
            modifierFilteredRows = term ? modifierAllRows.filter(r => r.textContent.toLowerCase().includes(term)) : [...modifierAllRows];
            const totalPages = Math.ceil(modifierFilteredRows.length / modifierPerPage) || 1;
            if (modifierCurrentPage > totalPages) modifierCurrentPage = totalPages;
            const start = (modifierCurrentPage - 1) * modifierPerPage;
            const end = Math.min(start + modifierPerPage, modifierFilteredRows.length);
            modifierAllRows.forEach(r => r.style.display = 'none');
            const noRow = document.getElementById('noModifierRow');
            if (noRow) noRow.style.display = modifierFilteredRows.length ? 'none' : '';
            for (let i = start; i < end; i++) if (modifierFilteredRows[i]) modifierFilteredRows[i].style.display = '';
            document.getElementById('modifierShowingStart').textContent = modifierFilteredRows.length ? start + 1 : 0;
            document.getElementById('modifierShowingEnd').textContent = end;
            document.getElementById('modifierTotalEntries').textContent = modifierFilteredRows.length;
            const p = document.getElementById('modifierPaginationControls'); p.innerHTML = '';
            if (totalPages > 1) {
                const maxVisible = 5;
                let startPage = Math.max(1, modifierCurrentPage - Math.floor(maxVisible / 2));
                let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                if (endPage - startPage < maxVisible - 1) {
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }
                const addBtn = (label, page, active = false) => {
                    const b = document.createElement('button');
                    b.textContent = label;
                    b.className = `px-3 py-2 text-sm border border-gray-300 rounded-md ${active ? 'bg-gray-800 text-white' : 'hover:bg-gray-100'}`;
                    b.onclick = () => { modifierCurrentPage = page; updateModifierDisplay(); };
                    p.appendChild(b);
                };
                if (startPage > 1) {
                    addBtn('1', 1, modifierCurrentPage === 1);
                    if (startPage > 2) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                }
                for (let i = startPage; i <= endPage; i++) {
                    addBtn(String(i), i, i === modifierCurrentPage);
                }
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                    addBtn(String(totalPages), totalPages, modifierCurrentPage === totalPages);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            modifierAllRows = Array.from(document.querySelectorAll('tr.modifier-row'));
            document.getElementById('modifierSearchbox')?.addEventListener('keyup', () => { modifierCurrentPage = 1; updateModifierDisplay(); });
            document.getElementById('modifierPerPageSelect')?.addEventListener('change', e => { modifierPerPage = parseInt(e.target.value); modifierCurrentPage = 1; updateModifierDisplay(); });
            updateModifierDisplay();
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
