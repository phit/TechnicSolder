<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Data')
                    ->description('Register your launcher client UUID to Solder so that private builds will show up to you in the launcher. After a client is added, link it to the modpacks you want it to access in Solder.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('A descriptive name for this client.'),
                        Forms\Components\TextInput::make('uuid')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The unique UUID for this client. This is required for private build access.'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('This is the client management area. Here you can register your launcher client UUID to Solder so that private builds will show up to you in the launcher. After a client is added to this list, they need to be linked to the modpacks you want them to have access to in Solder.')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('uuid')->label('Client UUID')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
        ];
    }

    public static function getMiddleware(): array
    {
        return [
            'solder_clients',
        ];
    }

    public static function afterCreate($record): void
    {
        \Illuminate\Support\Facades\Cache::forget('clients');
    }

    public static function afterDelete($record): void
    {
        $record->modpacks()->sync([]);
        \Illuminate\Support\Facades\Cache::forget('clients');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'uuid'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name;
    }
}
