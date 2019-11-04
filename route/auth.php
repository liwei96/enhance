<?php
/**
 * 权限相关路由
 * Created by PhpStorm.
 * User: wx
 * Date: 2019/10/14
 * Time: 15:34
 */

use think\facade\Route;

Route::group('login',[
    'getcode'=>'api/login/getcode',//erp后台获取验证码
    'login'=>'api/login/login',//erp后台用户登录
    'logout'=>'api/login/logout', //erp后台登出
    'authentication'=>'api/login/authentication',
    'email'=>'api/login/email',
    'sure'=>'api/login/sure',
    'getemail'=>'api/login/getemail',
    'weichatjudge'=>'api/weichat/bindingjudge',
    'weichatsure'=>'api/weichat/sure'
]);


Route::group('role',[
    'index'=>'api/role/index',//岗位首页接口
    'save'=>'api/role/save',
    'edit'=>'api/role/edit',
    'update'=>'api/role/update',
    'delete'=>'api/role/delete',
    'fen'=>'api/role/fen',
    'list'=>'api/role/listSelect'
])->middleware('check');