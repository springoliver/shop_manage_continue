<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('storeowner.storecatalog.index') }}" class="px-3 py-2 rounded {{ request()->routeIs('storeowner.storecatalog.index', 'storeowner.storecatalog.add', 'storeowner.storecatalog.edit', 'storeowner.storecatalog.by-category') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800' }}">Products</a>
    <a href="{{ route('storeowner.storecatalog.modifiers') }}" class="px-3 py-2 rounded {{ request()->routeIs('storeowner.storecatalog.modifiers') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800' }}">Modifiers</a>
    <a href="{{ route('storeowner.storecatalog.addons') }}" class="px-3 py-2 rounded {{ request()->routeIs('storeowner.storecatalog.addons') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800' }}">Addons</a>
    <a href="{{ route('storeowner.storecatalog.payment-methods') }}" class="px-3 py-2 rounded {{ request()->routeIs('storeowner.storecatalog.payment-methods') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800' }}">Payment Methods</a>
    <a href="{{ route('storeowner.storecatalog.categories') }}" class="px-3 py-2 rounded {{ request()->routeIs('storeowner.storecatalog.categories') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800' }}">Categories</a>
    <a href="{{ route('storeowner.storecatalog.settings') }}" class="px-3 py-2 rounded {{ request()->routeIs('storeowner.storecatalog.settings') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800' }}">Settings</a>
</div>
