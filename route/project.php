<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/14
 * Time: 16:06
 */

use think\facade\Route;
Route::group('pro',['img'=>'api/project/img']);
Route::group('pro',['imgapi'=>'api/project/img_api']);
Route::group('pro',['wlists'=>'api/project/wlists']);
Route::group('pro',['wdongs'=>'api/project/wdongs']);
Route::group('pro',['tongs'=>'api/project/tongs']);
Route::group('pro',['bdongs'=>'api/project/bdongs']);
Route::group('pro',[
    'create'=>'api/project/create',
    'update'=>'api/project/update',
    'delete'=>'api/project/delete',
    'index'=>'api/project/index',//项目信息列表首页

    'hetong'=>'api/project/upload_hetong',
    'save'=>'api/project/save',
    'saveimgs'=>'api/project/saveimgs',
    'edit'=>'api/project/edit',
    'delpics'=>'api/project/delpics',
    'updatetext'=>'api/project/updatetext',
    'tui'=>'api/project/tui',
    'test'=>'api/project/test',
    'img'=>'api/project/img',
    'getareas'=>'api/project/getareas',
    'ones'=>'api/project/ones',
    'tuitong'=>'api/project/tuitong',
    'xiatong'=>'api/project/xiatong',
    'list'=>'api/project/list',
    'sou'=>'api/project/sou',
    'tsou'=>'api/project/tsou',
    'dlist'=>'api/project/dlist',
    'clist'=>'api/project/clist',
    'tuisou'=>'api/project/tuisou',
    'tong'=>'api/project/tong',
    'xiatong'=>'api/project/xiatong',
    'tuitong'=>'api/project/tuitong',
    'shen'=>'api/project/shen',
    'updatex'=>'api/project/updatex',
    'isdeng'=>'api/project/isdeng',
    'editpic'=>'api/project/editpic',
    
])->middleware('check');