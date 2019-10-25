<?php
/**
 * 测试路由
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/14
 * Time: 15:12
 */
use think\facade\Route;
//redis测试连接
Route::rule('/api/demo/redis','api/demo/redis');
Route::rule('/api/demo/email','api/demo/email');
//thinkphp5首页
Route::rule('/','index/index');