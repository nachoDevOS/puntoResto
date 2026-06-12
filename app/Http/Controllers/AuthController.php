<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(): RedirectResponse
    {
        return redirect()->route('pos.index');
    }

    public function destroy(): RedirectResponse
    {
        return redirect()->route('login');
    }
}
