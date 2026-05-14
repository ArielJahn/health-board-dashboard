<?php

namespace App\Filament\Pages;

use App\Services\ApiClient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageIncidents extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Incidentes';
    protected static ?string $title           = 'Gerenciar Incidentes';
    protected static ?string $navigationGroup = 'Monitoramento';
    protected static ?int $navigationSort     = 2;
    protected static string $view             = 'filament.pages.manage-incidents';

    public array $incidents = [];
    public ?array $formData = [];

    public function mount(): void
    {
        $this->loadIncidents();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $repositories = rescue(fn () => app(ApiClient::class)->repositories(), []);
        $repoOptions  = collect($repositories)->pluck('name', 'id')->toArray();

        return $form
            ->schema([
                Select::make('repository_id')
                    ->label('Repositório')
                    ->options($repoOptions)
                    ->required()
                    ->searchable(),

                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(2)
                    ->columnSpan(2),

                Select::make('severity')
                    ->label('Severidade')
                    ->options([
                        'low'      => 'Baixa',
                        'medium'   => 'Média',
                        'high'     => 'Alta',
                        'critical' => 'Crítica',
                    ])
                    ->required(),

                Select::make('status')
                    ->label('Status inicial')
                    ->options([
                        'open'          => 'Aberto',
                        'investigating' => 'Investigando',
                    ])
                    ->default('open'),

                DateTimePicker::make('opened_at')
                    ->label('Aberto em')
                    ->default(now())
                    ->required(),
            ])
            ->columns(2)
            ->statePath('formData');
    }

    public function create(): void
    {
        $data   = $this->form->getState();
        $result = app(ApiClient::class)->createIncident($data);

        if ($result['success']) {
            Notification::make()->title('Incidente registrado')->success()->send();
            $this->form->fill();
            $this->loadIncidents();
        } else {
            $message = collect($result['errors'])->flatten()->first()
                ?? 'Erro ao registrar incidente.';

            Notification::make()->title('Erro ao registrar')->body($message)->danger()->send();
        }
    }

    public function investigate(int $id): void
    {
        $result = app(ApiClient::class)->updateIncident($id, ['status' => 'investigating']);

        if ($result['success'] ?? false) {
            Notification::make()->title('Status atualizado: investigando')->warning()->send();
        } else {
            Notification::make()->title('Erro ao atualizar')->danger()->send();
        }

        $this->loadIncidents();
    }

    public function resolve(int $id, string $title): void
    {
        $result = app(ApiClient::class)->updateIncident($id, ['status' => 'resolved']);

        if ($result['success'] ?? false) {
            Notification::make()->title("\"{$title}\" resolvido")->success()->send();
        } else {
            Notification::make()->title('Erro ao resolver')->danger()->send();
        }

        $this->loadIncidents();
    }

    private function loadIncidents(): void
    {
        $this->incidents = rescue(fn () => app(ApiClient::class)->incidents(), []);
    }
}
