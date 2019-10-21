<?php

namespace app\api\model;

use think\Model;

class Staff extends Model
{
    public static function findSubordinates($userId = 0){
        $idArr = [$userId];
        $userId = [$userId];
        $ids = null;
        do{
            $ids = self::where(" pid in (".implode(',',$userId).")")->column('id');

            if(!empty($ids) && count($ids)>0){
                $userId = $ids ;
                $idArr = array_merge($idArr,$ids);
            }
        }while(!empty($ids) && count($ids)>0);
        return $idArr;
    }
}
