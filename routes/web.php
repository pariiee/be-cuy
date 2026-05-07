<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello World';
})->name('home');

Route::get('/docs', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return redirect('/admin')->with('status', 'Login sebagai admin untuk mengakses API Docs.');
    }
    return view('docs.api', ['apiToken' => auth()->user()->api_token]);
})->name('docs.api');

Route::get('/docs/sandbox', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return redirect('/admin')->with('status', 'Login sebagai admin untuk mengakses API Sandbox.');
    }
    return view('docs.sandbox');
})->name('docs.sandbox');

Route::middleware(['auth', 'admin'])->get('/admin/qris-markup', function () {
    return view('admin.qris-markup');
})->name('admin.qris-markup');
