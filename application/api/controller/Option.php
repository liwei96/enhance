<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/31
 * Time: 14:20
 */

namespace app\api\controller;


use think\Controller;
use think\Db;

class Option extends Controller
{

    /**
     * 获取进客来源
     * @return \think\response\Json
     */
    public function sources(){
        $lst = Db::query("SELECT `key`,`value` from erp.erp_options WHERE type=1");
        return json($lst);
    }
}