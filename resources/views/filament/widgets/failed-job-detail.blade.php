{{-- #59 — detalle de un job fallido: payload + excepción completa. --}}
<div class="space-y-4 text-sm">
    <div>
        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.queue_column') }}</p>
        <p class="font-mono">{{ $record->queue }} · {{ $record->connection }}</p>
    </div>

    <div>
        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.failed_at') }}</p>
        <p>{{ optional($record->failed_at)->format('d/m/Y H:i:s') }} · UUID <span class="font-mono text-xs">{{ $record->uuid }}</span></p>
    </div>

    <div>
        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('admin.queue_monitor.exception') }}</p>
        <pre class="mt-1 max-h-96 overflow-auto rounded-md bg-gray-950/90 p-3 text-xs text-gray-100">{{ $record->exception }}</pre>
    </div>

    <div>
        <p class="font-medium text-gray-500 dark:text-gray-400">Payload</p>
        <pre class="mt-1 max-h-64 overflow-auto rounded-md bg-gray-100 p-3 text-xs dark:bg-white/5">{{ $record->payload }}</pre>
    </div>
</div>
