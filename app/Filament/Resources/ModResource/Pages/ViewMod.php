<?php

namespace App\Filament\Resources\ModResource\Pages;

use App\Filament\Resources\ModResource;
use App\Models\Mod;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Str;

class ViewMod extends ViewRecord
{
    protected static string $resource = ModResource::class;

    protected static string $view = 'filament.resources.mod-resource.pages.view-mod';

    public $addVersionVersion = '';

    public $addVersionMd5 = '';

    public function mount($record): void
    {
        $this->record = Mod::with(['versions.builds.modpack'])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return $this->record->pretty_name ?: $this->record->name;
    }

    /**
     * Get MD5 and filesize for a mod version, matching legacy controller logic.
     */
    protected function getModFileHashAndSize($mod, $version)
    {
        $repoLocation = config('solder.repo_location');
        $filePath = $repoLocation.'mods/'.$mod->name.'/'.$mod->name.'-'.$version.'.zip';
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            try {
                $result = \App\Libraries\UrlUtils::get_remote_md5($filePath);
                if ($result['success']) {
                    return [
                        'success' => true,
                        'md5' => $result['md5'],
                        'filesize' => $result['filesize'] ?? null,
                        'message' => null,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $result['message'] ?? 'Unknown error',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error attempting to remote MD5 file: '.$filePath.' - '.$e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        } elseif (file_exists($filePath)) {
            try {
                $md5 = md5_file($filePath);
                $filesize = filesize($filePath);
                return [
                    'success' => true,
                    'md5' => $md5,
                    'filesize' => $filesize,
                    'message' => null,
                ];
            } catch (\Exception $e) {
                Log::error('Error attempting to md5 the file: '.$filePath.' - '.$e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        } else {
            $error = $filePath.' does not exist';
            Log::error($error);
            return [
                'success' => false,
                'message' => $error,
            ];
        }
    }

    public function addVersion()
    {
        $mod = $this->record;
        $version = $this->addVersionVersion;
        $md5 = $this->addVersionMd5;
        if (! $version) {
            Notification::make()->title('Version is required')->danger()->send();
            return;
        }
        $exists = $mod->versions()->where('version', $version)->exists();
        if ($exists) {
            Notification::make()->title('Version already exists')->danger()->send();
            return;
        }
        $hashResult = $this->getModFileHashAndSize($mod, $version);
        if (! $hashResult['success']) {
            Notification::make()->title('File not found')->danger()->body($hashResult['message'])->send();
            return;
        }
        $fileMd5 = $hashResult['md5'];
        $fileFilesize = $hashResult['filesize'];
        $status = 'success';
        $reason = null;
        $finalMd5 = $md5 ?: $fileMd5;
        if ($md5 && $md5 !== $fileMd5) {
            $status = 'warning';
            $reason = 'MD5 provided does not match file MD5: '.$fileMd5;
        }
        $ver = $mod->versions()->create([
            'version' => $version,
            'md5' => $finalMd5,
            'filesize' => $fileFilesize,
        ]);
        $this->addVersionVersion = '';
        $this->addVersionMd5 = '';
        $this->record->refresh();
        if ($status === 'warning') {
            Notification::make()
                ->title('Version '.$ver->version.' added with warning')
                ->body($reason)
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('Version '.$ver->version.' added')
                ->success()
                ->send();
        }
    }

    public function rehashVersion($versionId, $md5 = null)
    {
        $ver = $this->record->versions()->find($versionId);
        if (! $ver) {
            Notification::make()->title('Version not found')->danger()->send();
            return;
        }
        $hashResult = $this->getModFileHashAndSize($this->record, $ver->version);
        if (! $hashResult['success']) {
            Notification::make()->title('MD5 hashing failed')->danger()->body($hashResult['message'])->send();
            return;
        }
        $fileMd5 = $hashResult['md5'];
        $fileFilesize = $hashResult['filesize'];
        $status = 'success';
        $reason = null;
        $finalMd5 = $md5 ?: $fileMd5;
        if ($md5 && $md5 !== $fileMd5) {
            $status = 'warning';
            $reason = 'MD5 provided does not match file MD5: '.$fileMd5;
        }
        $ver->md5 = $finalMd5;
        $ver->filesize = $fileFilesize;
        $ver->save();
        $this->record->refresh();
        if ($status === 'warning') {
            Notification::make()
                ->title('MD5 hashing complete with warning')
                ->body($reason)
                ->warning()
                ->send();
        } else {
            Notification::make()->title('MD5 hashing complete')->success()->send();
        }
        return ['md5' => $ver->md5, 'filesize' => $ver->filesize];
    }

    public function deleteVersion($versionId)
    {
        $ver = $this->record->versions()->find($versionId);
        if (! $ver) {
            Notification::make()->title('Version not found')->danger()->send();

            return;
        }
        $ver->delete();
        $this->record->refresh();
        Notification::make()->title('Mod version deleted')->success()->send();
    }
}
