<x-admin::layouts>
    <x-slot:title>
        @lang('kiss_datafeed::app.mapping.title')
    </x-slot>

    <div class="flex justify-between items-center">
        <p class="text-xl text-gray-800 dark:text-slate-50 font-bold">
            @lang('kiss_datafeed::app.mapping.title')
        </p>
    </div>

    <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
        <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
            <div class="p-4 bg-white dark:bg-cherry-900 rounded box-shadow">
                <p class="text-base text-gray-800 dark:text-white font-semibold mb-4">
                    @lang('kiss_datafeed::app.mapping.select-credential')
                </p>

                @if ($credentials->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">
                        @lang('kiss_datafeed::app.mapping.no-credentials')
                    </p>
                @else
                    <div class="grid gap-2.5">
                        @foreach ($credentials as $credential)
                            <a
                                href="{{ route('kiss_datafeed.mapping.index', $credential->id) }}"
                                class="flex items-center justify-between p-4 border dark:border-cherry-800 rounded cursor-pointer transition-all hover:bg-violet-50 hover:bg-opacity-30 dark:hover:bg-cherry-800"
                            >
                                <div>
                                    <p class="text-gray-800 dark:text-white font-semibold">{{ $credential->api_url }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $credential->client_id }}</p>
                                </div>

                                <i class="icon-arrow-right text-2xl"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin::layouts>
