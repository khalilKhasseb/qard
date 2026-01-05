@if(!empty($content['youtube_id']))
    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; background: #000; margin-bottom: 1rem;">
        <iframe 
            src="https://www.youtube.com/embed/{{ $content['youtube_id'] }}" 
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen
        ></iframe>
    </div>
@elseif(!empty($content['url']))
    <video controls style="width: 100%; border-radius: 12px; background: #000; margin-bottom: 1rem;">
        <source src="{{ $content['url'] }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
@endif

@if(!empty($content['caption']))
    <div class="gallery-caption" style="position: static; background: #f7fafc; color: #4a5568; padding: 0.75rem; margin-top: 0;">
        {{ $content['caption'] }}
    </div>
@endif
