<x-filament-panels::page>
    <x-filament-widgets::widgets :widgets="[
        App\Livewire\RecentModpacks::class,
        App\Livewire\RecentModVersions::class,
        App\Livewire\Changelog::class,
    ]" />
</x-filament-panels::page>
