<?php
/**
 * 资源导出路由
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/14
 * Time: 15:10
 */

use think\facade\Route;
//excel导出
Route::rule('export','export/index');
Route::rule('sdf','export/create');

