<x-storeowner-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Categories</h1>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    <div class="mb-4 flex justify-between items-center flex-wrap gap-4">
        <input type="text" id="categorySearchbox" placeholder="Search categories..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Show:</label>
            <select id="categoryPerPageSelect" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 min-w-[68px] text-sm">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="text-sm text-gray-700">entries</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4 lg:col-span-1">
            <h2 class="text-lg font-semibold mb-4">Add / Update Category</h2>
            <form method="POST" action="{{ route('storeowner.storecatalog.categories.update') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="catalog_product_categoryid" id="category_id_edit">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Group</label>
                    <select name="catalog_product_groupid" id="group_id_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">Select Group</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->catalog_product_groupid }}">{{ $group->catalog_product_group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Name</label>
                    <input name="catalog_product_category_name" id="category_name_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Colour</label>
                    <input name="catalog_product_category_colour" id="category_colour_edit" value="CCCCCC" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Tax</label>
                    <select name="catalog_product_taxid" id="category_tax_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">Select Tax</option>
                        @foreach ($taxSettings as $tax)
                            <option value="{{ $tax->taxid }}">{{ $tax->tax_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Sell Online</label>
                    <select name="catalog_product_sell_online" id="category_status_edit" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="Enable">Enable</option>
                        <option value="Disable">Disable</option>
                    </select>
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Save Category</button>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden lg:col-span-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Group</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sell Online</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="categoryTableBody">
                    @forelse($categories as $category)
                        <tr class="category-row" data-name="{{ strtolower($category->catalog_product_category_name) }}" data-group="{{ strtolower($category->catalog_product_group_name) }}" data-tax="{{ strtolower($category->tax_name) }}" data-status="{{ strtolower($category->catalog_product_sell_online) }}">
                            <td class="px-4 py-3">{{ $category->catalog_product_category_name }}</td>
                            <td class="px-4 py-3">{{ $category->catalog_product_group_name }}</td>
                            <td class="px-4 py-3">{{ $category->tax_name }}</td>
                            <td class="px-4 py-3">
                                <button type="button" onclick="window.openStatusModal('confirm-category-status{{ $category->catalog_product_categoryid }}')"
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $category->catalog_product_sell_online === 'Enable' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $category->catalog_product_sell_online }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                <button
                                    type="button"
                                    class="text-gray-600 hover:text-gray-900 edit-category-btn"
                                    title="Edit"
                                    data-id="{{ base64_encode($category->catalog_product_categoryid) }}"
                                    data-group-id="{{ $category->catalog_product_groupid }}"
                                    data-name="{{ $category->catalog_product_category_name }}"
                                    data-colour="{{ $category->catalog_product_category_colour }}"
                                    data-tax-id="{{ $category->catalog_product_taxid }}"
                                    data-sell-online="{{ $category->catalog_product_sell_online }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('storeowner.storecatalog.categories.delete', base64_encode($category->catalog_product_categoryid)) }}" onsubmit="return confirm('Delete this category?')" class="inline">
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
                        <tr id="noCategoryRow">
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="categoryShowingStart">1</span> to <span id="categoryShowingEnd">0</span> of <span id="categoryTotalEntries">0</span> entries
                </div>
                <div id="categoryPaginationControls"></div>
            </div>
        </div>
    </div>

    @foreach($categories as $category)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden" id="confirm-category-status{{ $category->catalog_product_categoryid }}" onclick="if(event.target===this) window.closeStatusModal('confirm-category-status{{ $category->catalog_product_categoryid }}')">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Change Status</h3>
                        <button onclick="window.closeStatusModal('confirm-category-status{{ $category->catalog_product_categoryid }}')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <form method="POST" action="{{ route('storeowner.storecatalog.categories.change-status') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center"><input type="radio" name="catalog_product_sell_online" value="Enable" {{ $category->catalog_product_sell_online === 'Enable' ? 'checked' : '' }} class="mr-2"><span>Enable</span></label>
                                <label class="flex items-center"><input type="radio" name="catalog_product_sell_online" value="Disable" {{ $category->catalog_product_sell_online === 'Disable' ? 'checked' : '' }} class="mr-2"><span>Disable</span></label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <input type="hidden" name="catalog_product_categoryid" value="{{ base64_encode($category->catalog_product_categoryid) }}">
                            <button type="button" onclick="window.closeStatusModal('confirm-category-status{{ $category->catalog_product_categoryid }}')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.querySelectorAll('.edit-category-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('category_id_edit').value = btn.dataset.id;
                document.getElementById('group_id_edit').value = btn.dataset.groupId;
                document.getElementById('category_name_edit').value = btn.dataset.name;
                document.getElementById('category_colour_edit').value = btn.dataset.colour;
                document.getElementById('category_tax_edit').value = btn.dataset.taxId;
                document.getElementById('category_status_edit').value = btn.dataset.sellOnline;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        let categoryCurrentPage = 1, categoryPerPage = 10, categoryAllRows = [], categoryFilteredRows = [];
        function updateCategoryDisplay() {
            const term = (document.getElementById('categorySearchbox')?.value || '').toLowerCase();
            categoryFilteredRows = term ? categoryAllRows.filter(r => r.textContent.toLowerCase().includes(term)) : [...categoryAllRows];
            const totalPages = Math.ceil(categoryFilteredRows.length / categoryPerPage) || 1;
            if (categoryCurrentPage > totalPages) categoryCurrentPage = totalPages;
            const start = (categoryCurrentPage - 1) * categoryPerPage;
            const end = Math.min(start + categoryPerPage, categoryFilteredRows.length);
            categoryAllRows.forEach(r => r.style.display = 'none');
            const noRow = document.getElementById('noCategoryRow');
            if (noRow) noRow.style.display = categoryFilteredRows.length ? 'none' : '';
            for (let i = start; i < end; i++) if (categoryFilteredRows[i]) categoryFilteredRows[i].style.display = '';
            document.getElementById('categoryShowingStart').textContent = categoryFilteredRows.length ? start + 1 : 0;
            document.getElementById('categoryShowingEnd').textContent = end;
            document.getElementById('categoryTotalEntries').textContent = categoryFilteredRows.length;
            const p = document.getElementById('categoryPaginationControls'); p.innerHTML = '';
            if (totalPages > 1) {
                const maxVisible = 5;
                let startPage = Math.max(1, categoryCurrentPage - Math.floor(maxVisible / 2));
                let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                if (endPage - startPage < maxVisible - 1) {
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }
                const addBtn = (label, page, active = false) => {
                    const b = document.createElement('button');
                    b.textContent = label;
                    b.className = `px-3 py-2 text-sm border border-gray-300 rounded-md ${active ? 'bg-gray-800 text-white' : 'hover:bg-gray-100'}`;
                    b.onclick = () => { categoryCurrentPage = page; updateCategoryDisplay(); };
                    p.appendChild(b);
                };
                if (startPage > 1) {
                    addBtn('1', 1, categoryCurrentPage === 1);
                    if (startPage > 2) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                }
                for (let i = startPage; i <= endPage; i++) {
                    addBtn(String(i), i, i === categoryCurrentPage);
                }
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const e = document.createElement('span'); e.textContent = '...'; e.className = 'px-2';
                        p.appendChild(e);
                    }
                    addBtn(String(totalPages), totalPages, categoryCurrentPage === totalPages);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            categoryAllRows = Array.from(document.querySelectorAll('tr.category-row'));
            document.getElementById('categorySearchbox')?.addEventListener('keyup', () => { categoryCurrentPage = 1; updateCategoryDisplay(); });
            document.getElementById('categoryPerPageSelect')?.addEventListener('change', e => { categoryPerPage = parseInt(e.target.value); categoryCurrentPage = 1; updateCategoryDisplay(); });
            updateCategoryDisplay();
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
