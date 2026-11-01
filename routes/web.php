<?php

Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::get('/', 'HomeController@index')->name('home');
Route::get('enroll/login/{course}', 'EnrollmentController@handleLogin')->name('enroll.handleLogin')->middleware('auth');
Route::get('enroll/{course}', 'EnrollmentController@create')->name('enroll.create');
Route::post('enroll/{course}', 'EnrollmentController@store')->name('enroll.store');
Route::get('my-courses', 'EnrollmentController@myCourses')->name('enroll.myCourses')->middleware('auth');
Route::resource('courses', 'CourseController')->only(['index', 'show']);

// Internship registration (separate from the course enroll flow above)
Route::group(['prefix' => 'internship', 'as' => 'internship.'], function () {
    Route::get('register', 'InternshipRegistrationController@create')->name('register');
    Route::post('register', 'InternshipRegistrationController@store')->name('register.store');

    // Dependent dropdowns: University -> College -> Stream -> Subject
    Route::get('lookup/colleges', 'InternshipLookupController@colleges')->name('lookup.colleges')->middleware('throttle:60,1');
    Route::get('lookup/streams', 'InternshipLookupController@streams')->name('lookup.streams')->middleware('throttle:60,1');
    Route::get('lookup/subjects', 'InternshipLookupController@subjects')->name('lookup.subjects')->middleware('throttle:60,1');

    // Payment (Razorpay)
    Route::post('razorpay/webhook', 'InternshipPaymentController@webhook')->name('payment.webhook');
    Route::group(['middleware' => ['auth']], function () {
        Route::get('registration/{registration}', 'InternshipPaymentController@show')->name('registration.show');
        Route::post('registration/{registration}/order', 'InternshipPaymentController@order')->name('payment.order')->middleware('throttle:10,1');
        Route::post('registration/{registration}/verify', 'InternshipPaymentController@verify')->name('payment.verify')->middleware('throttle:10,1');
    });
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('my-profile', 'StudentPortalController@profile')->name('student.profile');
    Route::get('welcome-letter', 'StudentPortalController@welcomeLetter')->name('student.welcome-letter');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Disciplines
    Route::delete('disciplines/destroy', 'DisciplinesController@massDestroy')->name('disciplines.massDestroy');
    Route::resource('disciplines', 'DisciplinesController');

    // Institutions
    Route::delete('institutions/destroy', 'InstitutionsController@massDestroy')->name('institutions.massDestroy');
    Route::post('institutions/media', 'InstitutionsController@storeMedia')->name('institutions.storeMedia');
    Route::resource('institutions', 'InstitutionsController');

    // Courses
    Route::delete('courses/destroy', 'CoursesController@massDestroy')->name('courses.massDestroy');
    Route::post('courses/media', 'CoursesController@storeMedia')->name('courses.storeMedia');
    Route::resource('courses', 'CoursesController');

    // Enrollments
    Route::delete('enrollments/destroy', 'EnrollmentsController@massDestroy')->name('enrollments.massDestroy');
    Route::resource('enrollments', 'EnrollmentsController');

    // Internship management
    Route::delete('universities/destroy', 'UniversitiesController@massDestroy')->name('universities.massDestroy');
    Route::resource('universities', 'UniversitiesController');

    Route::delete('colleges/destroy', 'CollegesController@massDestroy')->name('colleges.massDestroy');
    Route::resource('colleges', 'CollegesController');

    Route::delete('streams/destroy', 'StreamsController@massDestroy')->name('streams.massDestroy');
    Route::resource('streams', 'StreamsController');

    Route::delete('subjects/destroy', 'SubjectsController@massDestroy')->name('subjects.massDestroy');
    Route::resource('subjects', 'SubjectsController');

    Route::delete('internships/destroy', 'InternshipsController@massDestroy')->name('internships.massDestroy');
    Route::resource('internships', 'InternshipsController');

    Route::resource('internship-classes', 'InternshipClassesController');

    Route::get('internship-registrations', 'InternshipRegistrationsController@index')->name('internship-registrations.index');
    Route::post('internship-registrations/{registration}/approve', 'InternshipRegistrationsController@approve')->name('internship-registrations.approve');
    Route::post('internship-registrations/{registration}/reject', 'InternshipRegistrationsController@reject')->name('internship-registrations.reject');
    Route::post('internship-registrations/{registration}/mark-paid', 'InternshipRegistrationsController@markPaid')->name('internship-registrations.mark-paid');
    Route::post('internship-registrations/{registration}/mark-unpaid', 'InternshipRegistrationsController@markUnpaid')->name('internship-registrations.mark-unpaid');
});
