<x-admin::layouts>
    <x-slot:title>
        @lang('kiss_datafeed::app.export.title')
    </x-slot>

    <v-kiss-export></v-kiss-export>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-kiss-export-template">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('kiss_datafeed::app.export.title')
                </p>
            </div>

            <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
                <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
                    <div class="p-4 bg-white dark:bg-cherry-900 rounded box-shadow">
                        <p class="text-base text-gray-800 dark:text-white font-semibold mb-4">
                            @lang('kiss_datafeed::app.export.settings')
                        </p>

                        {{-- Credential selector --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                                @lang('kiss_datafeed::app.export.credential') <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="credentialId"
                                class="w-full max-w-[525px] rounded border px-3 py-2.5 text-sm text-gray-600 dark:text-gray-300 dark:bg-cherry-800 dark:border-cherry-800 transition-all hover:border-gray-400"
                                :disabled="isExporting"
                            >
                                <option value="">@lang('kiss_datafeed::app.export.select-credential')</option>
                                @foreach ($credentials as $credential)
                                    <option value="{{ $credential->id }}">
                                        {{ $credential->api_url }} ({{ $credential->client_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <p class="text-base text-gray-800 dark:text-white font-semibold mb-4 mt-6">
                            @lang('kiss_datafeed::app.export.filters')
                        </p>

                        {{-- Status filter --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                                @lang('kiss_datafeed::app.export.filter-status')
                            </label>

                            <select
                                v-model="filters.status"
                                class="w-full max-w-[525px] rounded border px-3 py-2.5 text-sm text-gray-600 dark:text-gray-300 dark:bg-cherry-800 dark:border-cherry-800 transition-all hover:border-gray-400"
                                :disabled="isExporting"
                            >
                                <option value="">@lang('kiss_datafeed::app.export.all-statuses')</option>
                                <option value="active">@lang('kiss_datafeed::app.export.status-active')</option>
                                <option value="inactive">@lang('kiss_datafeed::app.export.status-inactive')</option>
                            </select>
                        </div>

                        {{-- Attribute Family filter --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                                @lang('kiss_datafeed::app.export.filter-family')
                            </label>

                            <select
                                v-model="filters.attribute_family_id"
                                class="w-full max-w-[525px] rounded border px-3 py-2.5 text-sm text-gray-600 dark:text-gray-300 dark:bg-cherry-800 dark:border-cherry-800 transition-all hover:border-gray-400"
                                :disabled="isExporting"
                            >
                                <option value="">@lang('kiss_datafeed::app.export.all-families')</option>
                                @foreach ($attributeFamilies as $family)
                                    <option value="{{ $family->id }}">{{ $family->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Category filter --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                                @lang('kiss_datafeed::app.export.filter-category')
                            </label>

                            <select
                                v-model="filters.category"
                                class="w-full max-w-[525px] rounded border px-3 py-2.5 text-sm text-gray-600 dark:text-gray-300 dark:bg-cherry-800 dark:border-cherry-800 transition-all hover:border-gray-400"
                                :disabled="isExporting"
                            >
                                <option value="">@lang('kiss_datafeed::app.export.all-categories')</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->code }}">{{ $category->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Export button --}}
                        <button
                            type="button"
                            class="primary-button mt-2"
                            :disabled="!credentialId || isExporting"
                            @@click="startExport"
                        >
                            <template v-if="isExporting">
                                <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                                @lang('kiss_datafeed::app.export.exporting')
                            </template>
                            <template v-else>
                                @lang('kiss_datafeed::app.export.export-btn')
                            </template>
                        </button>
                    </div>

                    {{-- Success message (queued) --}}
                    <div v-if="successMessage" class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded box-shadow">
                        <p class="text-sm text-green-700 dark:text-green-400">@{{ successMessage }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            @lang('kiss_datafeed::app.export.redirecting')
                        </p>
                    </div>

                    {{-- Error message --}}
                    <div v-if="errorMessage" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded box-shadow">
                        <p class="text-sm text-red-600 dark:text-red-400">@{{ errorMessage }}</p>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-kiss-export', {
                template: '#v-kiss-export-template',

                data() {
                    return {
                        credentialId: '',
                        filters: {
                            status: '',
                            attribute_family_id: '',
                            category: '',
                        },
                        isExporting: false,
                        successMessage: null,
                        errorMessage: null,
                    };
                },

                methods: {
                    startExport() {
                        if (!this.credentialId) return;

                        this.isExporting = true;
                        this.successMessage = null;
                        this.errorMessage = null;

                        let payload = {
                            credential_id: this.credentialId,
                        };

                        if (this.filters.status) {
                            payload.status = this.filters.status;
                        }

                        if (this.filters.attribute_family_id) {
                            payload.attribute_family_id = this.filters.attribute_family_id;
                        }

                        if (this.filters.category) {
                            payload.category = this.filters.category;
                        }

                        this.$axios.post("{{ route('kiss_datafeed.export.run') }}", payload)
                        .then((response) => {
                            this.isExporting = false;
                            this.successMessage = response.data.message;

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message,
                            });

                            if (response.data.redirect_url) {
                                setTimeout(() => {
                                    window.location.href = response.data.redirect_url;
                                }, 1500);
                            }
                        })
                        .catch((error) => {
                            this.isExporting = false;

                            if (error.response && error.response.data && error.response.data.error) {
                                this.errorMessage = error.response.data.error;
                            } else {
                                this.errorMessage = '@lang('kiss_datafeed::app.export.export-error')';
                            }
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
