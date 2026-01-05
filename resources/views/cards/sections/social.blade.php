<div class="social-links">
    {{-- New format: individual fields --}}
    @if(!empty($content['github']))
        <a href="{{ $content['github'] }}" target="_blank" class="social-link">💻 GitHub</a>
    @endif
    @if(!empty($content['linkedin']))
        <a href="{{ $content['linkedin'] }}" target="_blank" class="social-link">💼 LinkedIn</a>
    @endif
    @if(!empty($content['twitter']))
        <a href="{{ $content['twitter'] }}" target="_blank" class="social-link">🐦 Twitter</a>
    @endif
    @if(!empty($content['instagram']))
        <a href="{{ $content['instagram'] }}" target="_blank" class="social-link">📷 Instagram</a>
    @endif
    @if(!empty($content['facebook']))
        <a href="{{ $content['facebook'] }}" target="_blank" class="social-link">📘 Facebook</a>
    @endif
    
    {{-- Legacy format: array of links --}}
    @foreach($content['links'] ?? [] as $link)
        <a href="{{ $link['url'] }}" target="_blank" class="social-link">
            @switch($link['platform'] ?? '')
                @case('facebook') 📘 Facebook @break
                @case('twitter') 🐦 Twitter @break
                @case('instagram') 📷 Instagram @break
                @case('linkedin') 💼 LinkedIn @break
                @case('youtube') ▶️ YouTube @break
                @case('github') 💻 GitHub @break
                @default 🔗 {{ $link['label'] ?? $link['platform'] ?? 'Link' }}
            @endswitch
        </a>
    @endforeach
</div>
