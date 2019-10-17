<?php

namespace app\http\middleware;

class Test
{
    public function handle($request, \Closure $next)
    {
        if($request->controller()=='Test'){
            $data=$request->param();
            if($data){
                return redirect('http://test.com/index.php/hello/ss');
            }else{
                return redirect('think');
            }            
        }
    }
}
