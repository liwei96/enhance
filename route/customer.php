<?php
/**
 * 客户管理路由
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/14
 * Time: 15:38
 */

use think\facade\Route;

Route::group('api',[
    'user/recover'=>'api/user/recover', //删除客户恢复
]);

Route::group('user',[
    'index'=>'api/user/sou',
    'save'=>'api/user/save',
    'edit'=>'api/user/edit',
    'update'=>'api/user/update',
    'delete'=>'api/user/delete',
    'tong'=>'api/user/tong',
    'lists'=>'api/user/lists',
    'area'=>'api/user/area',
    'sou'=>'api/user/sou',
    'like'=>'api/user/like',
    'type'=>'api/user/type',
    'zsave'=>'api/user/zsave',
    'qedit'=>'api/user/qedit',
    'qsave'=>'api/user/qsave',
    'qupdate'=>'api/user/qupdate',
    'qdelete'=>'api/user/qdelete',
    'hsave'=>'api/user/hsave',
    'hedit'=>'api/user/hedit',
    'hupdate'=>'api/user/hupdate',
    'hdelete'=>'api/user/hdelete',
    'hindex'=>'api/user/hindex',
    'qindex'=>'api/user/qindex',
    'xiang'=>'api/user/xiang',
    'bing'=>'api/user/bing',
    'changes'=>'api/user/changes',
    'changeg'=>'api/user/changeg',
    'change'=>'api/user/change',
    'get'=>'api/user/get',
    'changed'=>'api/user/changed',
    'g'=>'api/user/g',
    'sous'=>'api/user/sous',
    'lsou'=>'api/user/lsou',
    'gsou'=>'api/user/gsou',
    'tonglist'=>'api/user/tonglist',

    'dengfen'=>'api/user/dengfen',
])->middleware('check');