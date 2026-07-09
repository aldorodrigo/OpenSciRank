<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('admin.queue_monitor.cron_health') }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.cron_command') }}</th>
                        <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.cron_last_run') }}</th>
                        <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.cron_runtime') }}</th>
                        <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.cron_state') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->getRows() as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2 pr-4 align-top">
                                <span class="font-mono text-xs">{{ $row['command'] }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $row['description'] }}</span>
                            </td>
                            <td class="py-2 pr-4 align-top whitespace-nowrap">
                                @if ($row['never'])
                                    <span class="text-gray-400">{{ __('admin.queue_monitor.cron_never') }}</span>
                                @else
                                    {{ $row['last_ran_at'] }}
                                    <span class="text-gray-400">· {{ $row['ago'] }}</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 align-top">{{ $row['runtime'] ?? '—' }}</td>
                            <td class="py-2 pr-4 align-top">
                                @if ($row['status'] === \App\Models\ScheduledTaskRun::STATUS_FAILED)
                                    <x-filament::badge color="danger">{{ __('admin.queue_monitor.cron_failed') }}</x-filament::badge>
                                    @if ($row['error'])
                                        <span class="text-danger-600 text-xs">{{ \Illuminate\Support\Str::limit($row['error'], 80) }}</span>
                                    @endif
                                @elseif ($row['overdue'])
                                    <x-filament::badge color="warning">{{ __('admin.queue_monitor.cron_overdue') }}</x-filament::badge>
                                @else
                                    <x-filament::badge color="success">{{ __('admin.queue_monitor.cron_ok') }}</x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
