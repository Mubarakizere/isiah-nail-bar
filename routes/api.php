<?php

use Illuminate\Support\Facades\Route;

Route::post('/webhook/weflexfy', [\App\Http\Controllers\PaymentController::class, 'handleWebhook']);
