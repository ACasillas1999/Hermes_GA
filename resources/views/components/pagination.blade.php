@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Paginacion">
        <div class="muted">
            Mostrando {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }}
        </div>
        <div class="row-inline">
            @if ($paginator->onFirstPage())
                <span class="button button-secondary is-disabled" aria-disabled="true">Anterior</span>
            @else
                <a class="button button-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="muted" style="padding: 0 4px;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="button button-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="button button-secondary" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="button button-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="button button-secondary is-disabled" aria-disabled="true">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
