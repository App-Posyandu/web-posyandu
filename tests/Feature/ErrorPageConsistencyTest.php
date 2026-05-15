<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('404 page renders consistent minimal error layout', function () {
    $this->get('/path-yang-tidak-ada')
        ->assertStatus(404)
        ->assertSee('404')
        ->assertSee('Not Found');
});

test('403 page renders consistent minimal error layout', function () {
    $kabid = User::factory()->create([
        'role' => 'kabid',
    ]);

    $this->actingAs($kabid)
        ->get(route('admin.settings.index'))
        ->assertStatus(403)
        ->assertSee('403');
});

test('404 page can display custom not found message', function () {
    Route::get('/__test-custom-404-message', function () {
        abort(404, 'Data tidak ditemukan.');
    });

    $this->get('/__test-custom-404-message')
        ->assertStatus(404)
        ->assertSee('404')
        ->assertSee('Data tidak ditemukan.');
});
