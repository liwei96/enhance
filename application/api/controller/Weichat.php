<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/11
 * Time: 15:02
 */

namespace app\api\controller;
use app\api\model\Area;
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


}