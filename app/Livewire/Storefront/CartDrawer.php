<?php
namespace App\Livewire\Storefront;

use Livewire\Component;
use Livewire\Attributes\On;

class CartDrawer extends Component
{
    // Escucha cuando se agrega algo para refrescar la lista
    #[On('cart-updated')]
    public function render()
    {
        return view('livewire.storefront.cart-drawer', [
            'cart' => session()->get('cart', [])
        ]);
    }

    // Elimina una combinación específica (Variante)
    public function removeItem($key)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }

        $this->dispatch('cart-updated'); // Notifica al contador del header
    }

    // Limpia todo el carrito
    public function clearCart()
    {
        session()->forget('cart');
        $this->dispatch('cart-updated');
    }
}
