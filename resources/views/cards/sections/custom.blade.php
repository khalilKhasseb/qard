<div style="color: #4a5568; line-height: 1.7;">
    @if(!empty($content['html']))
        {!! $content['html'] !!}
    @elseif(!empty($content['text']))
        {!! nl2br(e($content['text'])) !!}
    @endif
</div>
