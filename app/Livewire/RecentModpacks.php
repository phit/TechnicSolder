<?php

namespace App\Livewire;

use App\Models\Build;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecentModpacks extends BaseWidget
{
    protected function getStats(): array
    {
        $recentBuilds = Build::with('modpack')
            ->withCount('modversions')
            ->where('is_published', '=', '1')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return $recentBuilds->map(function ($build) {
            return Stat::make(
                $build->modpack->name . ' #' . $build->version,
                $build->modversions_count . ' mods'
            )
            ->description('MC: ' . $build->minecraft . ' | Updated: ' . $build->updated_at->format('Y-m-d'))
            ->url(url('/modpack/build/' . $build->id));
        })->toArray();
    }
}
