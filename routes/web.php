<?php

use App\Http\Controllers\AssistantStreamController;
use App\Livewire\EmployeePortal;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/presentation', function () {
    return response()->file(public_path('presentation.html'));
})->name('presentation');

Route::get('/presentation/download', function () {
    return response()->download(public_path('presentation.pptx'), 'Kompetent-Buergerassistent-2030.pptx');
})->name('presentation.download');

Route::middleware(['auth'])->group(function () {
    Route::get('/portal', EmployeePortal::class)->name('portal.cases');
    Route::get('/portal/cases/{conversationId?}', EmployeePortal::class)->name('portal.case.show');
    Route::get('/employee', EmployeePortal::class)->name('portal.employee');
});

Route::post('/api/assistant/chat/stream', [AssistantStreamController::class, 'stream'])
    ->name('assistant.stream');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
