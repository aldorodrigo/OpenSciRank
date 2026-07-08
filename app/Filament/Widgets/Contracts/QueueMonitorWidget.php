<?php

namespace App\Filament\Widgets\Contracts;

/**
 * #59 — marcador para los widgets que pertenecen exclusivamente a la página
 * QueueMonitor. Se auto-descubren (para quedar registrados como componentes
 * Livewire) pero el Dashboard los excluye vía este contrato, así aparecen solo
 * donde QueueMonitor los declara en get{Header,Footer}Widgets().
 */
interface QueueMonitorWidget {}
