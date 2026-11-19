<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Admins land on their dashboard, auditors on their audit list.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('audits.index');
    }
}
