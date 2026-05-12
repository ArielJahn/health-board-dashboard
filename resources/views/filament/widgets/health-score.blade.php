<x-filament-widgets::widget>
    <x-filament::section :heading="$this->getHeading()">
        @if(empty($scores))
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Nenhum repositório encontrado ou API indisponível.
            </p>
        @else
            <div class="space-y-3">
                @foreach($scores as $repo)
                    @php
                        $color = match($repo['status']) {
                            'healthy'  => ['bar' => 'bg-success-500', 'badge' => 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200'],
                            'degraded' => ['bar' => 'bg-warning-500', 'badge' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200'],
                            default    => ['bar' => 'bg-danger-500',  'badge' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200'],
                        };
                        $label = match($repo['status']) {
                            'healthy'  => 'Saudável',
                            'degraded' => 'Degradado',
                            default    => 'Crítico',
                        };
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-40 shrink-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $repo['full_name'] }}">
                                {{ $repo['name'] }}
                            </p>
                        </div>

                        <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="{{ $color['bar'] }} h-2 rounded-full transition-all duration-500"
                                 style="width: {{ $repo['score'] }}%"></div>
                        </div>

                        <span class="w-10 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ $repo['score'] }}
                        </span>

                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $color['badge'] }}">
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
