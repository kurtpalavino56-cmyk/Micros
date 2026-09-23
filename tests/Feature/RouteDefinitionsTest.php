<?php

use Illuminate\Support\Facades\Route;

test('core UI route names used by the views are registered', function () {
    expect(Route::has('dashboard'))->toBeTrue();
    expect(Route::has('courses.browse'))->toBeTrue();
    expect(Route::has('notifications.index'))->toBeTrue();
    expect(Route::has('search'))->toBeTrue();
    expect(Route::has('password.request'))->toBeTrue();
    expect(Route::has('faculty.dashboard'))->toBeTrue();
    expect(Route::has('faculty.courses'))->toBeTrue();
    expect(Route::has('faculty.create'))->toBeTrue();
    expect(Route::has('faculty.analytics'))->toBeTrue();
    expect(Route::has('admin.dashboard'))->toBeTrue();
});
