<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | NVSU Tree Inventory</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
</head>
<body>
    <main class="login-page">
        <section class="form-panel">
            <div class="login-card">
                <div class="card-heading"><h1>Reset your password</h1><p>Enter your authorized account email address to receive a secure reset link.</p></div>
                @if (session('status'))<div class="form-alert" role="status">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="form-alert" role="alert">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('password.email') }}" class="login-form">
                    @csrf
                    <div class="field-group"><label for="email">Email Address</label><div class="input-wrap"><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus></div></div>
                    <button class="primary-button" type="submit">Send Password Reset Link</button>
                </form>
                <p class="authorized-note"><a href="{{ route('login') }}">Return to login</a></p>
            </div>
        </section>
    </main>
</body>
</html>
