@section('page_header', 'Add Employee hours')

<x-storeowner-app-layout>
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex text-gray-500 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('storeowner.dashboard') }}" class="inline-flex items-center hover:text-gray-700">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('storeowner.clocktime.index') }}" class="ml-1 hover:text-gray-700">Clock-in-out</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('storeowner.clocktime.employee_holidays') }}" class="ml-1 hover:text-gray-700">Employee Holidays</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 font-medium text-gray-700 md:ml-2">Add Employee hours</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
            <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">&times;</button>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
            <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">&times;</button>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
                Employee - {{ $employee->firstname ?? 'N/A' }} {{ $employee->lastname ?? '' }}
            </h3>
        </div>

        <form action="{{ route('storeowner.clocktime.store-employee-hours') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="employeeid" value="{{ $employee->employeeid }}">

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                    <div class="flex items-start gap-4 lg:col-span-1">
                        <label for="week_date" class="w-1/3 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                            Select Week
                        </label>
                        <div class="w-2/3">
                            <input type="date"
                                   name="week_date"
                                   id="week_date"
                                   value="{{ old('week_date') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('week_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex items-start gap-4 lg:col-span-1">
                        <label for="week_number" class="w-1/3 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                            Week Number
                        </label>
                        <div class="w-2/3">
                            <input type="text"
                                   id="week_number"
                                   class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                                   readonly>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 lg:col-span-1">
                        <label for="week_start" class="w-1/3 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                            Week Start Date
                        </label>
                        <div class="w-2/3">
                            <input type="text"
                                   id="week_start"
                                   class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                                   readonly>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 lg:col-span-1">
                        <label for="week_end" class="w-1/3 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                            Week End Date
                        </label>
                        <div class="w-2/3">
                            <input type="text"
                                   id="week_end"
                                   class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                                   readonly>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="hours_worked" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Hours to be paid
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="hours_worked"
                               id="hours_worked"
                               value="{{ old('hours_worked', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               required>
                        @error('hours_worked')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="sunday_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Sunday hrs
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="sunday_hrs"
                               id="sunday_hrs"
                               value="{{ old('sunday_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('sunday_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="owertime1_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Overtime
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="owertime1_hrs"
                               id="owertime1_hrs"
                               value="{{ old('owertime1_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('owertime1_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="owertime2_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Overtime
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="owertime2_hrs"
                               id="owertime2_hrs"
                               value="{{ old('owertime2_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('owertime2_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="holiday_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Holiday hrs
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="holiday_hrs"
                               id="holiday_hrs"
                               value="{{ old('holiday_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('holiday_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="sickpay_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Sickpay hrs
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="sickpay_hrs"
                               id="sickpay_hrs"
                               value="{{ old('sickpay_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('sickpay_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="extras1_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Extra hrs 1
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="extras1_hrs"
                               id="extras1_hrs"
                               value="{{ old('extras1_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('extras1_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="extras2_hrs" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Extra hrs 2
                    </label>
                    <div class="w-3/4">
                        <input type="number"
                               step="0.01"
                               name="extras2_hrs"
                               id="extras2_hrs"
                               value="{{ old('extras2_hrs', 0) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('extras2_hrs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <label for="notes" class="w-1/4 pt-2 text-sm font-medium text-gray-700 text-end pr-5">
                        Notes
                    </label>
                    <div class="w-3/4">
                        <input type="text"
                               name="notes"
                               id="notes"
                               value="{{ old('notes') }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-start gap-3">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Save
                </button>
                <a href="{{ route('storeowner.clocktime.weekly-hrs-byemployee', ['employeeid' => base64_encode((string) $employee->employeeid)]) }}"
                   class="px-4 py-2 bg-gray-200 rounded-md">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function getIsoWeekInfo(date) {
            const temp = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const dayNumber = temp.getUTCDay() || 7;
            temp.setUTCDate(temp.getUTCDate() + 4 - dayNumber);
            const yearStart = new Date(Date.UTC(temp.getUTCFullYear(), 0, 1));
            const weekNumber = Math.ceil((((temp - yearStart) / 86400000) + 1) / 7);
            const weekYear = temp.getUTCFullYear();

            const weekStart = new Date(temp);
            weekStart.setUTCDate(temp.getUTCDate() - 3);
            const weekEnd = new Date(weekStart);
            weekEnd.setUTCDate(weekStart.getUTCDate() + 6);

            return {
                weekNumber,
                weekYear,
                weekStart,
                weekEnd,
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            const weekDateInput = document.getElementById('week_date');
            const weekNumberInput = document.getElementById('week_number');
            const weekStartInput = document.getElementById('week_start');
            const weekEndInput = document.getElementById('week_end');

            if (!weekDateInput) {
                return;
            }

            const updateWeekFields = () => {
                if (!weekDateInput.value) {
                    weekNumberInput.value = '';
                    weekStartInput.value = '';
                    weekEndInput.value = '';
                    return;
                }

                const selectedDate = new Date(weekDateInput.value + 'T00:00:00');
                const info = getIsoWeekInfo(selectedDate);
                weekNumberInput.value = info.weekNumber;
                weekStartInput.value = formatDate(info.weekStart);
                weekEndInput.value = formatDate(info.weekEnd);
            };

            weekDateInput.addEventListener('change', updateWeekFields);
            updateWeekFields();
        });
    </script>
    @endpush
</x-storeowner-app-layout>
