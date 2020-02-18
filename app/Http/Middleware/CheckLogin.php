<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
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
        //是否登录
        if(strstr($_SERVER['REQUEST_URI'],'admin') && !strstr($_SERVER['REQUEST_URI'],'login') && !strstr($_SERVER['REQUEST_URI'],'admintv')){
            if(empty(session('id')) || empty(session('username'))){
                return redirect('/admintv');
            }
        }
        return $next($request);
    }
}
