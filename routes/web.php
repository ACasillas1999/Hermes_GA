<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListadoController;
use App\Http\Controllers\MessageLogController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduledMessageController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\EmailTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/usuarios/crear', [UserController::class, 'create'])->middleware('first-user')->name('users.create');
Route::post('/usuarios', [UserController::class, 'store'])->middleware('first-user')->name('users.store');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/envios', [MessagingController::class, 'index'])->name('messaging.index');
    Route::post('/envios/send', [MessagingController::class, 'send'])->name('messages.send');
    Route::get('/batches/{id}', [MessagingController::class, 'batchStatus'])->name('batches.status');
    Route::get('/plantillas', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/plantillas', [TemplateController::class, 'store'])->name('templates.store');
    Route::get('/programados', [ScheduledMessageController::class, 'index'])->name('scheduled.index');
    Route::post('/programados', [ScheduledMessageController::class, 'store'])->name('scheduled.store');
    Route::get('/programados/{scheduledMessage}', [ScheduledMessageController::class, 'show'])->name('scheduled.show');
    Route::delete('/programados/{scheduledMessage}', [ScheduledMessageController::class, 'destroy'])->name('scheduled.destroy');
    Route::post('/templates/sync', [TemplateController::class, 'sync'])->name('templates.sync');
    Route::get('/templates', [TemplateController::class, 'list'])->name('templates.list');
    
    Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email_templates.index');
    Route::post('/email-templates/sync', [EmailTemplateController::class, 'sync'])->name('email_templates.sync');
    Route::post('/email-templates', [EmailTemplateController::class, 'store'])->name('email_templates.store');
    Route::delete('/email-templates/{emailTemplate}', [EmailTemplateController::class, 'destroy'])->name('email_templates.destroy');
    
    Route::get('/historial', [MessageLogController::class, 'index'])->name('history.index');

    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::resource('listados', ListadoController::class);
    Route::post('listados/{listado}/empleados/importar', [ListadoController::class, 'importCsv'])->name('listados.empleados.import');
    Route::post('listados/{listado}/empleados', [ListadoController::class, 'storeEmpleado'])->name('listados.empleados.store');
    Route::put('listados/{listado}/empleados/{empleado}', [ListadoController::class, 'updateEmpleado'])->name('listados.empleados.update');
    Route::delete('listados/{listado}/empleados/{empleado}', [ListadoController::class, 'destroyEmpleado'])->name('listados.empleados.destroy');
});
