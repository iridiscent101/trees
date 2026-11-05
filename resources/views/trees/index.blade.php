<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse verified inventory trees and provisional survey records across Nueva Vizcaya State University.">
    <title>Tree Directory | NVSU Tree Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tree-directory.css') }}?v={{ filemtime(public_path('css/tree-directory.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-header.css') }}?v={{ filemtime(public_path('css/public-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-polish.css') }}">
</head>
<body>
    <x-public-header active="trees" />

    <main>
        <section class="directory-hero">
            <div class="hero-image-placeholder" role="img" aria-label="Campus trees banner placeholder"></div>
            <div class="hero-overlay"></div>
            <div><h1>Tree Directory</h1><p>Browse verified inventory trees and provisional survey records across Nueva Vizcaya State University.<br>Every provisional entry is clearly identified until its information has been reviewed.</p></div>
        </section>

        <div class="directory-layout">
            <button class="mobile-filter-toggle" type="button" aria-controls="directory-filters" aria-expanded="false">
                <span aria-hidden="true">▽</span>
                Filter Trees
                @if ($activeFilterCount > 0)<span class="filter-count">{{ $activeFilterCount }}</span>@endif
            </button>
            <button class="filter-backdrop" type="button" aria-label="Close tree filters" tabindex="-1"></button>
            <input class="desktop-filter-state sr-only" id="desktop-filter-state" type="checkbox" aria-label="Collapse tree filter sidebar">

            <aside class="filters" id="directory-filters" aria-label="Tree directory filters">
                <div class="filters-heading">
                    <h2>Filter Trees</h2>
                    <label class="desktop-filter-collapse" for="desktop-filter-state" aria-label="Hide tree filters" title="Hide tree filters">‹</label>
                    <button class="mobile-filter-close" type="button" aria-label="Close tree filters">×</button>
                </div>
                <form action="{{ route('trees.index') }}" method="GET">
                    <label for="filter-search">Search</label>
                    <div class="search-field"><input id="filter-search" name="q" value="{{ request('q') }}" placeholder="Name, ID, family, or location"><span aria-hidden="true">⌕</span></div>

                    <label for="area">Campus Area</label>
                    <select id="area" name="area">
                        <option value="">All Areas</option>
                        @foreach ($campusAreas as $area)
                            <option value="{{ $area->id }}" @selected((string) request('area') === (string) $area->id)>{{ $area->name }}</option>
                        @endforeach
                    </select>

                    <label for="record-type">Record Type</label>
                    <select id="record-type" name="record_type">
                        <option value="all" @selected(request('record_type', 'all') === 'all')>All Records</option>
                        <option value="verified" @selected(request('record_type') === 'verified')>Verified Trees</option>
                        <option value="survey" @selected(request('record_type') === 'survey')>Provisional Survey Records</option>
                    </select>

                    <label for="condition">Tree Condition</label>
                    <select id="condition" name="condition">
                        <option value="">All Conditions</option>
                        @foreach ($conditions as $condition)
                            <option value="{{ $condition->value }}" @selected(request('condition') === $condition->value)>{{ str($condition->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>

                    <label for="conservation">Conservation Status</label>
                    <select id="conservation" name="conservation">
                        <option value="">All Conservation Statuses</option>
                        @foreach ($conservationStatuses as $status)
                            <option value="{{ $status->value }}" @selected(request('conservation') === $status->value)>{{ str($status->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>

                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort">
                        <option value="recent" @selected(request('sort', 'recent') === 'recent')>Most Recent</option>
                        <option value="name" @selected(request('sort') === 'name')>Tree Name A–Z</option>
                        <option value="code" @selected(request('sort') === 'code')>Tree ID</option>
                    </select>

                    <input type="hidden" name="per_page" value="{{ request('per_page', 8) }}">
                    <button class="apply-button" type="submit">▽ &nbsp; Apply Filters</button>
                    <a class="clear-button" href="{{ route('trees.index') }}">↻ &nbsp; Clear Filters</a>
                </form>
            </aside>

            <section class="directory-results" aria-labelledby="results-heading">
                <div class="results-toolbar">
                    <div class="results-heading-group">
                        <label class="desktop-filter-reveal" for="desktop-filter-state">
                            <span aria-hidden="true">☷</span>
                            Show Filters
                        </label>
                        <div>
                            <h2 id="results-heading">Tree Directory</h2>
                            <p>
                                @if ($trees->total())
                                    Showing {{ $trees->firstItem() }}–{{ $trees->lastItem() }} of {{ number_format($trees->total()) }} directory records
                                @else
                                    No directory records match the selected filters
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="view-controls">
                        <button class="view-button active" type="button" data-view="grid" aria-pressed="true">▦ &nbsp; Grid View</button>
                        <button class="view-button" type="button" data-view="list" aria-pressed="false">☷ &nbsp; List View</button>
                        <form action="{{ route('trees.index') }}" method="GET">
                            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="per_page" aria-label="Results per page">
                                @foreach ([8, 12, 24, 48] as $size)
                                    <option value="{{ $size }}" @selected($trees->perPage() === $size)>{{ $size }} per page</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tree-grid" id="tree-results">
                    @forelse ($trees as $tree)
                        <article @class(['tree-card', 'provisional' => $tree->record_type === 'survey'])>
                            <div class="tree-photo-placeholder" role="img" aria-label="{{ $tree->name }} image{{ $tree->photo_url ? '' : ' placeholder' }}" @if($tree->photo_url) style="background-image:url('{{ $tree->photo_url }}')" @endif>
                                @unless($tree->photo_url)<b class="photo-placeholder-icon" aria-hidden="true">♣</b>@endunless
                                <span>{{ $tree->record_type === 'survey' ? 'Survey' : 'ID' }}: {{ $tree->code }}</span>
                            </div>
                            <div class="tree-info">
                                <h3>{{ $tree->name }}</h3>
                                <em>{{ $tree->scientific_name }}</em>
                                <p>Family: {{ $tree->family }}</p>
                                <p class="location">◆ &nbsp; {{ $tree->location_label }}</p>
                                <div class="tree-badges">
                                    @if ($tree->record_type === 'verified')
                                        <span>Condition: <b class="condition condition-{{ $tree->condition }}">{{ str($tree->condition)->title() }}</b></span>
                                        <span>Status: <b class="tree-status">Existing</b></span>
                                    @else
                                        <span>Height: <b class="survey-value">{{ $tree->height_m !== null ? $tree->height_m.' m' : 'Pending' }}</b></span>
                                        <span><b class="survey-status">Provisional</b></span>
                                    @endif
                                </div>
                                <p @class(['verified' => $tree->record_type === 'verified', 'survey-note' => $tree->record_type === 'survey'])>
                                    {{ $tree->record_type === 'verified' ? '●  Verified record' : '●  Information awaiting verification' }}
                                </p>
                                <a class="profile-link" href="{{ $tree->action_url }}">{{ $tree->record_type === 'verified' ? '⊙  View Profile' : '⌖  View on Map' }}</a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-results">
                            <strong>No trees found</strong>
                            <p>Try changing the search text or clearing the selected filters.</p>
                            <a class="clear-button" href="{{ route('trees.index') }}">Clear all filters</a>
                        </div>
                    @endforelse
                </div>

                @if ($trees->hasPages())
                    <nav class="pagination" aria-label="Tree directory pages">
                        <span class="pagination-status">
                            <strong>{{ number_format($trees->firstItem()) }}–{{ number_format($trees->lastItem()) }}</strong>
                            <span>of {{ number_format($trees->total()) }} trees</span>
                        </span>

                        <div class="pagination-pages-slot">
                            <div class="pagination-pages">
                            @php
                                $previousTenPage = max(1, $trees->currentPage() - 10);
                                $nextTenPage = min($trees->lastPage(), $trees->currentPage() + 10);
                            @endphp

                            @if ($trees->currentPage() > 1)
                                <a class="pagination-control pagination-jump" href="{{ $trees->url($previousTenPage) }}#results-heading" aria-label="Go back 10 pages" title="Back 10 pages">&laquo;</a>
                            @else
                                <span class="pagination-control pagination-jump disabled" aria-disabled="true" title="Back 10 pages">&laquo;</span>
                            @endif

                            @if ($trees->onFirstPage())
                                <span class="pagination-control disabled" aria-hidden="true">&lsaquo;</span>
                            @else
                                <a class="pagination-control" href="{{ $trees->previousPageUrl() }}#results-heading" rel="prev" aria-label="Previous page">&lsaquo;</a>
                            @endif

                            @php
                                $firstVisiblePage = max(1, $trees->currentPage() - 2);
                                $lastVisiblePage = min($trees->lastPage(), $trees->currentPage() + 2);
                            @endphp

                            @if ($firstVisiblePage > 1)
                                <a href="{{ $trees->url(1) }}#results-heading" aria-label="Page 1">1</a>
                                @if ($firstVisiblePage > 2)<span class="pagination-gap" aria-hidden="true">&hellip;</span>@endif
                            @endif

                            @foreach (range($firstVisiblePage, $lastVisiblePage) as $page)
                                <a href="{{ $trees->url($page) }}#results-heading" @class(['current' => $page === $trees->currentPage()]) @if($page === $trees->currentPage()) aria-current="page" @endif aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endforeach

                            @if ($lastVisiblePage < $trees->lastPage())
                                @if ($lastVisiblePage < $trees->lastPage() - 1)<span class="pagination-gap" aria-hidden="true">&hellip;</span>@endif
                                <a href="{{ $trees->url($trees->lastPage()) }}#results-heading" aria-label="Page {{ $trees->lastPage() }}">{{ $trees->lastPage() }}</a>
                            @endif

                            @if ($trees->hasMorePages())
                                <a class="pagination-control" href="{{ $trees->nextPageUrl() }}#results-heading" rel="next" aria-label="Next page">&rsaquo;</a>
                            @else
                                <span class="pagination-control disabled" aria-hidden="true">&rsaquo;</span>
                            @endif

                            @if ($trees->currentPage() < $trees->lastPage())
                                <a class="pagination-control pagination-jump" href="{{ $trees->url($nextTenPage) }}#results-heading" aria-label="Go forward 10 pages" title="Forward 10 pages">&raquo;</a>
                            @else
                                <span class="pagination-control pagination-jump disabled" aria-disabled="true" title="Forward 10 pages">&raquo;</span>
                            @endif
                            </div>
                        </div>
                    </nav>
                @endif
            </section>
        </div>

        <aside class="directory-note"><strong>♣ &nbsp; Note:</strong><span>Verified trees are certified inventory records. Survey records are provisional coordinates and may contain incomplete information.</span></aside>
    </main>

    <script src="{{ asset('js/tree-directory.js') }}?v={{ filemtime(public_path('js/tree-directory.js')) }}" defer></script>
</body>
</html>
