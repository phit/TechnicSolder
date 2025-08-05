<?php

namespace App\Filament\Resources\ModResource\Pages;

use App\Filament\Resources\ModResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EditMod extends EditRecord
{
    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Slugify the name
        $data['name'] = Str::slug($data['name']);

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $record->update($data);
        Cache::forget('mod:'.$record->name);
        Notification::make()->title('Mod successfully edited.')->success()->send();

        return $record;
    }
}
