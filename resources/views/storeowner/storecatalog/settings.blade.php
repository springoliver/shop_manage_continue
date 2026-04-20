<x-storeowner-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Group</h1>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4">Add / Update Group</h2>
            <form method="POST" action="{{ route('storeowner.storecatalog.groups.update') }}" class="space-y-3" id="catalogGroupForm">
                @csrf
                <input type="hidden" name="catalog_product_groupid" id="catalog_group_id_edit">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Group Name</label>
                    <input name="catalog_product_group_name" id="catalog_group_name_edit" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Save Group</button>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden lg:col-span-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Groups</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($groups as $group)
                        <tr>
                            <td class="px-4 py-3">{{ $group->catalog_product_group_name }}</td>
                            <td class="px-4 py-3 text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                    <button type="button"
                                        class="text-gray-600 hover:text-gray-900 edit-group-btn"
                                        title="Edit"
                                        data-id="{{ base64_encode($group->catalog_product_groupid) }}"
                                        data-name="{{ $group->catalog_product_group_name }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('storeowner.storecatalog.groups.delete', base64_encode($group->catalog_product_groupid)) }}" class="inline" onsubmit="return confirm('Delete this group?')">
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
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-gray-500">No groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h2 class="text-lg font-semibold mb-4">Catalog Settings</h2>
        <form action="{{ route('storeowner.storecatalog.settings.update') }}" method="POST">
            @csrf
            <div class="space-y-4">
                @forelse($settings as $setting)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $setting->title }}</label>
                        <input type="text" name="value[{{ $setting->settingid }}]" value="{{ old('value.' . $setting->settingid, $setting->value) }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No catalog settings configured for this store.</p>
                @endforelse
            </div>
            <div class="mt-6">
                <button class="px-4 py-2 bg-gray-800 text-white rounded-md">Save Settings</button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.edit-group-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('catalog_group_id_edit').value = btn.dataset.id;
                document.getElementById('catalog_group_name_edit').value = btn.dataset.name;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
</x-storeowner-app-layout>
