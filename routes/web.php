<?php

use App\Http\Controllers\PublicTreeController;
use App\Http\Controllers\CampusMapController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/login', 'auth.login')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()
            ->withErrors(['email' => 'The provided email address or password is incorrect.'])
            ->onlyInput('email');
    }

    $request->session()->regenerate();

    $destination = Auth::user()->isAdministrator() ? '/admin' : route('home');

    return redirect()->intended($destination)
        ->with('success', Auth::user()->isAdministrator()
            ? 'Welcome back, Administrator.'
            : 'Welcome back.');
})->middleware(['guest', 'throttle:5,1'])->name('login.submit');

Route::view('/forgot-password', 'auth.forgot-password')
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => ['required', 'email']]);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::ResetLinkSent
        ? back()->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->middleware(['guest', 'throttle:3,1'])->name('password.email');

Route::get('/reset-password/{token}', function (string $token, Request $request) {
    return view('auth.reset-password', ['token' => $token, 'email' => $request->string('email')]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $credentials = $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', PasswordRule::defaults()],
    ]);

    $status = Password::reset($credentials, function (User $user, string $password): void {
        $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));
    });

    return $status === Password::PasswordReset
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->middleware(['guest', 'throttle:5,1'])->name('password.update');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::get('/trees', [PublicTreeController::class, 'index'])->name('trees.index');
Route::get('/trees/{treeCode}', [PublicTreeController::class, 'show'])->name('trees.show');

Route::get('/campus-map', [CampusMapController::class, 'index'])->name('campus-map');
Route::get('/campus-map/data', [CampusMapController::class, 'data'])->name('campus-map.data');

Route::get('/about', [HomeController::class, 'about'])->name('about');
