<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

Route::rule('/test','api/index/checkdong');
Route::rule('/pro/create','api/project/create');
Route::rule('/index/checkdong','api/index/checkdong');


Route::group('tuan',[
    'index'=>'api/tuan/edit',
    'save'=>'api/tuan/update',
])->middleware('check');
Route::group('info',[
    'index'=>'api/information/index',
    'news'=>'api/information/news',
    'create'=>'api/information/create',
    'save'=>'api/information/save',
    'nsave'=>'api/information/nsave',
    'psou'=>'api/information/psou', 
    'nsou'=>'api/information/nsou', 
    'edit'=>'api/information/edit',
    'nedit'=>'api/information/nedit',
    'update'=>'api/information/update',
    'nupdate'=>'api/information/nupdate',
    'delete'=>'api/information/delete',
    'ndelete'=>'api/information/ndelete',
    'ntong'=>'api/information/ntong',
    'ptong'=>'api/information/ptong'
])->middleware('check');
Route::group('guide',[
    'index'=>'api/guide/index',
    'save'=>'api/guide/save',
    'edit'=>'api/guide/edit',
    'update'=>'api/guide/update',
    'delete'=>'api/guide/delete',
    'tong'=>'api/guide/tong',
    'fus'=>'api/guide/fus'
])->middleware('check');




Route::group('dai',[
    'index'=>'api/dai/index',
    'save'=>'api/dai/save',
    'edit'=>'api/dai/edit',
    'update'=>'api/dai/update',
    'delete'=>'api/dai/delete',
    'list'=>'api/dai/list',
    'tong'=>'api/dai/tong',
    'duser'=>'api/dai/duser',
    'dusersou'=>'api/dai/dusersou',
    'dusertype'=>'api/dai/dusertype',
    'utong'=>'api/dai/utong',
    'tonglist'=>'api/dai/tonglist',
    'read'=>'api/dai/read',
    'reimg'=>'api/dai/reimg',
    'tongdailist'=>'api/dai/tongdailist'
])->middleware('check');
Route::group('gen',[
    'index'=>'api/gen/index',
    'save'=>'api/gen/save',
    'edit'=>'api/gen/edit',
    'update'=>'api/gen/update',
    'delete'=>'api/gen/delete',
    'tong'=>'api/gen/tong',
    'tonglist'=>'api/gen/tonglist',
    'diangen'=>'api/gen/diangen',
    'tellist'=>'api/gen/tellist',
    'telsou'=>'api/gen/telsou',
            'tonggenlist'=>'api/gen/tonggenlist'
])->middleware('check');
Route::group('tupai',[
    'index'=>'api/tupai/index',
    'save'=>'api/tupai/save',
    'edit'=>'api/tupai/edit',
    'update'=>'api/tupai/update',
    'delete'=>'api/tupai/delete'
])->middleware('check');
Route::group('recording',[
    'index'=>'api/recording/index',
    'save'=>'api/recording/save',
    'edit'=>'api/recording/edit',
    'update'=>'api/recording/update',
    'delete'=>'api/recording/delete',
    'tong'=>'api/recording/tong',
    'recordProject'=>'api/recording/recordProject',
    'recordUser'=>'api/recording/recordUser',
    'recordUserSou'=>'api/recording/recordUserSou',

    'recordUserType'=>'api/recording/recordUserType',
    'uindex'=>'api/recording/uindex',
    'delpic'=>'api/recording/delpic',
    'recordProjectDai'=>'api/recording/recordProjectDai',
    'recordProjects'=>'api/recording/recordProjects',
    'recordProjectSou'=>'api/recording/recordProjectSou',
    'project'=>'api/recording/project',
    'projectSou'=>'api/recording/projectSou',
            'tongrelist'=>'api/recording/tongrelist'
])->middleware('check');
Route::group('staff',[
    'index'=>'api/staff/index',
    'save'=>'api/staff/save',
    'edit'=>'api/staff/edit',
    'update'=>'api/staff/update',
    'delete'=>'api/staff/delete',
    'list'=>'api/staff/list',
    'my'=>'api/staff/my',
    'type'=>'api/staff/type',
    'sou'=>'api/staff/sou',
    'gang'=>'api/staff/gang',
    'change'=>'api/staff/change'
])->middleware('check');
Route::group('zhi',[
    'index'=>'api/zhi/index',
    'sou'=>'api/zhi/sou',
    'save'=>'api/zhi/save',
    'edit'=>'api/zhi/edit',
    'update'=>'api/zhi/update',
    'ping'=>'api/zhi/save_comment',
	'comments'=>'api/zhi/comments',
    'delete/comment'=>'api/zhi/deletecomment',
	'fuze'=>'api/zhi/fuze'
])->middleware('check');
Route::group('index',[
    'index'=>'api/index/index',
    'out'=>'api/index/out',
    'list'=>'api/index/list',
    'sou'=>'api/index/sou',
    'lou'=>'api/index/lou',
    'jinfen'=>'api/index/jinfen',
    'zhong'=>'api/index/zhong',
    
])->middleware('check');
Route::group('integral',[
    'index'=>'api/integral/index',
    'read'=>'api/integral/read',
    'sou'=>'api/integral/sou',
    'read'=>'api/integral/read',
    'delete'=>'api/integral/delete'
])->middleware('check');
Route::group('fen',[
    'index'=>'api/fen/edit',
    'update'=>'api/fen/update',
])->middleware('check');
Route::group('qu',[
    'one'=>'api/qu/one',
    'two'=>'api/qu/two',
    'peo'=>'api/qu/peo'
])->middleware('check');

