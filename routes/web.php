<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\LeadSourceController;
use App\Http\Controllers\Admin\LeadStageController;
use App\Http\Controllers\Admin\LeadStatusController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authenticated CRM Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Bulk Lead Import & Dynamic Mapping (Admin & Manager)
    Route::get('/leads/import', [LeadImportController::class, 'create'])->name('leads.import');
    Route::post('/leads/import/preview', [LeadImportController::class, 'preview'])->name('leads.import.preview');
    Route::post('/leads/import/execute', [LeadImportController::class, 'execute'])->name('leads.import.execute');

    // Leads & Lead Workflow
    Route::resource('leads', LeadController::class);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/follow-ups', [LeadController::class, 'addFollowUp'])->name('leads.follow-ups.store');
    Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity'])->name('leads.activities.store');
    Route::post('/leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.notes.store');
    Route::post('/leads/{lead}/qualify', [LeadController::class, 'qualify'])->name('leads.qualify');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convertToOpportunity'])->name('leads.convert');
    Route::post('/leads/{lead}/mark-lost', [LeadController::class, 'markLost'])->name('leads.mark-lost');
    Route::post('/leads/{lead}/stage', [LeadController::class, 'updateStage'])->name('leads.stage.update');

    // Opportunities & Sales Pipeline
    Route::resource('opportunities', OpportunityController::class);
    Route::post('/opportunities/{opportunity}/stage-ajax', [OpportunityController::class, 'updateStageAjax'])->name('opportunities.stage.ajax');
    Route::post('/opportunities/{opportunity}/won', [OpportunityController::class, 'markWon'])->name('opportunities.mark-won');
    Route::post('/opportunities/{opportunity}/lost', [OpportunityController::class, 'markLost'])->name('opportunities.mark-lost');

    // Quotations
    Route::resource('quotations', QuotationController::class);
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
    Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
    Route::post('/quotations/{quotation}/email', [QuotationController::class, 'sendEmail'])->name('quotations.email');
    Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');

    // Follow-ups
    Route::resource('follow-ups', FollowUpController::class);
    Route::post('/follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])->name('follow-ups.complete');
    Route::post('/follow-ups/{followUp}/email-summary', [FollowUpController::class, 'emailSummary'])->name('follow-ups.email-summary');

    // Activities
    Route::resource('activities', ActivityController::class);
    Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete'])->name('activities.complete');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/leads', [ReportController::class, 'exportLeads'])->name('reports.export.leads');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::match(['get', 'post'], '/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::match(['get', 'post'], '/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Admin & Masters Management
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('lead-sources', LeadSourceController::class);
        Route::resource('lead-statuses', LeadStatusController::class);
        Route::resource('lead-stages', LeadStageController::class);
        Route::resource('services', ServiceController::class);
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
