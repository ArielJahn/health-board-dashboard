<x-filament-widgets::widget>
    <x-filament::section :heading="$this->getHeading()">
        @if(empty($incidents))
            <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                <x-heroicon-m-check-circle class="w-5 h-5"/>
                <p class="text-sm font-medium">Nenhum incidente aberto. Tudo operacional!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="pb-2 pr-4">Título</th>
                            <th class="pb-2 pr-4">Repositório</th>
                            <th class="pb-2 pr-4">Severidade</th>
                            <th class="pb-2 pr-4">Status</th>
                            <th class="pb-2">Aberto em</th>
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
                                    'investigating' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                    'open'          => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                    default         => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                };
                                $openedAt = isset($incident['opened_at'])
                                    ? \Carbon\Carbon::parse($incident['opened_at'])->format('d/m/Y H:i')
                                    : '—';
                            @endphp
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-900 dark:text-white">
                                    {{ $incident['title'] ?? '—' }}
                                </td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">
                                    {{ $incident['repository']['name'] ?? $incident['repository_id'] ?? '—' }}
                                </td>
                                <td class="py-2 pr-4">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $severityColor }}">
                                        {{ ucfirst($incident['severity'] ?? '—') }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusColor }}">
                                        {{ ucfirst($incident['status'] ?? '—') }}
                                    </span>
                                </td>
                                <td class="py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $openedAt }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
