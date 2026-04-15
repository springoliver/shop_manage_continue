<x-storeowner-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">Catalog Settings</h1>
        <div class="flex gap-2">
            <a href="{{ route('storeowner.storecatalog.categories') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md">Categories</a>
            <a href="{{ route('storeowner.storecatalog.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md">Catalog Products</a>
        </div>
    </div>
    @include('storeowner.storecatalog._nav')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <form action="{{ route('storeowner.storecatalog.settings.update') }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        <div class="space-y-4">
            @forelse($settings as $setting)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $setting->title }}</label>
                    <input
                        type="text"
                        name="value[{{ $setting->settingid }}]"
                        value="{{ old('value.' . $setting->settingid, $setting->value) }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
            @empty
                <p class="text-sm text-gray-500">No catalog settings configured for this store.</p>
            @endforelse
        </div>
        <div class="mt-6">
            <button class="px-4 py-2 bg-gray-800 text-white rounded-md">Save Settings</button>
        </div>
    </form>
</x-storeowner-app-layout>
