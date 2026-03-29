<?php

use App\Models\AdminUser;

test('login page loads', function () {
    $this->get('/admin/login')->assertOk();
});

test('admin can login', function () {
    AdminUser::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.nl',
        'password' => bcrypt('password'),
    ]);

    $this->post('/admin/login', [
        'email' => 'admin@test.nl',
        'password' => 'password',
    ])->assertRedirect('/admin');
});

test('invalid credentials rejected', function () {
    $this->post('/admin/login', [
        'email' => 'wrong@test.nl',
        'password' => 'wrong',
    ])->assertRedirect()->assertSessionHasErrors();
});

test('unauthenticated user redirected to login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('authenticated admin can access dashboard', function () {
    $admin = AdminUser::create([
        'name' => 'Admin',
        'email' => 'admin@test.nl',
        'password' => bcrypt('pass'),
    ]);

    $this->actingAs($admin, 'admin')
        ->get('/admin')
        ->assertOk();
});
