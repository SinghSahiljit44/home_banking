<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!auth()->user()->can($permission)) {
            abort(403, 'Non hai i permessi necessari per questa azione');
        }

        return $next($request);
    }
}