<?php

namespace App\Filament\Resources\ModResource\Pages;

use App\Filament\Resources\ModResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMod extends CreateRecord
{
    protected static string $resource = ModResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Slugify the name
        $data['name'] = Str::slug($data['name']);

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::create($data);
    }
}
