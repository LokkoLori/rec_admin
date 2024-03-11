<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\MatchMakingController;
use App\Http\Controllers\ScoreTableController;
use App\Http\Controllers\GamerInfoController;
use App\Http\Controllers\MatchesController;
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
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');
    Route::get('/entries', [EntryController::class, 'create'])->name('entries.create');
});

Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/admin/roles', [AdminController::class, 'rolesIndex'])->name('admin.roles');
    Route::post('/admin/roles', [AdminController::class, 'createRole'])->name('admin.roles.create');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users/{user}/assign-role', [AdminController::class, 'assignRole'])->name('admin.users.assignRole');
    Route::get('/registration', [RegistrationController::class, 'index'])->name('registration.index');
    Route::post('/registration/entry_store', [RegistrationController::class, 'storeEntry'])->name('registration.entry_store');
    Route::post('/registration/entry_update', [RegistrationController::class, 'updateEntry'])->name('registration.entry_update');
    Route::get('/matchmaking', [MatchMakingController::class, 'index'])->name('matchmaking.index');
    Route::post('/matchmaking/create_match', [MatchMakingController::class, 'create_match'])->name('matchmaking.create_match');
    Route::post('/matchmaking/start_match', [MatchMakingController::class, 'start_match'])->name('matchmaking.start_match');
    Route::post('/matchmaking/finish_match', [MatchMakingController::class, 'finish_match'])->name('matchmaking.finish_match');
    Route::get('/matches', [MatchesController::class, 'index'])->name('matches.index');
    Route::post('/matches/update', [MatchesController::class, 'update'])->name('matches.update');
});

Route::get('/scoretable', [ScoreTableController::class, 'index'])->name('scoretable.index');
Route::get('/gamerinfo', [GamerInfoController::class, 'index'])->name('gamerinfo.index');

require __DIR__.'/auth.php';
