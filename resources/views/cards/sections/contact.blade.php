@if(!empty($content['email']))
    <div class="contact-item">
        <span class="contact-icon">📧</span>
        <a href="mailto:{{ $content['email'] }}" class="contact-link">{{ $content['email'] }}</a>
    </div>
@endif

@if(!empty($content['phone']))
    <div class="contact-item">
        <span class="contact-icon">📞</span>
        <a href="tel:{{ $content['phone'] }}" class="contact-link">{{ $content['phone'] }}</a>
    </div>
@endif

@if(!empty($content['website']))
    <div class="contact-item">
        <span class="contact-icon">🌐</span>
        <a href="{{ $content['website'] }}" target="_blank" class="contact-link">{{ $content['website'] }}</a>
    </div>
@endif

@if(!empty($content['address']))
    <div class="contact-item">
        <span class="contact-icon">📍</span>
        <span class="contact-link" style="text-decoration: none; color: #4a5568;">{{ $content['address'] }}</span>
    </div>
@endif
