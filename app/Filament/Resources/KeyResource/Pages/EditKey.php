<?php

namespace App\Filament\Resources\KeyResource\Pages;

use App\Filament\Resources\KeyResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditKey extends EditRecord
{
    protected static string $resource = KeyResource::class;

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $record->update($data);
        Cache::forget('keys');
        Notification::make()->title('API key updated!')->success()->send();

        return $record;
    }
}
