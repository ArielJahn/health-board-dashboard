<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoardNotificationResource\Pages;
use App\Models\BoardNotification;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoardNotificationResource extends Resource
{
    protected static ?string $model = BoardNotification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Painel';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Notificação';
    protected static ?string $pluralModelLabel = 'Notificações';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Usuário')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),

            Textarea::make('message')
                ->label('Mensagem')
                ->required()
                ->rows(3),

            DateTimePicker::make('read_at')
                ->label('Lida em')
                ->nullable(),

            DateTimePicker::make('created_at')
                ->label('Criada em')
                ->default(now()),
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

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(40),

                IconColumn::make('read_at')
                    ->label('Lida')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->getStateUsing(fn ($record) => filled($record->read_at)),

                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('unread')
                    ->label('Não lidas')
                    ->query(fn (Builder $query) => $query->whereNull('read_at')),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoardNotifications::route('/'),
            'create' => Pages\CreateBoardNotification::route('/create'),
            'edit' => Pages\EditBoardNotification::route('/{record}/edit'),
        ];
    }
}
