<!DOCTYPE html>
<html lang="en" data-theme="nvsu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore the verified tree inventory of Nueva Vizcaya State University.">
    <title>NVSU Tree Inventory and Monitoring System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 text-base-content">
    <x-public-header active="home" />

    <main>
        <section class="home-hero relative isolate overflow-hidden text-neutral-content">
            <div class="home-hero-glow home-hero-glow-one" aria-hidden="true"></div>
            <div class="home-hero-glow home-hero-glow-two" aria-hidden="true"></div>

            <div class="home-hero-grid mx-auto grid min-h-[36rem] w-full max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-20">
                <div class="relative z-10 max-w-3xl">
                    <span class="home-eyebrow inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[.22em] text-secondary backdrop-blur-md">
                        <span class="size-2 rounded-full bg-secondary shadow-[0_0_14px_currentColor]" aria-hidden="true"></span>
                        NVSU tree inventory
                    </span>
                    <h1 class="mt-5 text-4xl font-black leading-[1.05] tracking-tight sm:text-5xl lg:text-7xl">Explore the <span class="home-gradient-text">Trees</span> of NVSU</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-neutral-content/75 sm:text-lg">Discover mapped tree locations, verified profiles, and species recorded across the university campus through one connected digital inventory.</p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a class="btn btn-secondary home-primary-cta border-0 shadow-xl" href="{{ route('trees.index') }}">Browse Directory <span aria-hidden="true">→</span></a>
                        <a class="btn border-white/25 bg-white/10 text-white backdrop-blur-md hover:border-white/50 hover:bg-white/20" href="{{ route('campus-map') }}">⌖ Open Campus Map</a>
                    </div>

                    <div class="mt-9 flex flex-wrap gap-x-7 gap-y-3 text-sm text-neutral-content/65">
                        <span class="flex items-center gap-2"><i class="size-2 rounded-full bg-success shadow-[0_0_10px_currentColor]"></i> Live inventory</span>
                        <span class="flex items-center gap-2"><i class="size-2 rounded-full bg-info shadow-[0_0_10px_currentColor]"></i> QR-accessible</span>
                        <span class="flex items-center gap-2"><i class="size-2 rounded-full bg-secondary shadow-[0_0_10px_currentColor]"></i> Campus mapped</span>
                    </div>
                </div>

                <div class="home-hero-stage perspective-distant relative hidden min-h-[28rem] transform-3d lg:block" aria-hidden="true">
                    <div class="home-orbit home-orbit-one"></div>
                    <div class="home-orbit home-orbit-two"></div>
                    <div class="home-ground-plane"></div>
                    <div class="home-logo-slab transform-3d">
                        <div class="home-slab-topline"><span></span> LIVE CANOPY DATA</div>
                        <img src="{{ asset('images/tree.png') }}" alt="">
                        <div class="home-slab-meter"><span></span></div>
                        <strong>QR–TIMS</strong>
                        <small>NVSU DIGITAL INVENTORY</small>
                    </div>
                    <div class="home-float-chip home-float-chip-map"><span>⌖</span><b>{{ number_format($stats['mapped']) }}</b><small>mapped points</small></div>
                    <div class="home-float-chip home-float-chip-verified"><span>✓</span><b>{{ number_format($stats['verified']) }}</b><small>verified profiles</small></div>
                    <div class="home-float-chip home-float-chip-qr"><span>▦</span><b>QR</b><small>scan ready</small></div>
                </div>
            </div>
        </section>

        <section class="nvsu-section relative z-10 py-10" aria-label="Live tree inventory summary">
            <div class="home-stat-grid grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="home-depth-card home-stat-card">
                    <div class="home-stat-icon bg-primary/10 text-primary" aria-hidden="true">♣</div>
                    <div class="text-sm text-base-content/60">Mapped Records</div>
                    <div class="mt-1 text-4xl font-black text-primary">{{ number_format($stats['mapped']) }}</div>
                    <div class="mt-2 text-xs text-base-content/55">{{ number_format($stats['survey']) }} provisional locations</div>
                </article>
                <article class="home-depth-card home-stat-card">
                    <div class="home-stat-icon bg-success/10 text-success" aria-hidden="true">⌁</div>
                    <div class="text-sm text-base-content/60">Recorded Species Labels</div>
                    <div class="mt-1 text-4xl font-black text-success">{{ number_format($stats['species']) }}</div>
                    <div class="mt-2 text-xs text-base-content/55">Pending forestry normalization</div>
                </article>
                <article class="home-depth-card home-stat-card">
                    <div class="home-stat-icon bg-secondary/15 text-secondary" aria-hidden="true">⌖</div>
                    <div class="text-sm text-base-content/60">Campus Areas</div>
                    <div class="mt-1 text-4xl font-black text-secondary">{{ number_format($stats['areas']) }}</div>
                    <div class="mt-2 text-xs text-base-content/55">Areas with verified records</div>
                </article>
                <article class="home-depth-card home-stat-card">
                    <div class="home-stat-icon bg-info/10 text-info" aria-hidden="true">✓</div>
                    <div class="text-sm text-base-content/60">Verified Records</div>
                    <div class="mt-1 text-4xl font-black text-info">{{ number_format($stats['verified']) }}</div>
                    <div class="mt-2 text-xs text-base-content/55">Approved public profiles</div>
                </article>
            </div>
        </section>

        <section class="nvsu-section pt-4" id="featured">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="text-sm font-semibold uppercase tracking-widest text-primary">From the inventory</span>
                    <h2 class="mt-1 text-3xl font-bold">Featured Trees</h2>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('trees.index') }}">View directory →</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredTrees as $tree)
                    @php
                        $conditionClass = match ($tree->condition) {
                            'healthy' => 'badge-success',
                            'needs_attention' => 'badge-warning',
                            'critical' => 'badge-error',
                            default => 'badge-ghost',
                        };
                    @endphp
                    <article class="card home-tilt-card group overflow-hidden border border-base-300/80 bg-base-100 shadow-lg">
                        <figure class="relative h-40 bg-primary/10">
                            @if ($tree->photo_url)
                                <img class="h-full w-full object-cover" src="{{ $tree->photo_url }}" alt="{{ $tree->name }}">
                            @else
                                <img class="h-32 w-32 object-contain opacity-25" src="{{ asset('images/tree.png') }}" alt="">
                            @endif
                            <span class="absolute inset-0 bg-linear-to-t from-neutral/25 to-transparent opacity-60"></span>
                            <span class="badge badge-neutral absolute left-4 top-4">{{ $tree->code }}</span>
                        </figure>
                        <div class="card-body p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="card-title">{{ $tree->name }}</h3>
                                    <p class="italic text-base-content/60">{{ $tree->scientific_name }}</p>
                                </div>
                                <span class="badge {{ $conditionClass }} badge-sm whitespace-nowrap">{{ str($tree->condition)->replace('_', ' ')->title() }}</span>
                            </div>
                            <p class="mt-2 text-sm text-base-content/70">⌖ {{ $tree->location }}</p>
                            <div class="card-actions mt-3 justify-end">
                                <a class="btn btn-primary btn-sm" href="{{ $tree->profile_url }}">View Tree Profile</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="alert alert-info sm:col-span-2 lg:col-span-3"><span>Verified featured trees will appear here.</span></div>
                @endforelse
            </div>
        </section>

        <section class="nvsu-section grid items-stretch gap-6 lg:grid-cols-2" id="campus-map">
            <div class="card image-full home-map-card home-tilt-card min-h-96 overflow-hidden shadow-2xl">
                <figure>
                    <img class="h-full w-full object-cover" src="{{ asset('images/map.png') }}" alt="NVSU campus map preview">
                </figure>
                <div class="card-body justify-end p-6 text-neutral-content lg:p-8">
                    <span class="badge badge-secondary badge-outline">Live locations</span>
                    <h2 class="card-title mt-3 text-3xl">Campus Tree Map</h2>
                    <p class="mt-2 text-neutral-content/80">Explore verified and provisional tree locations across NVSU campus areas.</p>
                    <div class="card-actions mt-5">
                        <a class="btn btn-secondary" href="{{ route('campus-map') }}">Explore {{ number_format($stats['mapped']) }} mapped records</a>
                    </div>
                </div>
            </div>

            <div class="home-depth-card card border border-base-300 bg-base-100 shadow-xl">
                <div class="card-body p-6 lg:p-8">
                    <span class="text-sm font-semibold uppercase tracking-widest text-primary">Quick guide</span>
                    <h2 class="card-title text-3xl">How It Works</h2>
                    <ol class="mt-5 grid gap-3">
                        <li class="home-process-step"><span>01</span><div><strong>Find a Tree</strong><small>Search the directory or campus map.</small></div></li>
                        <li class="home-process-step"><span>02</span><div><strong>Scan Its QR Code</strong><small>Open the code attached to the tree.</small></div></li>
                        <li class="home-process-step"><span>03</span><div><strong>View Information</strong><small>Read its verified profile and condition.</small></div></li>
                    </ol>
                </div>
            </div>
        </section>
    </main>

    <x-public-footer />
</body>
</html>
