<?php

use App\Http\Controllers\AuditPdfController;
use App\Http\Controllers\ChecklistFileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoutController;
use App\Livewire\Admin\AuditorManager;
use App\Livewire\Admin\CorrectiveActionRegister;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Audits\AuditIndex;
use App\Livewire\Audits\Checklist;
use App\Livewire\Audits\Conclusion;
use App\Livewire\Audits\CorrectiveActions;
use App\Livewire\Audits\Identity;
use App\Livewire\Audits\Inspection;
use App\Livewire\Audits\Recap;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('/login', Login::class)->name('login');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::livewire('/audits', AuditIndex::class)->name('audits.index');
    Route::get('/checklist/template', [ChecklistFileController::class, 'template'])->name('checklist.template');

    // The six audit steps, in the order from the client's Menu.docx.
    Route::prefix('/audits/{audit}')
        ->name('audits.')
        ->middleware('can:view,audit')
        ->scopeBindings()
        ->group(function () {
            Route::livewire('/identitas', Identity::class)->name('identity');
            Route::livewire('/checklist', Checklist::class)->name('checklist');
            Route::livewire('/daftar-periksa', Inspection::class)->name('inspection');
            Route::livewire('/car', CorrectiveActions::class)->name('car');
            Route::livewire('/rekap', Recap::class)->name('recap');
            Route::livewire('/kesimpulan', Conclusion::class)->name('conclusion');

            Route::get('/departemen/{department}/checklist.{format}', [ChecklistFileController::class, 'export'])
                ->whereIn('format', ['xlsx', 'csv', 'json'])
                ->name('checklist.export');

            Route::get('/pdf/daftar-periksa/{department}', [AuditPdfController::class, 'inspection'])->name('pdf.inspection');
            Route::get('/pdf/car', [AuditPdfController::class, 'correctiveActions'])->name('pdf.car');
            Route::get('/pdf/rekap', [AuditPdfController::class, 'recap'])->name('pdf.recap');
            Route::get('/pdf/kesimpulan', [AuditPdfController::class, 'conclusion'])->name('pdf.conclusion');
        });

    Route::middleware('role:admin')->prefix('/admin')->name('admin.')->group(function () {
        Route::livewire('/', Dashboard::class)->name('dashboard');
        Route::livewire('/auditor', AuditorManager::class)->name('auditors');
        Route::livewire('/car', CorrectiveActionRegister::class)->name('cars');
    });
});
