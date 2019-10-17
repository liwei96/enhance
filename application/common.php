<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('encrypt_password')){
    function encrypt_password($pwd){
        $salt='dasadjfh9u8jdfakf';
        return md5(md5($pwd).$salt);
    }


}
/**
 * 是否是微信浏览器
 */
if(!function_exists('is_weixin')){
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }
}
/**
 * 是否是ip白名单 add by wangxu
 */
if(!function_exists('in_ip_whitelist')){
    function in_ip_whitelist($ip){
        $whitelist = config('app.white_list_ip');
        return in_array($ip,$whitelist);
    }
}

if(!function_exists('sc_send')){
    function sc_send(  $text , $desp = '' , $key = 'SCU50524T5032b1d27716dc19320d16abf705a7e35ccbecdea6ed2'  )
    {
        $postdata = http_build_query(
            array(
                'text' => $text,
                'desp' => $desp
            )
        );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context  = stream_context_create($opts);
        return $result = file_get_contents('https://sc.ftqq.com/'.$key.'.send', false, $context);

    }
}
if(!function_exists('juhecurl')){
    function juhecurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }
}
if(!function_exists('sendmsg')){
    function sendmsg($phone,$msg){
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL

        $smsConf = array(
            'key'   => 'a7289630cca0320602d260376d297abd', //您申请的APPKEY
            'mobile'    => $phone, //接受短信的用户手机号码
            'tpl_id'    => '174725', //您申请的短信模板ID，根据实际情况修改
            'tpl_value' =>"#code#=$msg" //您设置的模板变量，根据实际情况修改
        );
        $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信

        if($content){
            $result = json_decode($content,true);
            $error_code = $result['error_code'];
            if($error_code == 0){
                //状态为0，说明短信发送成功
                return true;
            }else{
                //状态非0，说明失败
                return false;
            }
        }else{
            //返回内容异常，以下可根据业务逻辑自行修改
            return "请求发送短信失败";
        }

    }
}

if(!function_exists('getids')){
    function getids($data){
        static $ids=[];

        foreach($data as $v){
            $dd=Staff::where('pid',$v['id'])->select();
            if($dd){
                getids($v['id']);
            }else{
                $ids[]=$v['id'];
            }
        }
        return $ids;
    }
}

