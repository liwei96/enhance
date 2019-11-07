<?php

namespace app\http\middleware;
use app\api\model\Staff;
use think\facade\Session;
use think\facade\Cache;
use app\api\model\Auth;
use app\api\model\Log;

class check
{
    function checkauth(){
        try{
            $role_id=session('user.job');
            if(1==$role_id){
                return true;
            }
            $controller=request()->controller();
            $action=request()->action();
            if($controller=='index' && $action=='index'){
                return true;
            }
            $role=session('user');
            $role_auth_ids=$role['ids'];
            $auth=Auth::where([
                ['auth_c','eq',$controller],
                ['auth_a','eq',$action]
            ])->find();
            if(!$auth){
                return true;
            }
            $auth_id=$auth['id'];
            if(!in_array($auth_id,explode(',',$role_auth_ids))){
                return false;
            }else{
                return true;
            }
        }catch (\Exception $e){
            return false;
        }
    }
    public function ip() {
        //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
        return $res;
    }

    public function handle($request, \Closure $next)
    {

        // if(config('app.local')==true){
        //     return $next($request);
        // }else{
        //     return json(['code'=>300,'msg'=>'不知道什么东西过期了']);
        // }

        try{
            $data=$request->param();
            $ll=[];
            $ll['name']=$data['name'];
            $ll['ip']=$this->ip();
            $ll['controller'] = request()->controller();
            $ll['action'] = request()->action();
            $ll['nei']= memory_get_peak_usage()/1024/1024;


            if($data['num']==Cache::get($data['name'])){
                if(Session::get('user')){
                    cache($data['name'],$data['num'],3600);
                   $check=Staff::where('id','eq',Session::get('user')['id'])->column('check')[0];
                   $controller=request()->controller();
                   $action=request()->action();
                    if($controller!='Guide' && $action != 'update'){
                        if($check == 2){
                            return json(['code'=>'303','msg'=>'有超过7天未更新的动态，不能访问']);
                        }
                    }
                    
                    if($this->checkauth()){
                        $param = input('param.');
                        $ll['param']= json_encode($param,JSON_UNESCAPED_UNICODE);
                        $ll['userid'] = session('user.id');
                        Log::create($ll);
                        return $next($request);
                    }else{
                        return json(['code'=>'403','msg'=>'没有权限']);
                    }
                }else{
                    return json(['code'=>'300','msg'=>'失去登录状态']);
                }
            }else{
                return json(['code'=>402,'msg'=>'登录超时，请重新登录']);
            }
        }catch (\Exception $e){
            return json(['code'=>500,'msg'=>$e->getMessage()]);
        }
    }
}
