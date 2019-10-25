<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/21
 * Time: 11:07
 */

use think\facade\Route;
//获取所有的项目
Route::rule('/distribute/projects','api/distribute/projects');
//获取所有员工
Route::rule('/distribute/register','api/distribute/register');
//获取被分配的业务员
Route::rule('/distribute/distributions','api/distribute/distributions');
//发送邮件通知主管
Route::rule('/distribute/send','api/distribute/send');
//分配员工给客户
Route::rule('/distribute/delegate','api/distribute/delegate');
//发信息
Route::rule('/distribute/send','api/distribute/send');
//获取区域
Route::rule('/distribute/cities','api/distribute/cities');


