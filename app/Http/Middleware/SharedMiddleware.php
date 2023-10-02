<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Auth;

class SharedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $isshared=DB::table('shared')->select('id')
        ->where('id_collection','=',$request->id)
        ->where('id_permission','=',1)->get();
        if($user || sizeof($isshared)>0)
            return $next($request);
        abort(401);
    }
}
