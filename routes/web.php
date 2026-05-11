<?php

use App\Livewire\Storefront\ProductCatalog;
use App\Livewire\Storefront\ProductDetail;
use Illuminate\Support\Facades\Route;

Route::get('/', ProductCatalog::class)->name('home');

Route::get('/producto/{producto}', ProductDetail::class)->name('product.detail');