<x-storeowner-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Products</h1>
        <a href="{{ route('storeowner.storecatalog.add') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md">Add Product</a>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="mb-4 flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-2">
            <input type="text" id="searchbox" placeholder="Search catalog products..."
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Show:</label>
            <select id="perPageSelect" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 min-w-[68px] text-sm">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-sm text-gray-700">entries</span>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto p-6">
        <table class="min-w-full divide-y divide-gray-200" id="catalogTable">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sortable" data-sort="name" style="cursor: pointer;">Name <span class="sort-indicator"><i class="fas fa-sort text-gray-400"></i></span></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sortable" data-sort="group" style="cursor: pointer;">Group <span class="sort-indicator"><i class="fas fa-sort text-gray-400"></i></span></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sortable" data-sort="category" style="cursor: pointer;">Category <span class="sort-indicator"><i class="fas fa-sort text-gray-400"></i></span></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sortable" data-sort="price" style="cursor: pointer;">Price <span class="sort-indicator"><i class="fas fa-sort text-gray-400"></i></span></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sortable" data-sort="status" style="cursor: pointer;">Status <span class="sort-indicator"><i class="fas fa-sort text-gray-400"></i></span></th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="catalogTableBody">
                @forelse($catalogProducts as $product)
                    <tr class="catalog-row hover:bg-gray-50"
                        data-row-index="{{ $loop->index }}"
                        data-name="{{ strtolower($product->catalog_product_name ?? '') }}"
                        data-group="{{ strtolower($product->catalog_product_group_name ?? '') }}"
                        data-category="{{ strtolower($product->catalog_product_category_name ?? '') }}"
                        data-price="{{ strtolower($product->catalog_product_price ?? '') }}"
                        data-status="{{ strtolower($product->catalog_product_status ?? '') }}">
                        <td class="px-4 py-3">{{ $product->catalog_product_name }}</td>
                        <td class="px-4 py-3">{{ $product->catalog_product_group_name }}</td>
                        <td class="px-4 py-3">
                            <a class="text-indigo-600 hover:underline"
                                href="{{ route('storeowner.storecatalog.by-category', base64_encode($product->catalog_product_categoryid)) }}">
                                {{ $product->catalog_product_category_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $product->catalog_product_price }}</td>
                        <td class="px-4 py-3">
                            <button type="button" onclick="window.openStatusModal('confirm-status{{ $product->catalog_product_id }}')"
                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->catalog_product_status === 'Enable' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $product->catalog_product_status }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="flex justify-center space-x-3">
                                <a href="{{ route('storeowner.storecatalog.edit', base64_encode($product->catalog_product_id)) }}" class="text-gray-600 hover:text-gray-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('storeowner.storecatalog.destroy', base64_encode($product->catalog_product_id)) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
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
                    <tr id="noCatalogRow">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No catalog products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span id="showingStart">1</span> to <span id="showingEnd">0</span> of <span id="totalEntries">0</span> entries
            </div>
            <div id="paginationControls"></div>
        </div>
    </div>

    @foreach($catalogProducts as $product)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="confirm-status{{ $product->catalog_product_id }}" onclick="if(event.target===this) window.closeStatusModal('confirm-status{{ $product->catalog_product_id }}')">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Change Status</h3>
                        <button onclick="window.closeStatusModal('confirm-status{{ $product->catalog_product_id }}')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('storeowner.storecatalog.change-status') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="catalog_product_status" value="Enable" {{ $product->catalog_product_status === 'Enable' ? 'checked' : '' }} class="mr-2">
                                    <span>Enable</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="catalog_product_status" value="Disable" {{ $product->catalog_product_status === 'Disable' ? 'checked' : '' }} class="mr-2">
                                    <span>Disable</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <input type="hidden" name="catalog_product_id" value="{{ base64_encode($product->catalog_product_id) }}">
                            <button type="button" onclick="window.closeStatusModal('confirm-status{{ $product->catalog_product_id }}')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @push('scripts')
    <script>
        let currentPage = 1, perPage = 10, allRows = [], filteredRows = [], sortColumn = null, sortDirection = 'asc';
        function initTable() {
            const tbody = document.getElementById('catalogTableBody');
            allRows = Array.from(tbody.querySelectorAll('tr.catalog-row'));
            filteredRows = [...allRows];
            const noRow = document.getElementById('noCatalogRow');
            if (noRow && allRows.length > 0) noRow.style.display = 'none';
            perPage = parseInt(document.getElementById('perPageSelect').value);
            updateDisplay();
        }
        function updateDisplay() {
            const tbody = document.getElementById('catalogTableBody');
            allRows = Array.from(tbody.querySelectorAll('tr.catalog-row'));
            const term = (document.getElementById('searchbox')?.value || '').toLowerCase();
            filteredRows = term ? allRows.filter(r => r.textContent.toLowerCase().includes(term)) : [...allRows];
            if (sortColumn) {
                filteredRows.sort((a,b) => {
                    const av = a.getAttribute(`data-${sortColumn}`) || '', bv = b.getAttribute(`data-${sortColumn}`) || '';
                    if (av < bv) return sortDirection === 'asc' ? -1 : 1;
                    if (av > bv) return sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            }
            const totalPages = Math.ceil(filteredRows.length / perPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * perPage;
            const end = Math.min(start + perPage, filteredRows.length);
            allRows.forEach(r => r.style.display = 'none');
            const noRow = document.getElementById('noCatalogRow');
            if (noRow) noRow.style.display = filteredRows.length ? 'none' : '';
            for (let i = start; i < end; i++) if (filteredRows[i]) filteredRows[i].style.display = '';
            document.getElementById('showingStart').textContent = filteredRows.length ? start + 1 : 0;
            document.getElementById('showingEnd').textContent = end;
            document.getElementById('totalEntries').textContent = filteredRows.length;
            renderPagination(totalPages);
        }
        function renderPagination(totalPages) {
            const div = document.getElementById('paginationControls'); div.innerHTML = '';
            if (totalPages <= 1) return;
            const mk = (txt, disabled, cb, active = false) => {
                const b = document.createElement('button');
                b.textContent = txt; b.disabled = disabled;
                b.className = `px-3 py-2 text-sm border border-gray-300 rounded-md ${active ? 'bg-gray-800 text-white' : 'hover:bg-gray-100'} ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`;
                b.onclick = cb; return b;
            };
            div.appendChild(mk('Previous', currentPage === 1, () => { currentPage--; updateDisplay(); }));
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }
            if (startPage > 1) {
                div.appendChild(mk('1', false, () => { currentPage = 1; updateDisplay(); }, currentPage === 1));
                if (startPage > 2) {
                    const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                    div.appendChild(e);
                }
            }
            for (let i = startPage; i <= endPage; i++) {
                div.appendChild(mk(String(i), false, () => { currentPage = i; updateDisplay(); }, i === currentPage));
            }
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                    div.appendChild(e);
                }
                div.appendChild(mk(String(totalPages), false, () => { currentPage = totalPages; updateDisplay(); }, currentPage === totalPages));
            }
            div.appendChild(mk('Next', currentPage === totalPages, () => { currentPage++; updateDisplay(); }));
        }
        function sortTable(column, th) {
            sortDirection = sortColumn === column && sortDirection === 'asc' ? 'desc' : 'asc';
            sortColumn = column;
            document.querySelectorAll('.sortable .sort-indicator').forEach(i => i.innerHTML = '<i class="fas fa-sort text-gray-400"></i>');
            const ind = th.querySelector('.sort-indicator');
            if (ind) ind.innerHTML = sortDirection === 'asc' ? '<i class="fas fa-sort-up text-gray-800"></i>' : '<i class="fas fa-sort-down text-gray-800"></i>';
            currentPage = 1; updateDisplay();
        }
        document.addEventListener('DOMContentLoaded', () => {
            initTable();
            document.getElementById('searchbox')?.addEventListener('keyup', () => { currentPage = 1; updateDisplay(); });
            document.getElementById('perPageSelect')?.addEventListener('change', e => { perPage = parseInt(e.target.value); currentPage = 1; updateDisplay(); });
            document.querySelectorAll('.sortable').forEach(th => th.addEventListener('click', () => sortTable(th.dataset.sort, th)));
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
    @endpush
</x-storeowner-app-layout>
