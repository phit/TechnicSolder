<?php

namespace App\Livewire;

use App\Libraries\UpdateUtils;
use Filament\Widgets\Widget;

class Changelog extends Widget
{
    protected static string $view = 'livewire.changelog';

    public function getViewData(): array
    {
        $rawChangeLog = UpdateUtils::getLatestChangeLog();
        $changelog = array_key_exists('error', $rawChangeLog) ? $rawChangeLog : array_slice($rawChangeLog, 0, 10);
        return [
            'changelog' => $changelog,
        ];
    }
}
