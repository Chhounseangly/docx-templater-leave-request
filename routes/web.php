<?php

use App\Http\Controllers\LeaveTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeaveTemplateController::class, 'index'])->name('leave_template.index');
Route::get('/templates/create', [LeaveTemplateController::class, 'create'])->name('leave_template.create');
Route::get('/templates/edit/{id}', [LeaveTemplateController::class, 'edit'])->name('leave_template.create');
Route::post('/templates/store', [LeaveTemplateController::class, 'store'])->name('leave_template.store');
Route::put('/templates/update/{id}', [LeaveTemplateController::class, 'update'])->name('leave_template.update');
Route::get('/templates/generate-pdf/{id}', [LeaveTemplateController::class,'generatePdf'])->name('leave_template.generate.pdf');
