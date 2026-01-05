@foreach($content['items'] ?? [] as $testimonial)
    <div class="testimonial">
        <p class="testimonial-quote">"{{ $testimonial['text'] ?? $testimonial['quote'] ?? $testimonial }}"</p>
        @if(isset($testimonial['author']))
            <div class="testimonial-author">
                {{ $testimonial['author'] }}
                @if(isset($testimonial['title']) || isset($testimonial['company']))
                    <span class="testimonial-company"> — {{ $testimonial['title'] ?? $testimonial['company'] }}</span>
                @endif
            </div>
        @endif
    </div>
@endforeach
