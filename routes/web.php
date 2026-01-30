<?php

use App\Http\Controllers\LeaveTemplateWorkingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeaveTemplateWorkingController::class, 'index'])->name('leave_template.index');
Route::get('/templates/create', [LeaveTemplateWorkingController::class, 'create'])->name('leave_template.create');
Route::get('/templates/edit/{id}', [LeaveTemplateWorkingController::class, 'edit'])->name('leave_template.create');
Route::post('/templates/store', [LeaveTemplateWorkingController::class, 'store'])->name('leave_template.store');
Route::put('/templates/update/{id}', [LeaveTemplateWorkingController::class, 'update'])->name('leave_template.update');
Route::get('/templates/generate-pdf/{id}', [LeaveTemplateWorkingController::class,'generatePdf'])->name('leave_template.generate.pdf');