<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeyResource\Pages;
use App\Models\Key;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KeyResource extends Resource
{
    protected static ?string $model = Key::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'API Keys';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('API Key Data')
                    ->description('API keys grant access to Solder. Make sure to keep them secure. You can add, view, and delete API keys here.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('api_key')
                            ->label('API Key')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('This is the list of API keys that have access to Solder.')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('api_key')->label('API Key')->copyable(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(fn ($record) => 'Delete API Key ('.$record->name.')')
                    ->modalDescription('This will immediately remove access to Solder using this API Key. Make sure to unlink any packs using this key before doing this.')
                    ->before(function ($record) {
                        \Illuminate\Support\Facades\Cache::forget('keys');
                    })
                    ->successNotificationTitle('API Key deleted!'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            \Illuminate\Support\Facades\Cache::forget('keys');
                        })
                        ->successNotificationTitle('API Keys deleted!'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeys::route('/'),
            'create' => Pages\CreateKey::route('/create'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'api_key'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name;
    }
}
