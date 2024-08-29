<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoAuthenticateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $id = 'Token-ID';
        if ($request->header($id) && !Auth::check()) {
            // Assuming you have a model named 'User' and a field named 'token' to store the token

            try {
                $token = decrypt($request->header($id));
                $user = \App\Models\User::find($token);
                if ($user) {
                    Auth::login($user);
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return $next($request);
    }
}
