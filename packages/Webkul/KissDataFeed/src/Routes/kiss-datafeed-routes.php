<?php

use Illuminate\Support\Facades\Route;
use Webkul\KissDataFeed\Http\Controllers\CredentialController;
use Webkul\KissDataFeed\Http\Controllers\MappingController;
use Webkul\KissDataFeed\Http\Controllers\ExportController;
use Webkul\KissDataFeed\Http\Controllers\OptionController;

Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('kiss-datafeed')->group(function () {

        // Credentials
        Route::get('/credentials', [CredentialController::class, 'index'])
            ->name('kiss_datafeed.credentials.index');

        Route::post('/credentials/create', [CredentialController::class, 'store'])
            ->name('kiss_datafeed.credentials.store');

        Route::get('/credentials/edit/{id}', [CredentialController::class, 'edit'])
            ->name('kiss_datafeed.credentials.edit');

        Route::put('/credentials/update/{id}', [CredentialController::class, 'update'])
            ->name('kiss_datafeed.credentials.update');

        Route::delete('/credentials/delete/{id}', [CredentialController::class, 'destroy'])
            ->name('kiss_datafeed.credentials.destroy');

        // Field Mapping
        Route::get('/mapping', [MappingController::class, 'select'])
            ->name('kiss_datafeed.mapping.select');

        Route::get('/mapping/{credentialId}', [MappingController::class, 'index'])
            ->name('kiss_datafeed.mapping.index');

        Route::post('/mapping/create', [MappingController::class, 'store'])
            ->name('kiss_datafeed.mapping.store');

        // Export
        Route::get('/export', [ExportController::class, 'index'])
            ->name('kiss_datafeed.export.index');

        Route::post('/export/run', [ExportController::class, 'run'])
            ->name('kiss_datafeed.export.run');

        // Options (AJAX endpoints)
        Route::get('/options/attributes', [OptionController::class, 'listAttributes'])
            ->name('kiss_datafeed.options.attributes');

        Route::get('/options/credentials', [OptionController::class, 'listCredentials'])
            ->name('kiss_datafeed.credential.fetch-all');
    });
});
