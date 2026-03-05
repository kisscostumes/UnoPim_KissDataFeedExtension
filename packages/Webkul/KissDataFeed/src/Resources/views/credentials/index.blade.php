<x-admin::layouts>
    <x-slot:title>
        @lang('kiss_datafeed::app.credentials.title')
    </x-slot>

    <v-kiss-credentials>
        <div class="flex justify-between items-center">
            <p class="text-xl text-gray-800 dark:text-slate-50 font-bold">
                @lang('kiss_datafeed::app.credentials.title')
            </p>

            <div class="flex gap-x-2.5 items-center">
                @if (bouncer()->hasPermission('kiss_datafeed.credentials.create'))
                    <button type="button" class="primary-button">
                        @lang('kiss_datafeed::app.credentials.create-btn')
                    </button>
                @endif
            </div>
        </div>

        <x-admin::shimmer.datagrid />
    </v-kiss-credentials>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-kiss-credentials-template">
            <div class="flex justify-between items-center">
                <p class="text-xl text-gray-800 dark:text-slate-50 font-bold">
                    @lang('kiss_datafeed::app.credentials.title')
                </p>

                <div class="flex gap-x-2.5 items-center">
                    @if (bouncer()->hasPermission('kiss_datafeed.credentials.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @@click="$refs.credentialCreateModal.open()"
                        >
                            @lang('kiss_datafeed::app.credentials.create-btn')
                        </button>
                    @endif
                </div>
            </div>

            <x-admin::datagrid :src="route('kiss_datafeed.credentials.index')" ref="datagrid" class="mb-8" />

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @@submit="handleSubmit($event, create)" ref="credentialCreateForm">
                    <x-admin::modal ref="credentialCreateModal">
                        <x-slot:header>
                            <p class="text-lg text-gray-800 dark:text-white font-bold">
                                @lang('kiss_datafeed::app.credentials.create-btn')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('kiss_datafeed::app.credentials.api-url')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="api_url"
                                    name="api_url"
                                    rules="required"
                                    :label="trans('kiss_datafeed::app.credentials.api-url')"
                                    :placeholder="trans('kiss_datafeed::app.credentials.api-url-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="api_url" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('kiss_datafeed::app.credentials.client-id')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="client_id"
                                    name="client_id"
                                    rules="required"
                                    :label="trans('kiss_datafeed::app.credentials.client-id')"
                                    :placeholder="trans('kiss_datafeed::app.credentials.client-id')"
                                />

                                <x-admin::form.control-group.error control-name="client_id" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('kiss_datafeed::app.credentials.client-secret')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="password"
                                    id="client_secret"
                                    name="client_secret"
                                    rules="required"
                                    :label="trans('kiss_datafeed::app.credentials.client-secret')"
                                    :placeholder="trans('kiss_datafeed::app.credentials.client-secret')"
                                />

                                <x-admin::form.control-group.error control-name="client_secret" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <x-slot:footer>
                            <div class="flex gap-x-2.5 items-center">
                                <button type="submit" class="primary-button">
                                    @lang('kiss_datafeed::app.credentials.save-btn')
                                </button>
                            </div>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-kiss-credentials', {
                template: '#v-kiss-credentials-template',

                methods: {
                    create(params, { setErrors }) {
                        let formData = new FormData(this.$refs.credentialCreateForm);

                        this.$axios.post("{{ route('kiss_datafeed.credentials.store') }}", formData)
                            .then((response) => {
                                window.location.href = response.data.redirect_url;
                            })
                            .catch(error => {
                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
