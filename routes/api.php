<?php

use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute API untuk integrasi SiPintu Gateway Webhook Real-time.
|
*/

Route::post('/sipintu/sync-user', [OAuthController::class, 'syncUser'])->name('api.sipintu.sync-user');
