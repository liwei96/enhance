<?php

namespace app\http\middleware;

class Test
{
    public function handle($request, \Closure $next)
    {
        if($request->controller()=='index'){
            Log::write('test');
            return $next($request);
        }
    }
}
