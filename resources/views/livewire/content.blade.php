<div>
    <h1 class="text-2xl font-bold mb-4">{{ $content->title }}</h1>
    @if($content->excerpt)
        <div class="text-sm text-gray-500 mb-4">{{ $content->excerpt }}</div>
    @endif
    <div class="prose max-w-none">
        {!! nl2br(e($content->body)) !!}
    </div>
</div>


