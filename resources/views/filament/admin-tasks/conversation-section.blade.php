{{--
    Sprint 3.7 #40 — Sección "Conversación" embebida en ViewAdminTask (Filament).
    Se inyecta via getFooter() en la página de vista de tarea.
--}}
<div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Conversación con el editor</h2>
    </div>

    @if($task->conversation)
        {{-- Hilo existente --}}
        <div style="height: 520px; display: flex; flex-direction: column; overflow: hidden;">
            @livewire('message-thread', [
                'conversation' => $task->conversation,
                'role'         => 'admin',
            ], key('task-conv-'.$task->conversation->id))
        </div>
    @else
        {{-- Sin hilo aún — botón para crear --}}
        <div class="flex flex-col items-center justify-center gap-4 py-12 text-center"
             x-data="{ loading: false }">
            <svg class="h-10 w-10 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
            </svg>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay conversación vinculada a esta tarea.</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Podés abrir un hilo directamente con el editor.</p>
            </div>
            <form method="POST" action="{{ route('admin.tasks.open-conversation', $task) }}">
                @csrf
                <button type="submit"
                        @click="loading = true"
                        :disabled="loading"
                        class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:opacity-60">
                    <span x-show="!loading">Abrir conversación con el editor</span>
                    <span x-show="loading">Creando…</span>
                </button>
            </form>
        </div>
    @endif
</div>
