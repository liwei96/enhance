<?php
/**
 * 微信api相关路由
 * Created by PhpStorm.
 * User: 王旭
 * Date: 2019/10/14
 * Time: 15:18
 */

use think\facade\Route;


Route::group('weichat',[
    '/validate_words'=>'api/weichat/validate_words' ,//网络敏感词检测
]);

Route::group('pro',[
'getarea'=>'api/project/getSubCate',//获取区域
]);