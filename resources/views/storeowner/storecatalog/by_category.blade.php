<x-storeowner-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">
            Catalog Products - {{ $category->catalog_product_category_name }}
        </h1>
        <a href="{{ route('storeowner.storecatalog.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md">Back</a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($catalogProducts as $product)
                    <tr>
                        <td class="px-4 py-3">{{ $product->catalog_product_name }}</td>
                        <td class="px-4 py-3">{{ $product->catalog_product_price }}</td>
                        <td class="px-4 py-3">{{ $product->catalog_product_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-storeowner-app-layout>
