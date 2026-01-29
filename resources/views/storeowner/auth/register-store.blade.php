<x-guest-layout>
    <div class="w-full max-w-2xl bg-white/95 rounded-lg shadow-lg p-8">
        <h1 class="text-center mb-6 font-medium text-xl">Store Registration</h1>
        
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        @if (session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('storeowner.register.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <input type="hidden" name="ownerid" value="{{ $ownerid }}">

            <!-- Store Name -->
            <div class="flex items-start gap-4">
                <label for="store_name" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                    Store Name<span class="text-red-500"> *</span>
                </label>
                <div class="w-3/4">
                    <x-text-input id="store_name" class="block w-full" type="text" name="store_name" :value="old('store_name')" placeholder="Enter Store Name." required autofocus />
                    <x-input-error :messages="$errors->get('store_name')" class="mt-2" />
                </div>
            </div>

            <!-- Website URL -->
            <div class="flex items-start gap-4">
                <label for="weburl" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                    Website URL<span class="text-red-500"> *</span>
                </label>
                <div class="w-3/4">
                    <x-text-input id="weburl" class="block w-full" type="url" name="weburl" :value="old('weburl')" placeholder="Enter Website URL." required />
                    <x-input-error :messages="$errors->get('weburl')" class="mt-2" />
                </div>
            </div>

            <!-- Store Email -->
            <div class="flex items-start gap-4">
                <label for="store_email" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                    Store Email<span class="text-red-500"> *</span>
                </label>
                <div class="w-3/4">
                    <x-text-input id="store_email" class="block w-full" type="email" name="store_email" :value="old('store_email')" placeholder="Enter Store Email." required />
                    <x-input-error :messages="$errors->get('store_email')" class="mt-2" />
                </div>
            </div>

            <!-- Store Address -->
            <div class="flex items-start gap-4">
                <label for="address1" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                    Store Address<span class="text-red-500"> *</span>
                </label>
                <div class="w-3/4">
                    <x-text-input id="address1" class="block w-full" type="text" name="address1" placeholder="Location" :value="old('address1')" required />
                    <x-input-error :messages="$errors->get('address1')" class="mt-2" />
                </div>
            </div>

            <!-- Hidden fields for Google Places API -->
            <div style="display: none;">
                <input type="hidden" name="address_lat1" id="address_lat1" value="{{ old('address_lat1') }}">
                <input type="hidden" name="address_lng1" id="address_lng1" value="{{ old('address_lng1') }}">
                <input type="hidden" name="address_formatted_address1" id="address_formatted_address1" value="{{ old('address_formatted_address1') }}">
                <input type="hidden" name="address_street_number1" id="address_street_number1" value="{{ old('address_street_number1') }}">
                <input type="hidden" name="address_street1" id="address_street1" value="{{ old('address_street1') }}">
                <input type="hidden" name="address_airport1" id="address_airport1" value="{{ old('address_airport1') }}">
                <input type="hidden" name="address_state1" id="address_state1" value="{{ old('address_state1') }}">
                <input type="hidden" name="address_country1" id="address_country1" value="{{ old('address_country1') }}">
                <input type="hidden" name="address_city1" id="address_city1" value="{{ old('address_city1') }}">
                <input type="hidden" name="address_zipcode1" id="address_zipcode1" value="{{ old('address_zipcode1') }}">
                <input type="hidden" name="address_location_type1" id="address_location_type1" value="{{ old('address_location_type1') }}">
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-primary-button>
                    Register
                </x-primary-button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const addressInput = document.getElementById('address1');
            const fields = {
                lat: document.getElementById('address_lat1'),
                lng: document.getElementById('address_lng1'),
                formatted: document.getElementById('address_formatted_address1'),
                streetNumber: document.getElementById('address_street_number1'),
                street: document.getElementById('address_street1'),
                state: document.getElementById('address_state1'),
                country: document.getElementById('address_country1'),
                city: document.getElementById('address_city1'),
                zip: document.getElementById('address_zipcode1'),
            };

            function setAddressFields(address, lat, lng, displayName) {
                if (fields.lat) fields.lat.value = lat ?? '';
                if (fields.lng) fields.lng.value = lng ?? '';
                if (fields.formatted) fields.formatted.value = displayName ?? '';
                if (fields.streetNumber) fields.streetNumber.value = address?.house_number || '';
                if (fields.street) fields.street.value = address?.road || '';
                if (fields.state) fields.state.value = address?.state || address?.state_district || '';
                if (fields.country) fields.country.value = address?.country || '';
                if (fields.city) fields.city.value = address?.city || address?.town || address?.village || '';
                if (fields.zip) fields.zip.value = address?.postcode || '';
                if (addressInput && !addressInput.value) {
                    addressInput.value = displayName ?? '';
                }
            }

            function reverseGeocode(lat, lng) {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then((res) => res.ok ? res.json() : null)
                    .then((data) => {
                        if (!data || !data.address) {
                            return;
                        }
                        setAddressFields(data.address, lat, lng, data.display_name);
                    })
                    .catch(() => {});
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        reverseGeocode(lat, lng);
                    },
                    () => {},
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                );
            }
        })();
    </script>
</x-guest-layout>

