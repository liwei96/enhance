<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/11
 * Time: 15:02
 */

namespace app\api\controller;
use app\api\model\Staff;
use app\api\model\Role;
use Cryption;
use HttpHelper;
use think\facade\Cache;
use think\Request;
use \Weichat as WeichatModel;
use think\Controller;
use app\api\model\User;
use \FilterWord;

class Weichat extends Controller
{


    /**
     * 敏感词校验接口
     * @return \think\response\Json
     */
    public function validate_words(){
        try{
            $fuck = file_get_contents('./static/keyWords.txt');
            $content = input('param.content');//wangxu
            $secret = strtolower(input('param.secret',''));//d72f4974c08005991390f0fd78e8f064
            $salt = config('param.md5_salt','hzyhwl');

            if(md5($salt.$content)!=$secret){
                throw new \Exception('加密串校验失败');
            }
            $keyWord = FilterWord::strPosFuck($content,$fuck);
            if($keyWord){
                throw new \Exception("输入内容含有敏感词汇({$keyWord}),请重新编辑");
            }
            return json(['code'=>200,'message'=>'没有敏感词']);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }

    }
    protected function validate_logic($scecret,$apikey){
        $encrypted = encrypt_password($apikey);
        if($scecret !== $encrypted){
            return false;
        }
        return true;
    }


    /**
     * 获取微信access_token接口
     * @param Request $request
     * @return \think\response\Json
     */
    public function getToken(Request $request){

//        Cache::store('redis')->set('test:name:1','value',3600);
//        halt( Cache::store('redis')->get('test:name:1'));

        $ip = $request->ip();

        //不是ip白名单 || 不是微信环境(微信本身有检验白名单机制)
        if(!in_ip_whitelist($ip)||!is_weixin()){
            $scecret = input('post.scecret');
            $apikey = input('post.apikey');

            if(empty($scecret)||empty($apikey)){
                return json(['code'=>false,'message'=>'请输入验证参数','token'=>''],400);
            }

            //验证
            if(!$this->validate_logic($scecret,$apikey)){
                return json(['code'=>false,'message'=>'验证失败','token'=>''],402);
            }
        }

        try{
            $access_token = WeichatModel::getInstance()->AccessToken();
            if(empty($access_token)){
                throw new \Exception('获取 access_token 失败');
            }
            return json(['code'=>true,'message'=>'获取成功','token'=>$access_token],200);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage(),'token'=>$access_token],500);
        }
    }

    /**
     * 绑定判断
     */
    public function bindingjudge(){
        $code = input('param.code');
        try{
            $appId = config('app.Weichat.login_appid');
            $secret = config('app.Weichat.login_secret');
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appId}&secret={$secret}&code=$code&grant_type=authorization_code";
            $http = HttpHelper::initData($url,'application/json', '', 'UTF-8', []);
            $res = ($http->doGet());
            if(empty($res)){
                throw new \Exception('微信接口调用失败');
            }
            $obj = json_decode($res,true);
            if(!isset($obj['openid'])){
                throw new \Exception('微信接口调用失败:'.$res);
            }
            $staff = Staff::get(['openid'=>$obj['openid']]);
            if(empty($staff)){
                //todo 需要换成前端的绑定用户页路由
                echo "<script>parent.window.location.href='".
                    "http://ll.edefang.net/#/loginweibang?openid=".$obj['openid']."';</script>";
                exit();
            }
            if($staff->enable == 0){
              throw new \Exception('该账号已被禁用');
            }
            $num = $this->createNoncestr(8);
            session('user',$staff);
            cache($staff->name,$num,3600);

            $staff['ids']=Role::where('id',$staff->job)->value('ids');
            session('user',$staff);
            //todo 登录成功，需要换成登录成功的路由
            echo "<script>parent.window.location.href='".
                "http://ll.edefang.net/#/home?name=".Cryption::Encode($staff->name)."&num=".Cryption::Encode($num)."';</script>";
            exit();

        }catch (\Exception $e){
            //todo 需要换成前端的错误页路由
            echo "<script>parent.window.location.href='".
                'http://ll.edefang.net/#/loginshua?msg='.$e->getMessage()."';</script>";
            exit();
        }
    }

    /**
     * 确定登录
     * @return \think\response\Json
     */
    public function sure(){
        try{
            $username = input('param.username');
            $password = input('param.password');
            $openid = input('param.openid');

            $staff = Staff::get(['name'=>$username,'enable'=>1]);
            if(empty($staff)){
                throw new \Exception('员工不存在或被禁用');
            }

            if($staff->password != encrypt_password($password)){
                throw new \Exception('您输入的密码错误');
            }
            if(!empty($staff->openid)){
                throw new \Exception('您的微信已经绑定过，请联系管理员！');
            }
            $staff->openid = $openid;
            $staff->update_time = time();
            if(!$staff->save()){
                throw new \Exception($staff->getError());
            }

            $num = $this->createNoncestr(8);
            session('user',$staff);
            cache($staff->name,$num,3600);
            $staff['ids']=Role::where('id',$staff->job)->value('ids');
            session('user',$staff);
            return json(['code'=>200,'message'=>'登录成功','name'=>$staff->name,'num'=>$num]);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 产生定长随机串
     * @param int $length
     * @return string
     */
    function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
}