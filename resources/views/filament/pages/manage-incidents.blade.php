<x-filament-panels::page>

    {{-- Formulário de novo incidente --}}
    <x-filament::section heading="Registrar Incidente">
        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-m-exclamation-triangle" color="danger">
                    Registrar incidente
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Histórico de incidentes --}}
    <x-filament::section heading="Histórico de Incidentes">
        @if(empty($incidents))
            <div class="flex items-center gap-2 py-4 text-success-600 dark:text-success-400">
                <x-heroicon-m-check-circle class="w-5 h-5"/>
                <p class="text-sm">Nenhum incidente registrado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="pb-3 pr-4">Título</th>
                            <th class="pb-3 pr-4">Repositório</th>
                            <th class="pb-3 pr-4">Severidade</th>
                            <th class="pb-3 pr-4">Status</th>
                            <th class="pb-3 pr-4">Aberto em</th>
                            <th class="pb-3 pr-4">Resolvido em</th>
                            <th class="pb-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($incidents as $incident)
                            @php
                                $severityColor = match($incident['severity'] ?? '') {
                                    'critical' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                    'high'     => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                    'medium'   => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                    default    => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                };
                                $statusColor = match($incident['status'] ?? '') {
                                    'open'          => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                    'investigating' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                    'resolved'      => 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200',
                                    default         => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                };
                                $statusLabel = match($incident['status'] ?? '') {
                                    'open'          => 'Aberto',
                                    'investigating' => 'Investigando',
                                    'resolved'      => 'Resolvido',
                                    default         => ucfirst($incident['status'] ?? '—'),
                                };
                                $openedAt = isset($incident['opened_at'])
                                    ? \Carbon\Carbon::parse($incident['opened_at'])->format('d/m/Y H:i')
                                    : '—';
                                $resolvedAt = isset($incident['resolved_at'])
                                    ? \Carbon\Carbon::parse($incident['resolved_at'])->format('d/m/Y H:i')
                                    : '—';
                            @endphp
                            <tr wire:key="inc-{{ $incident['id'] }}">
                                <td class="py-3 pr-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $incident['title'] ?? '—' }}
                                        </p>
                                        @if(!empty($incident['description']))
                                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                {{ $incident['description'] }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-400">
                                    {{ $incident['repository']['name'] ?? '—' }}
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $severityColor }}">
                                        {{ ucfirst($incident['severity'] ?? '—') }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                    {{ $openedAt }}
                                </td>
                                <td class="py-3 pr-4 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                    {{ $resolvedAt }}
                                </td>
                                <td class="py-3">
                                    <div class="flex gap-1">
                                        @if(($incident['status'] ?? '') === 'open')
                                            <x-filament::button
                                                color="warning"
                                                size="sm"
                                                wire:click="investigate({{ $incident['id'] }})"
                                                wire:loading.attr="disabled"
                                            >
                                                Investigar
                                            </x-filament::button>
                                        @endif
                                        @if(in_array($incident['status'] ?? '', ['open', 'investigating']))
                                            <x-filament::button
                                                color="success"
                                                size="sm"
                                                wire:click="resolve({{ $incident['id'] }}, '{{ addslashes($incident['title'] ?? '') }}')"
                                                wire:confirm="Marcar como resolvido?"
                                                wire:loading.attr="disabled"
                                            >
                                                Resolver
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
