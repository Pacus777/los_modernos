<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:es,en'],
        ]);

        session(['locale' => $validated['locale']]);

        app()->setLocale($validated['locale']);

        return back();
    }
}