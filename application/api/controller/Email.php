<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/15
 * Time: 14:16
 */

namespace app\api\controller;
use EmailSend;

class Email
{
    /**
     *发邮件接口
     * @return \think\response\Json
     */
    public function send(){
        try{
            $email = strtolower(input('param.email'));
            $name = input('param.name');
            $title = input('param.title');
            $content = input('param.content');
           
            $res = EmailSend::getInstance()->send($email,$name,$title,$content);
            if(!empty($res)){
                throw new \Exception($res);
            }
            return json(['code'=>200,'message'=>'发送成功']);

        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
   }
}