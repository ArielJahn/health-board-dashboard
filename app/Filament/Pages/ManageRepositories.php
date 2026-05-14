<?php

namespace App\Filament\Pages;

use App\Services\ApiClient;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageRepositories extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon  = 'heroicon-o-server-stack';
    protected static ?string $navigationLabel = 'Repositórios';
    protected static ?string $title           = 'Repositórios Monitorados';
    protected static ?string $navigationGroup = 'Monitoramento';
    protected static ?int $navigationSort     = 1;
    protected static string $view             = 'filament.pages.manage-repositories';

    public array $repositories = [];
    public ?array $formData    = [];

    public function mount(): void
    {
        $this->loadRepositories();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome curto')
                    ->placeholder('api-gateway')
                    ->required()
                    ->maxLength(255),

                TextInput::make('full_name')
                    ->label('Owner/repo')
                    ->placeholder('ArielJahn/api-gateway')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Deve bater exatamente com o nome no GitHub (case-sensitive)'),

                TextInput::make('github_url')
                    ->label('URL do repositório')
                    ->placeholder('https://github.com/ArielJahn/api-gateway')
                    ->url()
                    ->required()
                    ->maxLength(500),

                TextInput::make('access_token')
                    ->label('GitHub Personal Access Token')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->helperText('Opcional. Necessário para repos privados.'),
            ])
            ->statePath('formData');
    }

    // ── Ações modais por repositório ──────────────────────────────────────────

    public function registerPipelineAction(): Action
    {
        return Action::make('registerPipeline')
            ->label('Registrar Pipeline')
            ->icon('heroicon-m-beaker')
            ->color('info')
            ->modalHeading('Registrar execução de pipeline')
            ->form([
                TextInput::make('workflow_name')
                    ->label('Nome do workflow')
                    ->placeholder('CI / Deploy / Test')
                    ->required()
                    ->maxLength(255),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'success'     => 'Sucesso',
                        'failure'     => 'Falha',
                        'cancelled'   => 'Cancelado',
                        'in_progress' => 'Em andamento',
                    ])
                    ->required(),

                TextInput::make('branch')
                    ->label('Branch')
                    ->default('main')
                    ->required()
                    ->maxLength(255),

                TextInput::make('duration')
                    ->label('Duração (segundos)')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('120'),

                DateTimePicker::make('run_at')
                    ->label('Executado em')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (array $data, array $arguments): void {
                $data['repository_id'] = $arguments['repositoryId'];

                $result = app(ApiClient::class)->createPipeline($data);

                if ($result['success']) {
                    Notification::make()
                        ->title('Pipeline registrado com sucesso')
                        ->success()
                        ->send();
                } else {
                    $message = collect($result['errors'])->flatten()->first()
                        ?? 'Erro ao registrar pipeline.';

                    Notification::make()
                        ->title('Erro ao registrar pipeline')
                        ->body($message)
                        ->danger()
                        ->send();
                }
            });
    }

    public function registerReleaseAction(): Action
    {
        return Action::make('registerRelease')
            ->label('Registrar Release')
            ->icon('heroicon-m-rocket-launch')
            ->color('success')
            ->modalHeading('Registrar deploy / release')
            ->form([
                TextInput::make('version')
                    ->label('Versão')
                    ->placeholder('v1.2.0')
                    ->required()
                    ->maxLength(50),

                Select::make('environment')
                    ->label('Ambiente')
                    ->options([
                        'dev'        => 'Desenvolvimento',
                        'staging'    => 'Staging',
                        'production' => 'Produção',
                    ])
                    ->required(),

                DateTimePicker::make('deployed_at')
                    ->label('Implantado em')
                    ->default(now())
                    ->required(),

                Textarea::make('changelog')
                    ->label('Changelog')
                    ->placeholder("- Corrige bug de autenticação\n- Melhora performance do dashboard")
                    ->rows(3),
            ])
            ->action(function (array $data, array $arguments): void {
                $data['repository_id'] = $arguments['repositoryId'];

                $result = app(ApiClient::class)->createRelease($data);

                if ($result['success']) {
                    Notification::make()
                        ->title('Release registrada com sucesso')
                        ->success()
                        ->send();
                } else {
                    $message = collect($result['errors'])->flatten()->first()
                        ?? 'Erro ao registrar release.';

                    Notification::make()
                        ->title('Erro ao registrar release')
                        ->body($message)
                        ->danger()
                        ->send();
                }
            });
    }

    // ── Submissão do formulário de adição ─────────────────────────────────────

    public function create(): void
    {
        $data = $this->form->getState();

        $result = app(ApiClient::class)->createRepository($data);

        if ($result['success']) {
            Notification::make()
                ->title('Repositório adicionado')
                ->body("{$data['full_name']} está sendo monitorado.")
                ->success()
                ->send();

            $this->form->fill();
            $this->loadRepositories();
        } else {
            $message = $result['errors']
                ? collect($result['errors'])->flatten()->first()
                : 'Erro ao adicionar repositório. Verifique se o owner/repo já está cadastrado.';

            Notification::make()
                ->title('Erro ao adicionar')
                ->body($message)
                ->danger()
                ->send();
        }
    }

    public function delete(int $id, string $name): void
    {
        $deleted = app(ApiClient::class)->deleteRepository($id);

        if ($deleted) {
            Notification::make()
                ->title('Repositório removido')
                ->body("{$name} foi removido do monitoramento.")
                ->success()
                ->send();

            $this->loadRepositories();
        } else {
            Notification::make()
                ->title('Erro ao remover')
                ->body('Não foi possível remover o repositório.')
                ->danger()
                ->send();
        }
    }

    private function loadRepositories(): void
    {
        $this->repositories = rescue(fn () => app(ApiClient::class)->repositories(), []);
    }
}
