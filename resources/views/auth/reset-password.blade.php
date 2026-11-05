<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose New Password | NVSU Tree Inventory</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
</head>
<body>
    <main class="login-page">
        <section class="form-panel">
            <div class="login-card">
                <div class="card-heading"><h1>Choose a new password</h1><p>Use at least eight characters with letters and numbers.</p></div>
                @if ($errors->any())<div class="form-alert" role="alert">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('password.update') }}" class="login-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="field-group"><label for="email">Email Address</label><div class="input-wrap"><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required></div></div>
                    <div class="field-group"><label for="password">New Password</label><div class="input-wrap"><input id="password" name="password" type="password" required></div></div>
                    <div class="field-group"><label for="password_confirmation">Confirm New Password</label><div class="input-wrap"><input id="password_confirmation" name="password_confirmation" type="password" required></div></div>
                    <button class="primary-button" type="submit">Reset Password</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
