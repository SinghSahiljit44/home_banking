<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return redirect()->intended('/dashboard-admin');
        } elseif ($user->isEmployee()) {
            return redirect()->intended('/dashboard-employee');
        } elseif ($user->isClient()) {
            return redirect()->intended('/dashboard-cliente');
        }
        
        return redirect()->intended('/dashboard');
    }
}