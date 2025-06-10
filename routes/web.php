]
<?php

use App\Http\Controllers\SoketiTestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoketiWebhookController;


Route::get('/', function () {
    return redirect('/admin');
});

// Soketi test routes
Route::middleware(['auth'])->group(function () {
    Route::get('/soketi/test/{soketiApp}', [SoketiTestController::class, 'test'])->name('soketi.test');
    Route::get('/api/soketi/test/{soketiApp}', [SoketiTestController::class, 'testApi'])->name('soketi.test.api');

    // Webhook test routes
    Route::get('/webhook/test/{webhook}', [\App\Http\Controllers\WebhookTestController::class, 'test'])->name('webhook.test');
    Route::post('/api/webhook/test/{webhook}', [\App\Http\Controllers\WebhookTestController::class, 'sendTest'])->name('webhook.test.send');

});

Route::post('soketi/webhook', [SoketiWebhookController::class, 'handle']);
