<?php

use App\Http\Controllers\Auth\InvitationAcceptanceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

Route::middleware('guest')->group(function () {
    Route::get('/invitations/accept/{token}', [InvitationAcceptanceController::class, 'create'])
        ->name('invitations.accept');
    Route::post('/invitations/accept', [InvitationAcceptanceController::class, 'store'])
        ->name('invitations.accept.store');
});

// --------------------------
// PROTECTED SPA ROUTES (Vue)
// --------------------------
Route::middleware(['auth'])->group(function () {

    // All Vue routes handled here
    Route::get('/{any}', function () {
        return view('layouts.app'); // Vue SPA root blade
    })->where('any', '^(?!api|logout|login|register).*$');

});


// Route::get('/test', function () {
//     return view('dashboard');
// });


Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth']);



Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out']);
});



// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';
