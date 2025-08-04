<?php

namespace App\Livewire;

use App\Models\Modversion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecentModVersions extends BaseWidget
{
    protected function getStats(): array
    {
        $recentModVersions = Modversion::with('mod')
            ->whereNotNull('md5')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return $recentModVersions->map(function ($modversion) {
            return Stat::make(
                ($modversion->mod->pretty_name ?: $modversion->mod->name) . ' v' . $modversion->version,
                'By: ' . ($modversion->mod->author ?: 'N/A')
            )
            ->description('Created: ' . $modversion->created_at->format('Y-m-d'))
            ->url(url('/mod/view/' . $modversion->mod->id . '#versions'));
        })->toArray();
    }
}
