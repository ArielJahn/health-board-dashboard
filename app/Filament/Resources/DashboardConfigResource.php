<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardConfigResource\Pages;
use App\Models\DashboardConfig;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DashboardConfigResource extends Resource
{
    protected static ?string $model = DashboardConfig::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Painel';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Configuração';
    protected static ?string $pluralModelLabel = 'Configurações';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Usuário')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            Select::make('default_view')
                ->label('View padrão')
                ->options([
                    'overview' => 'Visão geral',
                    'repos' => 'Repositórios',
                    'incidents' => 'Incidentes',
                ])
                ->default('overview')
                ->required(),

            TextInput::make('refresh_interval')
                ->label('Intervalo de atualização (seg.)')
                ->numeric()
                ->default(60)
                ->minValue(10)
                ->maxValue(3600),

            Select::make('theme')
                ->label('Tema')
                ->options([
                    'light' => 'Claro',
                    'dark' => 'Escuro',
                    'auto' => 'Automático',
                ])
                ->default('auto')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('default_view')
                    ->label('View padrão')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'overview' => 'Visão geral',
                        'repos' => 'Repositórios',
                        'incidents' => 'Incidentes',
                        default => $state,
                    }),

                TextColumn::make('refresh_interval')
                    ->label('Intervalo (seg.)')
                    ->sortable(),

                TextColumn::make('theme')
                    ->label('Tema')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'light' => 'Claro',
                        'dark' => 'Escuro',
                        'auto' => 'Automático',
                        default => $state,
                    }),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboardConfigs::route('/'),
            'create' => Pages\CreateDashboardConfig::route('/create'),
            'edit' => Pages\EditDashboardConfig::route('/{record}/edit'),
        ];
    }
}
