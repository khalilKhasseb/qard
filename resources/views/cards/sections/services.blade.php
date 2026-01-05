<ul class="item-list">
    @foreach($content['items'] ?? [] as $item)
        <li class="item">
            <div class="item-header">
                <span class="item-name">{{ $item['name'] ?? $item }}</span>
            </div>
            @if(isset($item['description']))
                <p class="item-description">{{ $item['description'] }}</p>
            @endif
        </li>
    @endforeach
</ul>
