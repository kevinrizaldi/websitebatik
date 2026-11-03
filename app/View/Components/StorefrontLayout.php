<?php

namespace App\View\Components;

use App\Models\CartItem;
use Illuminate\View\Component;
use Illuminate\View\View;

class StorefrontLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $activeNav = null,
    ) {}

    public function render(): View
    {
        $cartCount = CartItem::forCurrentVisitor()->sum('qty');

        return view('layouts.storefront', [
            'cartCount' => $cartCount,
        ]);
    }
}
