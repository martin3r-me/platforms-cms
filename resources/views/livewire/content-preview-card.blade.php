<div class="bg-white rounded-md border p-3 hover:shadow-sm transition" wire:navigate.hover>
    <a href="{{ route('cms.contents.show', $content->id) }}" class="block">
        <div class="text-sm font-medium truncate">{{ $content->title }}</div>
        @if($content->excerpt)
            <div class="text-xs text-gray-500 line-clamp-2">{{ $content->excerpt }}</div>
        @endif
        <div class="mt-2 text-[10px] uppercase text-gray-400">Status: {{ $content->status }}</div>
    </a>
</div>

