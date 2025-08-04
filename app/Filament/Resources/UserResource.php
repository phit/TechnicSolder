<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Data')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->minLength(3)
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->label('Password')
                            ->helperText('If you would like to change this accounts password you may include new passwords below. This is not required to edit an account.'),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Permissions')
                    ->description('Please select the level of access this user will be given. The "Super User" permission grants full access. Mod and Modpack user permissions are displayed in their corresponding sections. General Modpack Access permissions are required before granting access to a specific modpack.')
                    ->relationship('permission')
                    ->schema([
                        Forms\Components\Toggle::make('solder_full')
                            ->label('Super User')
                            ->default(false)
                            ->helperText('Full Solder Access (blanket permission).'),
                        Forms\Components\Toggle::make('solder_users')
                            ->label('Manage Users')
                            ->default(false),
                        Forms\Components\Toggle::make('solder_keys')
                            ->label('Manage Keys')
                            ->default(false),
                        Forms\Components\Toggle::make('solder_clients')
                            ->label('Manage Clients')
                            ->default(false),
                        Forms\Components\Toggle::make('mods_create')
                            ->label('Create Mods')
                            ->default(false),
                        Forms\Components\Toggle::make('mods_manage')
                            ->label('Manage Mods')
                            ->default(false),
                        Forms\Components\Toggle::make('mods_delete')
                            ->label('Delete Mods')
                            ->default(false),
                        Forms\Components\Section::make('General Modpack Access')
                            ->schema([
                                Forms\Components\Toggle::make('modpacks_create')
                                    ->label('Create Modpacks')
                                    ->default(false),
                                Forms\Components\Toggle::make('modpacks_manage')
                                    ->label('Manage Modpacks')
                                    ->default(false),
                                Forms\Components\Toggle::make('modpacks_delete')
                                    ->label('Delete Modpacks')
                                    ->default(false),
                                Forms\Components\Select::make('modpacks')
                                    ->label('Specific Modpacks')
                                    ->hiddenLabel()
                                    ->multiple()
                                    ->options(\App\Models\Modpack::all()->pluck('name', 'id')->toArray()),
                            ])
                            ->description('General Modpack Access permissions are required before granting access to a specific modpack. Users without these permission will not be able to perform stated actions even if the specific modpack is selected.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('This is the user management area. Here you can manage users and their permissions for Solder.')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID #')->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('updated_by_user.username')
                    ->label('Updated by (User)')
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('updated_by_ip')
                    ->label('Updated by (IP)')
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime('r')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getMiddleware(): array
    {
        return [
            'solder_users',
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->permission->solder_full || $user->permission->solder_users);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->permission->solder_full || $user->permission->solder_users);
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && (
            $user->permission->solder_full ||
            $user->permission->solder_users ||
            $user->id === $record->id
        );
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        // Only allow delete if not deleting the only remaining super user
        if ($record->permission->solder_full) {
            $numOfOtherSuperUsers = \App\Models\UserPermission::where('solder_full', true)
                ->where('user_id', '!=', $record->id)
                ->count();
            if ($numOfOtherSuperUsers <= 0) {
                return false;
            }
        }
        return $user && ($user->permission->solder_full || $user->permission->solder_users);
    }
}
