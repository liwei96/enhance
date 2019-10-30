<?php

namespace app\api\controller;

use EmailSend;
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

            if(!in_array($phone,config('app.white_list_phone'))){
                cache($phone,null);//验证码建议长期有效
            }
            cache($re['name'],$num,3600);
            return json(['code'=>200,'num'=>$num,'re'=>$re]);
        }else{
            return json(['code'=>300]);
        }
    }

    /**
     * 邮箱确认
     * @return \think\response\Json
     */
    public function sure(){
        try{

            $username = input('param.name');
            $password = input('param.password');
            $check_code = input('param.code');

            //验签
//            $time = input('param.sign');
//            if(strtotime($time)>strtotime('+3 minute')){
//                throw new \Exception('签名失效');
//            }

            //员工校验
            $staff = Staff::where(['name'=>$username,'enable'=>1])->find();
            if(empty($staff)){
                throw new \Exception('该员工不存在或被禁用，请联系管理员');
            }
            if(encrypt_password($password)!=$staff->password){
                throw new \Exception('密码错误');
            }
            $code = Cache::get($staff->urgent);
            if($check_code!=$code){
                throw new \Exception('验证码不正确');
            }

            $num = $this->createNoncestr(8);
            session('user',$staff);
            cache($staff->name,$num,3600);

            return json(['code'=>200,'message'=>'登录成功','name'=>$staff->name,'num'=>$num]);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }

    public function authentication(){
        try{
            $id = input('param.id',0);
            $num = $this->createNoncestr(8);
            $time = input('param.sign');

            if(strtotime($time)>strtotime('+3 minute')){
                throw new \Exception('签名失效');
            }

            $re = Staff::get($id);

            if(empty($re)){
                throw new \Exception('员工不存在');
            }
            $re['ids']=Role::where('id',$re['job'])->value('ids');
            session('user',$re);
            cache($re['name'],$num,3600);
            return json(['code'=>200,'num'=>$num,'re'=>$re]);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
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
     * 邮箱发送验证码
     * @return \think\response\Json
     */
    public function email(){
        try{
            $name = input('param.name');
            $staff = Staff::where(['name'=>$name,'enable'=>1])->find();
            if(empty($staff)){
                throw new \Exception( '账号不存在或被禁用，请联系管理员');
            }
            $password = input('param.password');
            if($staff->password!=encrypt_password($password)){
                throw new \Exception( '请输入正确的账号或密码');
            }
            $email = input('param.email');
            if(!isEmail($email)){
                throw new \Exception('邮箱格式不正确');
            }
            if($staff->email!=$email){
                throw new \Exception('邮箱已经绑定，绑定邮箱为'.$staff->email);
            }
            $code = mt_rand(100000,999999);//验证码
            $register_time = cache($staff->urgent.'time') ? : 0;

            //一分钟频率限制
            if (time() - $register_time < 60) {
              throw new \Exception('发送太频繁，稍后再试');
            }

            $title = 'ERP登录验证码';
            $content = '您的验证码是【'.$code."】，请迅速填写，30分钟内有效！";
            $res = EmailSend::getInstance()->send($email,$name,$title,$content);
            if(empty($res)){
                Cache::set($staff->urgent,$code,60*30);//30分钟有效
            }else{
                throw new \Exception('邮件发送失败:'.$res);
            }
            //一分钟有效
            Cache::set($staff->urgent.'time',time(),60);
            $staff->email = trim($email);
            if(!$staff->save()){
                throw new \Exception($staff->getError());
            }

            return json(['code'=>200,'message'=>'验证码已发送，会有延迟，请耐心等待']);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 获得email
     * @return \think\response\Json
     */
    public function getemail(){
        try{
            $name = input('param.name');
            $staff = Staff::where(['name'=>$name,'enable'=>1])->find();
            if(empty($staff)){
                throw new \Exception( '账号不存在或被禁用，请联系管理员');
            }

            if(empty($staff->email)){
                throw new \Exception('邮箱未绑定');
            }

            return json(['code'=>200,'message'=>'获取成功','email'=>$staff->email]);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
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
