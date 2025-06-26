<?php
// app/Http/Responses/LoginResponse.php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            return redirect()->intended('/admin/dashboard');
        } elseif ($user->hasRole('employee')) {
            return redirect()->intended('/employee/dashboard');
        } elseif ($user->hasRole('client')) {
            return redirect()->intended('/client/dashboard');
        }
        
        return redirect()->intended('/dashboard');
    }
}