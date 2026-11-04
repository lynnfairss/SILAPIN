<div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-5 py-3.5 text-xs text-slate-500 sm:flex-row">
    <div>
        Menampilkan
        <span class="font-semibold text-slate-700">{{ $paginator->firstItem() ?? 0 }}</span>
        &ndash;
        <span class="font-semibold text-slate-700">{{ $paginator->lastItem() ?? 0 }}</span>
        dari
        <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
        data
    </div>

    @if ($paginator->hasPages())
        <div class="admin-pagination flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="is-disabled" aria-disabled="true"><i class="fas fa-chevron-left text-[10px]"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="is-dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="is-disabled" aria-disabled="true"><i class="fas fa-chevron-right text-[10px]"></i></span>
            @endif
        </div>
    @endif
</div>
