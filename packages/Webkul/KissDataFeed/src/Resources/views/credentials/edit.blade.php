<x-admin::layouts>
    <x-slot:title>
        @lang('kiss_datafeed::app.credentials.edit-title')
    </x-slot>

    <x-admin::form
        :action="route('kiss_datafeed.credentials.update', ['id' => $credential->id])"
    >
        @method('PUT')

        <div class="flex justify-between items-center">
            <p class="text-xl text-gray-800 dark:text-slate-50 font-bold">
                @lang('kiss_datafeed::app.credentials.edit-title')
            </p>

            <div class="flex gap-x-2.5 items-center">
                <a
                    href="{{ route('kiss_datafeed.credentials.index') }}"
                    class="transparent-button"
                >
                    @lang('kiss_datafeed::app.credentials.back-btn')
                </a>

                <a
                    href="{{ route('kiss_datafeed.mapping.index', $credential->id) }}"
                    class="secondary-button"
                >
                    @lang('kiss_datafeed::app.mapping.title')
                </a>

                <button type="submit" class="primary-button">
                    @lang('kiss_datafeed::app.credentials.save-btn')
                </button>
            </div>
        </div>

        <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
            <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
                <div class="p-4 bg-white dark:bg-cherry-900 rounded box-shadow">
                    <p class="text-base text-gray-800 dark:text-white font-semibold mb-4">
                        @lang('kiss_datafeed::app.credentials.general')
                    </p>

                    <x-admin::form.control-group class="w-[525px]">
                        <x-admin::form.control-group.label class="required">
                            @lang('kiss_datafeed::app.credentials.api-url')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="api_url"
                            name="api_url"
                            rules="required"
                            :value="old('api_url') ?? $credential->api_url"
                            :label="trans('kiss_datafeed::app.credentials.api-url')"
                            :placeholder="trans('kiss_datafeed::app.credentials.api-url-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="api_url" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group class="w-[525px]">
                        <x-admin::form.control-group.label class="required">
                            @lang('kiss_datafeed::app.credentials.client-id')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="client_id"
                            name="client_id"
                            rules="required"
                            :value="old('client_id') ?? $credential->client_id"
                            :label="trans('kiss_datafeed::app.credentials.client-id')"
                        />

                        <x-admin::form.control-group.error control-name="client_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group class="w-[525px]">
                        <x-admin::form.control-group.label>
                            @lang('kiss_datafeed::app.credentials.client-secret')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="password"
                            id="client_secret"
                            name="client_secret"
                            :label="trans('kiss_datafeed::app.credentials.client-secret')"
                            :placeholder="trans('kiss_datafeed::app.credentials.client-secret-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="client_secret" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('kiss_datafeed::app.credentials.active')
                        </x-admin::form.control-group.label>

                        <input type="hidden" name="active" value="0" />

                        <x-admin::form.control-group.control
                            type="switch"
                            name="active"
                            value="1"
                            :checked="(boolean) $credential->active"
                        />
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
