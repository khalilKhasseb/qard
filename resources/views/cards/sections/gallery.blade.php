<div class="gallery-grid">
    {{-- New format: items array --}}
    @foreach($content['items'] ?? [] as $image)
        <div class="gallery-item">
            <img src="{{ $image['url'] ?? $image }}" alt="{{ $image['caption'] ?? 'Gallery image' }}" loading="lazy">
            @if(!empty($image['caption']))
                <div class="gallery-caption">{{ $image['caption'] }}</div>
            @endif
        </div>
    @endforeach
    
    {{-- Legacy format: images array --}}
    @foreach($content['images'] ?? [] as $image)
        <div class="gallery-item">
            <img src="{{ $image['url'] ?? $image }}" alt="{{ $image['caption'] ?? 'Gallery image' }}" loading="lazy">
            @if(!empty($image['caption']))
                <div class="gallery-caption">{{ $image['caption'] }}</div>
            @endif
        </div>
    @endforeach
</div>
