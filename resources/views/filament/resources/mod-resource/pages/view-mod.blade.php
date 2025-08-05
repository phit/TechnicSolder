@php $mirrorUrl = config('solder.mirror_url'); @endphp
<x-filament::page>
    <div x-data="{ tab: 'versions', addVersion: '', addMd5: '', urlPreview: '', expanded: {} }" x-init="urlPreview = ''">
        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
            <button type="button" class="px-4 py-2 -mb-px border-b-2 transition-colors font-semibold"
                :class="tab === 'versions' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400'"
                @click="tab = 'versions'">Versions</button>
            <button type="button" class="px-4 py-2 -mb-px border-b-2 transition-colors font-semibold"
                :class="tab === 'details' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400'"
                @click="tab = 'details'">Details</button>
        </div>
        <div x-show="tab === 'details'">
            <div class="space-y-4">
                <x-filament::section label="Mod Details">
                    <dl class="grid grid-cols-2 gap-4">
                        <dt>Mod Name</dt><dd>{{ $record->pretty_name }}</dd>
                        <dt>Mod Slug</dt><dd>{{ $record->name }}</dd>
                        <dt>Author</dt><dd>{{ $record->author }}</dd>
                        <dt>Description</dt><dd>{{ $record->description }}</dd>
                        <dt>Website</dt><dd><a href="{{ $record->link }}" target="_blank">{{ $record->link }}</a></dd>
                    </dl>
                </x-filament::section>
            </div>
        </div>
        <div x-show="tab === 'versions'">
            <x-filament::section label="Versions">
                <p class="mb-2 text-sm">
                    Solder currently does not support uploading files directly to it. Your repository still needs to exist and follow a strict directory structure. When you add versions the URL will be verified to make sure the file exists before it is added to Solder. The directory structure for this mod is as follows:
                </p>
                <blockquote class="mb-4"><strong>/mods/{{ $record->name }}/{{ $record->name }}-[version].zip</strong></blockquote>
                <table class="table w-full border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700"></th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">Version</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">MD5</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">Download URL</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">Filesize</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ expanded: {} }">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <form wire:submit.prevent="addVersion" class="contents">
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model.defer="addVersionVersion" placeholder="Version" class="fi-input fi-forms-input fi-forms-text-input w-full bg-transparent border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model.defer="addVersionMd5" placeholder="MD5" class="fi-input fi-forms-input fi-forms-text-input w-full bg-transparent border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($addVersionVersion)
                                        {{ $mirrorUrl.'mods/'.$record->name.'/'.$record->name.'-'.$addVersionVersion.'.zip' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-3 py-2">N/A</td>
                                <td class="px-3 py-2">
                                    <x-filament::button type="submit" size="sm">Add Version</x-filament::button>
                                </td>
                            </form>
                        </tr>
                        @foreach ($record->versions->sortByDesc('id') as $ver)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-3 py-2 text-center align-middle">
                                    <button type="button" @click="expanded[{{ $ver->id }}] = !expanded[{{ $ver->id }}]" :aria-expanded="expanded[{{ $ver->id }}] === true" class="focus:outline-none">
                                        <svg :class="expanded[{{ $ver->id }}] ? 'rotate-90 text-primary-600' : 'text-gray-400'" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-2">{{ $ver->version }}</td>
                                <td class="px-3 py-2">
                                    <form x-data="{ md5: '' }" x-on:submit.prevent="$wire.call('rehashVersion', {{ $ver->id }}, md5).then(r => { md5 = r.md5; })" class="flex gap-2 items-center">
                                        <input type="text"
                                            class="fi-input fi-forms-input fi-forms-text-input w-full bg-transparent border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            x-model="md5" placeholder="{{ $ver->md5 }}" />
                                        <x-filament::button type="submit" color="primary" size="sm">Rehash</x-filament::button>
                                    </form>
                                </td>
                                <td class="px-3 py-2">
                                    <a href="{{ $mirrorUrl.'mods/'.$record->name.'/'.$record->name.'-'.$ver->version.'.zip' }}" target="_blank">
                                        {{ $mirrorUrl.'mods/'.$record->name.'/'.$record->name.'-'.$ver->version.'.zip' }}
                                    </a>
                                </td>
                                <td class="px-3 py-2">{{ $ver->humanFilesize() }}</td>
                                <td class="px-3 py-2">
                                    <x-filament::button color="danger" size="sm" x-on:click="$wire.call('deleteVersion', {{ $ver->id }})">Delete</x-filament::button>
                                </td>
                            </tr>
                            <tr x-show="expanded[{{ $ver->id }}]" x-cloak class="bg-gray-50 dark:bg-gray-800 transition-colors">
                                <td></td>
                                <td class="px-3 py-2" colspan="5">
                                    @if ($ver->builds->isEmpty())
                                        <p>Not used in any builds</p>
                                    @else
                                        <p>Builds used in:</p>
                                        <ul>
                                            @foreach ($ver->builds as $build)
                                                <li>
                                                    <a href="{{ url('/modpack/view/'.$build->modpack->id) }}">{{ $build->modpack->name }}</a>
                                                    -
                                                    <a href="{{ url('/modpack/build/'.$build->id) }}">{{ $build->version }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-filament::section>
        </div>
    </div>
</x-filament::page>
