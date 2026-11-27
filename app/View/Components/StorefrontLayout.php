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
        public ?int $cartCount = null,
    ) {}

    public function render(): View
    {
        $cartCount = $this->cartCount ?? (int) CartItem::forCurrentVisitor()->count();

        return view('layouts.storefront', [
            'cartCount' => $cartCount,
        ]);
    }
}
