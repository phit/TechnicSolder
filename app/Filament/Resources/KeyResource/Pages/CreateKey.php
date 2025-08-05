<?php

namespace App\Filament\Resources\KeyResource\Pages;

use App\Filament\Resources\KeyResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateKey extends CreateRecord
{
    protected static string $resource = KeyResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $record = static::getModel()::create($data);
        Cache::forget('keys');
        Notification::make()->title('API key added!')->success()->send();

        return $record;
    }
}
