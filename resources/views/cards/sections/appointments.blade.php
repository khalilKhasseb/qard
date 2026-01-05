@if(!empty($content['calendly_url']) || !empty($content['booking_url']))
    <a href="{{ $content['calendly_url'] ?? $content['booking_url'] }}" target="_blank" class="appointment-btn">
        📅 Book an Appointment
    </a>
@endif

@if(!empty($content['instructions']) || !empty($content['description']))
    <div class="appointment-instructions">
        {{ $content['instructions'] ?? $content['description'] }}
    </div>
@endif
