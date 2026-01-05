<ul class="item-list">
    @foreach($content['items'] ?? [] as $item)
        <li class="item">
            <div class="item-header">
                <span class="item-name">{{ $item['name'] ?? $item }}</span>
                @if(isset($item['price']))
                    <span class="item-price">{{ $item['price'] }}</span>
                @endif
            </div>
            @if(isset($item['description']))
                <p class="item-description">{{ $item['description'] }}</p>
            @endif
        </li>
    @endforeach
</ul>
