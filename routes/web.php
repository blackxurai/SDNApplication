<?php

use App\Http\Controllers\CostingSheetController;
use App\Http\Controllers\QuotationController;
use App\Models\CostingSheet;
use App\Models\Quotation;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'costingSheets' => CostingSheet::count(),
            'quotations'    => Quotation::count(),
            'accepted'      => Quotation::where('status', 'accepted')->count(),
        ],
        'recentSheets' => CostingSheet::latest()->take(5)->get()->map(fn($s) => [
            'id' => $s->id, 'reference' => $s->reference,
            'project_name' => $s->project_name, 'client_name' => $s->client_name,
            'status' => $s->status,
        ]),
        'recentQuotations' => Quotation::latest()->take(5)->get()->map(fn($q) => [
            'id' => $q->id, 'reference' => $q->reference,
            'project_name' => $q->project_name, 'client_name' => $q->client_name,
            'status' => $q->status,
        ]),
    ]);
})->name('dashboard');

Route::resource('costing-sheets', CostingSheetController::class);
Route::patch('costing-sheets/{costingSheet}/finalize', [CostingSheetController::class, 'finalize'])
    ->name('costing-sheets.finalize');

Route::resource('quotations', QuotationController::class)->except(['create', 'store']);
Route::post('quotations/from-costing/{costingSheet}', [QuotationController::class, 'fromCosting'])
    ->name('quotations.from-costing');
Route::patch('quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])
    ->name('quotations.update-status');
