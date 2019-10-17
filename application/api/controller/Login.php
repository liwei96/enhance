<?php

namespace app\api\controller;

use think\Controller;
use think\facade\Cache;
use think\facade\Session;
use think\Request;
use think\Validate;
use app\api\model\Staff;
use app\api\model\Role;


class Login extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
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

    /**
     * 登录验证
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        //
        $data= input('param.value');
        $phone= Staff::where('name',input('param.value.name'))->value('urgent');
        if(!$phone){
            return json(['code'=>300]);
        }
        $ma= input('param.value.ma');
        $rule=[
            'name'=>'require',
            'password'=>'require|length:6,12',
            'ma'=>'require'
        ];
        $msg=[
            'name.require'=>'用户名不能为空',
            'password.require'=>'密码不能为空',
            'password.length'=>'密码长度为6到12位',
            'ma.require'=>'验证码没填'
        ];
        $validate=new Validate($rule,$msg);
        if(!$validate->check($data)){
            $error=$validate->getError();
            $this->error($error);
        }
        if(!$ma){
            return json(['code'=>1003,'msg'=>'请输入验证码']);
        }
        if($ma!=cache($phone)){
            return json(['code'=>1001,'msg'=>'验证码错误']);
        }
        $password = encrypt_password($data['password']);
        $num = $this->createNoncestr(8);
        $re = Staff::where([['name','eq',$data['name']],['password','eq',$password]])->find();
        if($re){
            $re['ids']=Role::where('id',$re['job'])->value('ids');

            
            session('user',$re);

            // if(!in_array($phone,config('app.white_list_phone'))){
            //     cache($phone,null);//验证码建议长期有效
            // }
            cache($re['name'],$num,3600);
            return json(['code'=>200,'num'=>$num,'re'=>$re]);
        }else{
            return json(['code'=>300]);
        }
    }

    /**
     * 获取验证码
     * @return \think\response\Json
     */
    public function getcode(){
        $name = input('param.name');
        $phone= Staff::where('name',$name)->value('urgent');
        if(empty($phone)){
            return json(['code'=>300]);
        }

        $code = mt_rand(1000,9999);//验证码
        $register_time = cache($phone.'time') ? : 0;

        $time = time();
        //一分钟频率限制
        if ( time() - $register_time < 60) {
            $res = [
                'code' => 10003,
                'msg' => '发送太频繁，稍后再试',
            ];
            return json($res);
        }

        $result = sendmsg($phone,$code);//短信发送验证码

        if($result){

            if(in_array($phone,['18868181816'])){//白名单手机号
                Cache::set($phone,$code);//建议长期有效
            }else{
                Cache::set($phone,$code,300);//建议长期有效
            }

            Cache::set($phone.'time',$time,60);
            $res=[
                'code' => 200,
            ];
            return json($res);
        }else{
            $res=[
                'code' => 300,
                'msg' => '发送失败'
            ];
            return json($res);
        }
    }
    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    public function logout(){
        Session::start();
        Session::destroy();//退出时销毁全部session
        return json(['code'=>200]);
    }
}
