@props(['active'])

@php
    $navigationItems = [
        ['key' => 'home', 'label' => 'Home', 'route' => 'home'],
        ['key' => 'trees', 'label' => 'Tree Directory', 'route' => 'trees.index'],
        ['key' => 'map', 'label' => 'Campus Map', 'route' => 'campus-map'],
        ['key' => 'about', 'label' => 'About the Project', 'route' => 'about'],
    ];
@endphp

<header class="navbar sticky top-0 z-[2000] border-b border-base-300 bg-base-100/95 px-4 shadow-sm backdrop-blur lg:px-8">
    <div class="navbar-start gap-2">
        <div class="dropdown lg:hidden">
            <button tabindex="0" type="button" class="btn btn-ghost btn-circle" aria-label="Open navigation menu">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <ul tabindex="0" class="menu dropdown-content z-10 mt-3 w-64 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                @foreach ($navigationItems as $item)
                    <li><a @class(['menu-active bg-primary text-white!' => $active === $item['key']]) href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <a class="flex min-w-0 items-center gap-3" href="{{ route('home') }}">
            <span class="avatar">
                <span class="w-11 rounded-full bg-primary/10 p-1"><img src="{{ asset('images/tree.png') }}" alt="QR-TIMS"></span>
            </span>
            <span class="hidden min-w-0 sm:block">
                <strong class="block truncate text-base text-primary">NVSU Tree Inventory</strong>
                <small class="block truncate text-xs text-base-content/65">QR Code-Integrated Monitoring System</small>
            </span>
        </a>
    </div>

    <nav class="navbar-center hidden lg:flex" aria-label="Main navigation">
        <ul class="menu menu-horizontal gap-1 px-1">
            @foreach ($navigationItems as $item)
                <li><a @class(['menu-active bg-primary font-semibold text-white!' => $active === $item['key']]) href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
            @endforeach
        </ul>
    </nav>

    <div class="navbar-end">
        @auth
            @php
                $authenticatedUser = auth()->user();
                $roleLabel = str($authenticatedUser->getRoleNames()->first() ?? 'user')->replace('-', ' ')->title();
            @endphp
            <div class="dropdown dropdown-end">
                <button tabindex="0" type="button" class="btn btn-circle relative border border-primary/20 bg-base-100 p-0 shadow-sm transition hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Open account menu for {{ $authenticatedUser->name }}" aria-haspopup="menu" data-testid="account-avatar-trigger" title="{{ $authenticatedUser->name }}">
                    <span class="avatar placeholder">
                        <span class="grid size-10 place-items-center rounded-full bg-primary text-primary-content ring-2 ring-primary/15 ring-offset-2 ring-offset-base-100">
                            <span class="text-sm leading-none font-bold">{{ strtoupper(substr($authenticatedUser->name, 0, 1)) }}</span>
                        </span>
                    </span>
                    <span class="absolute right-0.5 bottom-0.5 size-3 rounded-full border-2 border-base-100 bg-success" aria-hidden="true"></span>
                </button>

                <div tabindex="0" class="card dropdown-content z-10 mt-3 w-72 border border-base-300 bg-base-100 shadow-xl" role="menu" aria-label="Account menu">
                    <div class="card-body gap-3 p-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="avatar placeholder shrink-0">
                                <span class="grid size-12 place-items-center rounded-full bg-primary/10 text-primary ring-1 ring-primary/15">
                                    <span class="text-base leading-none font-bold">{{ strtoupper(substr($authenticatedUser->name, 0, 1)) }}</span>
                                </span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="truncate font-semibold text-base-content">{{ $authenticatedUser->name }}</h2>
                                <p class="truncate text-sm text-base-content/60">{{ $authenticatedUser->email }}</p>
                                <span class="badge badge-success badge-outline badge-sm mt-2">{{ $roleLabel }}</span>
                            </div>
                        </div>
                        <div class="divider my-0"></div>
                        @if ($authenticatedUser->isAdministrator())
                            <a class="btn btn-ghost btn-sm justify-start" href="/admin" role="menuitem">Administration Panel</a>
                        @endif
                        <a class="btn btn-ghost btn-sm justify-start" href="{{ route('trees.index') }}" role="menuitem">Tree Directory</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline btn-sm btn-block" type="submit" role="menuitem">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <a class="btn btn-primary btn-sm whitespace-nowrap sm:btn-md" href="{{ route('login') }}">Staff Login</a>
        @endauth
    </div>
</header>
