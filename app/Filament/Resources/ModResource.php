<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModResource\Pages;
use App\Models\Mod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ModResource extends Resource
{
    protected static ?string $model = Mod::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mod Data')
                    ->description("Because Solder doesn't do any file handling yet you will need to manually manage your set of mods in your repository. The mod repository structure is very strict and must match your Solder data exactly. An example of your mod directory structure will be listed below:\n\n/mods/[slug]/\n/mods/[slug]/[slug]-[version].zip\n\nThe mod slug automatically updates based on the mod name. You can change the slug to whatever you want after you set the name. If you modify the slug, it will no longer update automatically. If you wish to restore that behavior, then simply empty the slug field.")
                    ->schema([
                        Forms\Components\TextInput::make('pretty_name')
                            ->label('Mod Name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Only update slug if it is empty or matches the previous slugified name
                                $currentSlug = $get('name');
                                $slugified = Str::slug($state);
                                if (empty($currentSlug) || $currentSlug === Str::slug($get('previous_pretty_name', ''))) {
                                    $set('name', $slugified);
                                }
                                $set('previous_pretty_name', $state);
                            }),
                        Forms\Components\TextInput::make('name')
                            ->label('Mod Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('The mod slug automatically updates based on the mod name. If you modify the slug, it will no longer update automatically. To restore auto-update, clear this field.')
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // If the slug is cleared, resume auto-update
                                if (empty($state)) {
                                    $set('name', Str::slug($get('pretty_name')));
                                }
                            }),
                        Forms\Components\TextInput::make('author')
                            ->label('Author'),
                        Forms\Components\Textarea::make('description')
                            ->label('Description'),
                        Forms\Components\TextInput::make('link')
                            ->label('Mod Website')
                            ->url()
                            ->nullable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Mod Library')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('pretty_name')->label('Mod Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('author')->label('Author')->sortable()->searchable()->default('N/A'),
                Tables\Columns\TextColumn::make('link')->label('Website')->url(fn ($record) => $record->link)->default('N/A'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.default.resources.mods.view', ['record' => $record->getKey()])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(fn ($record) => 'Delete Request for '.$record->name)
                    ->modalDescription('Deleting a mod can have serious repercussions. All associated modpacks and builds using this mod will have the mod forcefully stripped from their build history. This includes every version of the mod in every build it existed in. This tool should mainly be used to delete a mod you created by mistake or are entirely sure is no longer needed. For your convenience we have generated a list of all builds currently using this mod for every version of the mod. Please carefully check the versions list before removing the mod!\n\nDeleting a mod is irreversible!')
                    ->modalContent(function ($record) {
                        $html = '<ul>';
                        foreach ($record->versions as $ver) {
                            $html .= '<li>'.e($ver->version).'</li>';
                            if ($ver->builds->count() >= 1) {
                                $html .= '<ul>';
                                foreach ($ver->builds as $build) {
                                    $html .= '<li>'.e($build->modpack->name).' - '.e($build->version).'</li>';
                                }
                                $html .= '</ul>';
                            }
                        }
                        $html .= '</ul>';

                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->before(function ($record) {
                        // Remove all versions and builds, clear cache
                        foreach ($record->versions as $ver) {
                            $ver->builds()->sync([]);
                            $ver->delete();
                        }
                        \Illuminate\Support\Facades\Cache::forget('mod:'.$record->name);
                    })
                    ->successNotificationTitle('Mod deleted!'),
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
            'index' => Pages\ListMods::route('/'),
            'create' => Pages\CreateMod::route('/create'),
            'edit' => Pages\EditMod::route('/{record}/edit'),
            'view' => Pages\ViewMod::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'pretty_name', 'author'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->pretty_name;
    }
}
