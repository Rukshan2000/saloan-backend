<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceBeauticianController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentServiceController;
use App\Http\Controllers\BeauticianAvailabilityController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // Branch CRUD
    Route::get('branches', [BranchController::class, 'index']);
    Route::post('branches', [BranchController::class, 'store']);
    Route::put('branches/{branch}', [BranchController::class, 'update']);

    // Get available time slots for beautician and service
    Route::get('appointment-services/available-time-slots', action: [AppointmentServiceController::class, 'getAvailableTimeSlots']);
    Route::patch('branches/{branch}', [BranchController::class, 'update']);
    Route::delete('branches/{branch}', [BranchController::class, 'destroy']);

    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('services', [ServiceController::class, 'index']);
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{service}', [ServiceController::class, 'update']);
    Route::patch('services/{service}', [ServiceController::class, 'update']);
    Route::delete('services/{service}', [ServiceController::class, 'destroy']);

    Route::get('service-beauticians', [ServiceBeauticianController::class, 'index']);
    Route::post('service-beauticians', [ServiceBeauticianController::class, 'store']);
    Route::put('service-beauticians/{serviceBeautician}', [ServiceBeauticianController::class, 'update']);
    Route::patch('service-beauticians/{serviceBeautician}', [ServiceBeauticianController::class, 'update']);
    Route::delete('service-beauticians/{serviceBeautician}', [ServiceBeauticianController::class, 'destroy']);

    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::patch('appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);
    // Update appointment status
    Route::put('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

    Route::get('appointment-services', [AppointmentServiceController::class, 'index']);
    Route::post('appointment-services', [AppointmentServiceController::class, 'store']);
    Route::put('appointment-services/{appointmentService}', [AppointmentServiceController::class, 'update']);
    Route::patch('appointment-services/{appointmentService}', [AppointmentServiceController::class, 'update']);
    Route::delete('appointment-services/{appointmentService}', [AppointmentServiceController::class, 'destroy']);

    Route::get('beautician-availability', [BeauticianAvailabilityController::class, 'index']);
    Route::post('beautician-availability', [BeauticianAvailabilityController::class, 'store']);
    Route::put('beautician-availability/{beauticianAvailability}', [BeauticianAvailabilityController::class, 'update']);
    Route::patch('beautician-availability/{beauticianAvailability}', [BeauticianAvailabilityController::class, 'update']);
    Route::delete('beautician-availability/{beauticianAvailability}', [BeauticianAvailabilityController::class, 'destroy']);

    Route::get('time-slots', [TimeSlotController::class, 'index']);
    Route::post('time-slots', [TimeSlotController::class, 'store']);
    Route::put('time-slots/{timeSlot}', [TimeSlotController::class, 'update']);
    Route::patch('time-slots/{timeSlot}', [TimeSlotController::class, 'update']);
    Route::delete('time-slots/{timeSlot}', [TimeSlotController::class, 'destroy']);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::post('invoices', [InvoiceController::class, 'store']);
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::patch('invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy']);

    Route::get('promotions', [PromotionController::class, 'index']);
    Route::post('promotions', [PromotionController::class, 'store']);
    Route::put('promotions/{promotion}', [PromotionController::class, 'update']);
    Route::patch('promotions/{promotion}', [PromotionController::class, 'update']);
    Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::put('roles/{role}', [RoleController::class, 'update']);
    Route::patch('roles/{role}', [RoleController::class, 'update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);

    Route::post('login', [LoginController::class, 'login']);
});

