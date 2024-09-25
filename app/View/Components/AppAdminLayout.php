<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppAdminLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function render(): View
    {
        return view('layouts.appadmin');
    }
}
