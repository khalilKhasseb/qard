<div class="hours-grid">
    {{-- New format: individual day fields --}}
    @if(!empty($content['monday']) || !empty($content['tuesday']) || !empty($content['wednesday']) || !empty($content['thursday']) || !empty($content['friday']) || !empty($content['saturday']) || !empty($content['sunday']))
        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
            @if(!empty($content[$day]))
                <div class="hours-row">
                    <span class="hours-day">{{ $day }}</span>
                    <span class="hours-time">{{ $content[$day] }}</span>
                </div>
            @endif
        @endforeach
    @elseif(!empty($content['schedule']))
        {{-- Legacy format: schedule object --}}
        @foreach($content['schedule'] as $day => $hours)
            <div class="hours-row">
                <span class="hours-day">{{ ucfirst($day) }}</span>
                <span class="hours-time">{{ $hours['open'] ?? 'Closed' }}{{ isset($hours['close']) && $hours['close'] ? ' - ' . $hours['close'] : '' }}</span>
            </div>
        @endforeach
    @endif
</div>

@if(!empty($content['note']))
    <p style="margin-top: 1rem; font-size: 0.875rem; opacity: 0.8;">{{ $content['note'] }}</p>
@endif
