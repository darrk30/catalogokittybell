<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Livewire\Attributes\On;

class CartCounter extends Component
{
    // Escucha el evento para refrescarse cuando agregues algo
    #[On('cart-updated')]
    public function render()
    {
        $count = collect(session()->get('cart', []))->sum('cantidad');

        return view('livewire.storefront.cart-counter', [
            'count' => $count
        ]);
    }
}