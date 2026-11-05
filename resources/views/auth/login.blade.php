<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Administrator login for the NVSU Tree Inventory and Monitoring System.">
    <title>Administrator Login | NVSU Tree Inventory</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
</head>
<body>
    <main class="login-page">
        <section class="intro-panel" aria-labelledby="system-title">
            <div class="campus-image-placeholder" role="img" aria-label="Campus image placeholder"></div>
            <div class="intro-overlay"></div>

            <div class="intro-content">
                <header class="university-brand">
                    <img class="university-logo" src="{{ asset('images/nvsu.gif') }}" alt="Nueva Vizcaya State University logo">
                    <p>Nueva Vizcaya<br>State University</p>
                </header>

                <div class="intro-copy">
                    <h2 id="system-title">QR Code-Integrated<br>Tree Inventory and<br>Monitoring System</h2>
                    <p>A centralized system for recording, monitoring, and sharing verified information about the trees within the NVSU campus.</p>
                </div>

                <div class="feature-list" aria-label="System features">
                    <article class="feature-item">
                        <span class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 3a4 4 0 0 0-3.7 2.5A3.5 3.5 0 0 0 6 12h4v9h4v-9h4a3.5 3.5 0 0 0-2.3-6.5A4 4 0 0 0 12 3Z"/><path d="m9 15 3-3 3 3"/></svg>
                        </span>
                        <div><h2>Tree Inventory</h2><p>Register and manage campus trees with complete information.</p></div>
                    </article>
                    <article class="feature-item">
                        <span class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3z"/><path d="M15 14h2v2h-2zM19 14h2v4h-2zM14 19h4v2h-4zM20 20h1v1h-1z"/></svg>
                        </span>
                        <div><h2>QR Code Access</h2><p>Scan QR codes placed on signs near trees to view approved public information.</p></div>
                    </article>
                    <article class="feature-item">
                        <span class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z"/><path d="M9 3v15M15 6v15"/><circle cx="12" cy="10" r="2"/></svg>
                        </span>
                        <div><h2>Campus Mapping</h2><p>Visualize tree locations and explore the greenness of NVSU.</p></div>
                    </article>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="leaf-decoration" aria-hidden="true"></div>
            <div class="login-card">
                <div class="tree-mark">
                    <img src="{{ asset('images/tree.png') }}" alt="QR-TIMS logo">
                </div>
                <div class="card-heading">
                    <h1>Welcome Back!</h1>
                    <p>Sign in to access the administrator and<br>authorized user panel.</p>
                </div>

                @if ($errors->any())
                    <div class="form-alert" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="form-alert" role="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="login-form">
                    @csrf
                    <div class="field-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="Enter your email address" required autofocus>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required>
                            <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false">
                                <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember"><input type="checkbox" name="remember"><span>Remember me</span></label>
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>

                    <button class="primary-button" type="submit">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/></svg>
                        Log In
                    </button>

                </form>

                <p class="authorized-note">Access for authorized users only.</p>
            </div>
        </section>
    </main>

    <footer class="security-strip">
        <div class="security-item security-message">
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
            <span>Secure access for authorized Nueva Vizcaya State University users.</span>
        </div>
        <nav aria-label="Login page links">
            <a href="{{ route('about') }}"><span aria-hidden="true">ⓘ</span> About the Project</a>
        </nav>
        <div class="security-item audit-message">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-5"/></svg>
            <span>Data is secured and monitored.<br>All activities are logged.</span>
        </div>
    </footer>

    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}" defer></script>
</body>
</html>
