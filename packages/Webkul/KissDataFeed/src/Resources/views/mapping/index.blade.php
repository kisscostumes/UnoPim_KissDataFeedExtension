<x-admin::layouts>
    <x-slot:title>
        @lang('kiss_datafeed::app.mapping.title')
    </x-slot>

    <v-kiss-field-mapping></v-kiss-field-mapping>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-kiss-field-mapping-template">
            <x-admin::form
                :action="route('kiss_datafeed.mapping.store')"
                enctype="multipart/form-data"
            >
                @method('POST')
                <input type="hidden" name="credential_id" value="{{ $credentialId }}" />

                <div class="flex justify-between items-center">
                    <p class="text-xl text-gray-800 dark:text-slate-50 font-bold">
                        @lang('kiss_datafeed::app.mapping.title') — {{ $credential->api_url }}
                    </p>

                    <div class="flex gap-x-2.5 items-center">
                        <a
                            href="{{ route('kiss_datafeed.credentials.index') }}"
                            class="transparent-button"
                        >
                            @lang('kiss_datafeed::app.credentials.back-btn')
                        </a>

                        <button type="submit" class="primary-button">
                            @lang('kiss_datafeed::app.credentials.save-btn')
                        </button>
                    </div>
                </div>

                <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
                    <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
                        <div class="p-4 bg-white dark:bg-cherry-900 rounded box-shadow">

                            {{-- Header row --}}
                            <div class="grid grid-cols-3 gap-2.5 items-center px-4 py-4 border-b dark:border-cherry-800 text-gray-600 dark:text-gray-300">
                                <p class="break-words font-bold">@lang('kiss_datafeed::app.mapping.api-field')</p>
                                <p class="break-words font-bold">@lang('kiss_datafeed::app.mapping.attribute')</p>
                                <p class="break-words font-bold">@lang('kiss_datafeed::app.mapping.default-value')</p>
                            </div>

                            @foreach ($mappingFields as $field)
                                @php
                                    $fieldName = $field['name'];
                                    $value = $currentMappings[$fieldName] ?? '';
                                    $defaultValue = $currentDefaults[$fieldName] ?? '';
                                @endphp

                                <div class="grid grid-cols-3 gap-2.5 items-center px-4 py-4 border-b dark:border-cherry-800 text-gray-600 dark:text-gray-300 transition-all hover:bg-violet-50 hover:bg-opacity-30 dark:hover:bg-cherry-800">
                                    {{-- Column 1: API Field label --}}
                                    <p class="break-words">{{ $field['label'] }} <span class="text-xs text-gray-400">[{{ $fieldName }}]</span></p>

                                    {{-- Column 2: UnoPim attribute dropdown --}}
                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.control
                                            type="select"
                                            :name="$fieldName"
                                            :value="$value"
                                            :label="$field['label']"
                                            :placeholder="$field['label']"
                                            track-by="code"
                                            label-by="label"
                                            :entityName="json_encode($field['types'])"
                                            async="true"
                                            :list-route="route('kiss_datafeed.options.attributes')"
                                            @@input="handleSelectChange($event, '{{ $fieldName }}')"
                                        />
                                    </x-admin::form.control-group>

                                    {{-- Column 3: Default/fixed value --}}
                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.control
                                            type="text"
                                            :name="'default_' . $fieldName"
                                            :id="'default_' . $fieldName"
                                            :value="old('default_' . $fieldName) ?? $defaultValue"
                                            :placeholder="$field['label']"
                                            ::disabled="disabledFields['default_{{ $fieldName }}']"
                                        />
                                    </x-admin::form.control-group>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-kiss-field-mapping', {
                template: '#v-kiss-field-mapping-template',

                data() {
                    return {
                        disabledFields: {
                            @foreach ($mappingFields as $field)
                                'default_{{ $field['name'] }}': {{ !empty($currentMappings[$field['name']]) ? 'true' : 'false' }},
                            @endforeach
                        },
                    };
                },

                methods: {
                    handleSelectChange(event, fieldName) {
                        var defaultFieldName = 'default_' + fieldName;

                        this.disabledFields[defaultFieldName] = !!event;
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
