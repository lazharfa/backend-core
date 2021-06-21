<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\UserLog;

class UserLogging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = Auth::user();

            if ($request->method() != 'GET') {
                UserLog::create([
                    'user_id'   => $user->id,
                    'email'     => $user->email,
                    'request'   => json_encode($request->all()),
                    'activity'  => $request->path()
                ]);
            }   
        } catch (\Exception $e) {
            
        }
            

        return $next($request);
    }
}
