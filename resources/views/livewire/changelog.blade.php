<x-filament-widgets::widget>
    <div>
        <h3>Changelog</h3>
        @if (array_key_exists('error', $changelog))
            <div class="alert alert-warning">{{ $changelog['error'] }}</div>
        @else
            <ul>
                @foreach ($changelog as $change)
                    <li>
                        <code><a href="{{ url($change['html_url']) }}" rel="noopener noreferrer">{{ substr($change['sha'], 0, 7) }}</a></code>
                        <span style="margin-left:5px;margin-right:5px;">
                            <i class="fa fa-angle-double-left fa-1"></i>
                        </span> {{ explode("\n", $change['commit']['message'], 2)[0] }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-widgets::widget>
