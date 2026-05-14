<x-filament-panels::page>

    {{-- Formulário de adição --}}
    <x-filament::section heading="Adicionar Repositório">
        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-m-plus">
                    Adicionar repositório
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Lista de repositórios monitorados --}}
    <x-filament::section heading="Repositórios monitorados">
        @if(empty($repositories))
            <div class="flex items-center gap-2 py-4 text-gray-500 dark:text-gray-400">
                <x-heroicon-m-server-stack class="w-5 h-5"/>
                <p class="text-sm">Nenhum repositório cadastrado ainda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="pb-3 pr-6">Repositório</th>
                            <th class="pb-3 pr-6">URL</th>
                            <th class="pb-3 pr-6">Token</th>
                            <th class="pb-3 pr-6">Cadastrado em</th>
                            <th class="pb-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($repositories as $repo)
                            <tr wire:key="repo-{{ $repo['id'] }}">
                                <td class="py-3 pr-6">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $repo['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $repo['full_name'] }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-3 pr-6">
                                    <a href="{{ $repo['github_url'] }}"
                                       target="_blank"
                                       class="text-primary-600 hover:underline text-xs truncate max-w-xs block">
                                        {{ $repo['github_url'] }}
                                    </a>
                                </td>
                                <td class="py-3 pr-6">
                                    @if(!empty($repo['has_access_token']) || !empty($repo['access_token']))
                                        <span class="inline-flex items-center gap-1 text-xs text-success-600 dark:text-success-400">
                                            <x-heroicon-m-lock-closed class="w-3 h-3"/> Configurado
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-6 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                    {{ isset($repo['created_at'])
                                        ? \Carbon\Carbon::parse($repo['created_at'])->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <x-filament::button
                                            color="info"
                                            size="sm"
                                            icon="heroicon-m-beaker"
                                            wire:click="mountAction('registerPipeline', { repositoryId: {{ $repo['id'] }} })"
                                        >
                                            Pipeline
                                        </x-filament::button>

                                        <x-filament::button
                                            color="success"
                                            size="sm"
                                            icon="heroicon-m-rocket-launch"
                                            wire:click="mountAction('registerRelease', { repositoryId: {{ $repo['id'] }} })"
                                        >
                                            Release
                                        </x-filament::button>

                                        <x-filament::button
                                            color="danger"
                                            size="sm"
                                            icon="heroicon-m-trash"
                                            wire:click="delete({{ $repo['id'] }}, '{{ $repo['name'] }}')"
                                            wire:confirm="Remover {{ $repo['name'] }} do monitoramento?"
                                        >
                                            Remover
                                        </x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-panels::page>
