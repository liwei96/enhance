<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/13
 * Time: 20:18
 */

namespace app\api\controller;
use think\facade\Cache;
use think\facade\Session;

/**
 * demon
 * Class Demo
 * @package app\api\controller
 */
class Demo
{
   public function redis(){
       //$result = sendmsg('18868181816','1234');//短信发送验证码
       session('wangxu:1234','123');
       dump(Session::get('wangxu:1234'));
//       Cache::store('redis')->set('wangxu:123','testredis',3600);
//       dump(Cache::store('redis')->get('wangxu:123'));
   }
}