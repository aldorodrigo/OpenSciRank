<x-filament-panels::page>
    {{-- Tarjetas de conteo: cola de correo, cola total, fallidos, enviados hoy --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-filament::section>
            <div class="flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-envelope" class="h-8 w-8 text-primary-500" />
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.mail_queue') }}</p>
                    <p class="text-2xl font-bold">{{ $this->getMailQueueCount() }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-queue-list" class="h-8 w-8 text-gray-500" />
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.total_queue') }}</p>
                    <p class="text-2xl font-bold">{{ $this->getTotalQueueCount() }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-8 w-8 text-danger-500" />
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.failed_jobs') }}</p>
                    <p class="text-2xl font-bold text-danger-600">{{ $this->getFailedJobsCount() }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-check-badge" class="h-8 w-8 text-success-500" />
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.sent_today') }}</p>
                    <p class="text-2xl font-bold text-success-600">{{ $this->getSentTodayCount() }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Listado de últimos jobs fallidos --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            {{ __('admin.queue_monitor.recent_failures') }}
        </x-slot>

        @php($failedJobs = $this->getFailedJobsList())

        @if (empty($failedJobs))
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.no_failures') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">ID</th>
                            <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.job') }}</th>
                            <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.queue_column') }}</th>
                            <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.failed_at') }}</th>
                            <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.exception') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($failedJobs as $job)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 pr-4 align-top">{{ $job['id'] }}</td>
                                <td class="py-2 pr-4 align-top font-mono text-xs">{{ $job['job_name'] }}</td>
                                <td class="py-2 pr-4 align-top">{{ $job['queue'] }}</td>
                                <td class="py-2 pr-4 align-top whitespace-nowrap">{{ $job['failed_at'] }}</td>
                                <td class="py-2 pr-4 align-top text-danger-600">{{ $job['exception_line'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
