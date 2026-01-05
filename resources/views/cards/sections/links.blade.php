<div class="social-links">
    @foreach($content['items'] ?? [] as $link)
        <a href="{{ $link['url'] }}" target="_blank" class="social-link" style="background: #f7fafc; color: #1a202c; border: 1px solid #e2e8f0;">
            🔗 {{ $link['label'] ?? $link['url'] }}
        </a>
    @endforeach
</div>
