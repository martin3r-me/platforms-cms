<div>
    <x-page.title>{{ $content->title }}</x-page.title>
    @if($content->excerpt)
        <div class="text-sm text-gray-500 mb-4">{{ $content->excerpt }}</div>
    @endif
    <div class="prose max-w-none">
        {!! nl2br(e($content->body)) !!}
    </div>
</div>


