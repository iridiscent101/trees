<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Verified profile for {{ $tree->species->common_name }} ({{ $tree->tree_code }}).">
    <title>{{ $tree->species->common_name }} {{ $tree->tree_code }} | NVSU Tree Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tree-directory.css') }}?v={{ filemtime(public_path('css/tree-directory.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-header.css') }}?v={{ filemtime(public_path('css/public-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-polish.css') }}">
</head>
<body>
    <x-public-header active="trees" />

    <main class="tree-profile-page">
        <nav class="profile-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('trees.index') }}">Tree Directory</a><span>›</span><span>{{ $tree->tree_code }}</span></nav>

        <article class="tree-profile">
            <section class="profile-visual">
                @if ($tree->primaryPhoto)
                    <img src="{{ Storage::disk($tree->primaryPhoto->disk)->url($tree->primaryPhoto->path) }}" alt="{{ $tree->primaryPhoto->alt_text ?: $tree->species->common_name }}">
                @else
                    <div class="profile-photo-placeholder"><span>♣</span><p>Tree photograph will appear here</p></div>
                @endif
                <span class="profile-code">{{ $tree->tree_code }}</span>
            </section>

            <section class="profile-summary-card">
                <p class="verified-label">● Verified public record</p>
                <h1>{{ $tree->species->common_name }}</h1>
                <p class="scientific-name">{{ $tree->species->scientific_name }}</p>
                @if ($tree->species->local_name)<p class="local-name">Also known locally as {{ $tree->species->local_name }}</p>@endif

                <dl class="profile-facts">
                    <div><dt>Family</dt><dd>{{ $tree->species->family_name }}</dd></div>
                    <div><dt>Condition</dt><dd><span class="condition condition-{{ $tree->current_condition->value }}">{{ str($tree->current_condition->value)->title() }}</span></dd></div>
                    <div><dt>Campus area</dt><dd>{{ $tree->campusArea->name }}</dd></div>
                    <div><dt>Location</dt><dd>{{ $tree->descriptive_location }}</dd></div>
                    @if ($tree->nearby_landmark)<div><dt>Nearby landmark</dt><dd>{{ $tree->nearby_landmark }}</dd></div>@endif
                    @if ($tree->estimated_height_m)<div><dt>Estimated height</dt><dd>{{ $tree->estimated_height_m }} m</dd></div>@endif
                    @if ($tree->trunk_diameter_cm)<div><dt>Trunk diameter</dt><dd>{{ $tree->trunk_diameter_cm }} cm</dd></div>@endif
                    @if ($tree->estimated_age_years)<div><dt>Estimated age</dt><dd>{{ $tree->estimated_age_years }} years</dd></div>@endif
                </dl>

                <div class="profile-actions" aria-label="Tree profile actions">
                    <a class="primary" href="{{ route('campus-map', ['q' => $tree->tree_code]) }}">⌖ View on Campus Map</a>
                    <a href="{{ route('trees.index') }}">Browse Tree Directory</a>
                    @if ($tree->latitude !== null && $tree->longitude !== null)
                        <a href="https://www.openstreetmap.org/?mlat={{ $tree->latitude }}&mlon={{ $tree->longitude }}#map=20/{{ $tree->latitude }}/{{ $tree->longitude }}" target="_blank" rel="noopener noreferrer">Open precise location ↗</a>
                    @endif
                </div>
            </section>
        </article>

        <div class="profile-details-grid">
            <section class="profile-panel"><h2>About this species</h2><p>{{ $tree->species->description ?: 'A detailed species description has not yet been added.' }}</p></section>
            <section class="profile-panel"><h2>Inventory notes</h2><p>{{ $tree->public_notes ?: 'No additional public notes are available for this tree.' }}</p><small>Verified {{ optional($tree->verified_at)->format('F j, Y') ?: 'for public display' }}</small></section>
            @if ($tree->monitorings->isNotEmpty())
                <section class="profile-panel">
                    <h2>Verified monitoring history</h2>
                    <div class="monitoring-history">
                    @foreach ($tree->monitorings as $monitoring)
                        <article class="monitoring-entry">
                            <strong>{{ $monitoring->observed_at->format('F j, Y') }} — {{ str($monitoring->condition->value)->title() }}</strong>
                            <p>{{ $monitoring->public_notes ?: 'Condition inspected and verified.' }}</p>
                        </article>
                    @endforeach
                    </div>
                </section>
            @endif
        </div>
    </main>
</body>
</html>
