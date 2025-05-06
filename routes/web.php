<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jobs', [JobsController::class, 'index'])->name('jobs');
Route::get('/jobs/detail/{id}', [JobsController::class, 'detail'])->name('jobsDetail');
Route::post('/apply-job', [JobsController::class, 'applyJob'])->name('applyJob');
Route::post('/save-job', [JobsController::class, 'saveJob'])->name('saveJob');




Route::group(['account'], function () {
  // Guest route
  Route::group(['middleware' => 'guest'], function () {
    Route::get('/account/register', [AccountController::class, 'registration'])->name('account.registration');
    Route::post('/account/process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
    Route::get('/account/login', [AccountController::class, 'login'])->name('account.login');
    Route::post('/account/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
  });


  //Authenticated Routes
  Route::group(['middleware' => 'auth'], function () {
    Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::put('/account/update-profile', [AccountController::class, 'updateProfile'])->name('account.udpateprofile');
    Route::post('/update-profile-pic', [AccountController::class, 'updateProfilePic'])->name('account.updateProfilePic');
    Route::get('/account/create-job', [AccountController::class, 'createjob'])->name('account.createjob');
    Route::get('/account/edit-job/{jobId}', [AccountController::class, 'editjob'])->name('account.editjob');
    Route::post('/account/save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');
    Route::post('/account/edit-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');
    Route::post('/account/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');

    Route::get('/account/my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');
    Route::get('/account/my-job-applications', [AccountController::class, 'myJobApplication'])->name('account.my-job-applications');
  
    Route::post('/remove-job-application', [AccountController::class, 'removeJobs'])->name('account.removeJobs');

  });
});
