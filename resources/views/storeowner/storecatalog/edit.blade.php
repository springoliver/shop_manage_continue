<x-storeowner-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">Edit Catalog Product</h1>
        <a href="{{ route('storeowner.storecatalog.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md">Back</a>
    </div>
    @include('storeowner.storecatalog._nav')

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('storeowner.storecatalog.update') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        @include('storeowner.storecatalog._form')
        <div class="pt-2">
            <button class="px-4 py-2 bg-gray-800 text-white rounded-md">Update Product</button>
        </div>
    </form>
</x-storeowner-app-layout>
