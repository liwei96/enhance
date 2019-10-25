<?php


/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/21
 * Time: 10:27
 */

namespace app\api\controller;


use app\api\model\Building;
use think\App;
use think\Controller;
use think\Db;
use app\api\model\User;
use app\api\model\Staff;
class Distribute extends Controller
{
    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
    }

    /**
     * 获取项目信息
     * @return \think\response\Json
     */
    public function projects(){
        try{
            $timestamp = input('param.sign');
            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }
            $name = input('param.name','');
            $where = '';
            if(!empty($name)){
                $where = " WHERE building_name like '%$name%' ";
            }
            $buildings = Db::query(" SELECT id,building_name as name FROM erp.erp_building $where ");
            return json(['code'=>true,'message'=>'获取成功','data'=>$buildings]);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 获取员工信息
     * @return \think\response\Json
     */
    public function register(){
        try{
            $timestamp = input('param.sign');
            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }
            $name = input('param.name','');
            $where = ' ';
            if(!empty($name)){
                $where = " and name like '%$name%' ";
            }
            $staffs = Db::query(" SELECT id,`name`  FROM erp.erp_staff where urgent!='' and job=32 $where ");
            return json(['code'=>true,'message'=>'获取成功','data'=>$staffs]);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 获取要被分配的业务员
     * @return \think\response\Json
     */
    public function distributions(){
        try{
            $timestamp = input('param.sign');
            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }
            $charge_id = input('param.chargeid');
            $mates = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' 
INNER JOIN erp.erp_qu q on q.id = s.area and ( s.pid= $charge_id or s.pid in(SELECT id FROM erp.erp_staff where pid = $charge_id ) or s.id = $charge_id)");
            return json(['code'=>true,'message'=>'操作成功','data'=>$mates]);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 分配员工给客户
     * @return \think\response\Json
     */
    public function delegate(){
        try{
            $userid = input('param.userid');
            $staffid = input('param.staffid');

            $timestamp = input('param.sign');
            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }

            $user = new User;
            $result = $user->save([
                's_id' => $staffid
            ],['id' => $userid]);

            if(!$result){
                throw new \Exception('分配失败');
            }

            return json(['code'=>true,'message'=>'客户已经分配成功']);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }


    /**
     * 查找主管
     * @param $project_id
     * @return mixed
     */
    private function findCharge($project_id = 0,$city_id = 0){
        //选择区域项目经理
        $areaCharges = [];
        if(empty($project_id)&&!empty($city_id)){
            $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.id={$city_id} ");
        }else{
            //所有的项目经理
            $lstCharge = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area ");
            $building = Building::get($project_id);
            foreach ($lstCharge as $item){
                if(strpos($building->branch,$item['city'])!==false){
                    $areaCharges[] = $item;
                }
            }
        }

        if(count($areaCharges)==1){
            return ($areaCharges)[0]['id'];
        }else if(count($areaCharges)>0){
            $min = Db::query("SELECT count(*) Num FROM erp.erp_distribute where charge_id =".$areaCharges[0]['id']);
            if(empty($min)){
                $min = 0;
            }else{
                $min = current($min)['Num'];
            }
            $id = 0;
            foreach ($areaCharges as $item){
                $num = Db::query("SELECT count(*) Num FROM erp.erp_distribute where charge_id =".$areaCharges[0]['id']);
                if(empty($num)){
                    $num = 0;
                }else{
                    $num = current($num)['Num'];
                }

                if($min >= $num){
                    $min = $num;
                    $id = $item['id'];
                }
            }
            return $id;
        }
    }

    /**
     * 发送邮件通知主管
     * @return \think\response\Json
     */
    public function send(){
        try{
            $area_id = input('param.area_id',0);//区域id 如果项目没有的话区域必填

            $timestamp = input('param.sign');
            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }

            $enable = input('param.enable',1);

            $username = input('param.username');
            if(empty($username)){
                throw new \Exception('客户姓名未填写');
            }
            $project_id = input('param.project',0);
            if(empty($project_id)&&empty($area_id)&&$enable){
                throw new \Exception('项目和区域必须选择一项');
            }

            $phone = input('param.phone');
            if(empty($phone)){
                throw new \Exception('电话未填写');
            }
            $source = input('param.source');
            if(empty($source)){
                throw new \Exception('进客来源未填写');
            }
            $reg_id = input('param.reg_id',0);
            if(!empty($reg_id)){
                throw new \Exception('登记人未选择');
            }

            $remark = input('param.remark');
            if(empty($remark)){
                throw new \Exception('备注未填写');
            }
            $staff_id = 0 ;
            $email = '';
            $staff_name ='';
            if($enable){
                $staff_id = $this->findCharge($project_id,$area_id);
                if(empty($staff_id)){
                    throw new \Exception('未匹配到对应业务主管');
                }

                $staff = Staff::get($staff_id);
                if(empty($staff)||empty($staff->email)){
                    throw new \Exception('业务主管'.$staff->name.'未绑定邮箱，无法通知');
                }
                $staff_name = $staff->name;
                $email = $staff->email;
                $user = new User;
                $user->name = $username;
                $user->project = $project_id;
                $user->tel = $phone;
                $user->area = '';
                $user->port = $source;
                $user->time = date('Y-m-d');
                $user->s_id= $staff_id;
                $user->sid = $reg_id;
                $user->remark = $remark;
                $user->update_time = time();
                $user->create_time = time();
                if(!$user->save()){
                    throw new \Exception($user->getError());
                }
            }
            $distribute = model('Distribute');
            $distribute->data([
                'customer_name'=>$username,
                'mobile'=>$phone,
                'source'=>$source,
                'poroject_id'=>$project_id,
                'enable'=>$enable,
                'remark'=>$remark,
                'charge_id'=>$staff_id,
                'register_id'=>$reg_id,
                'userid'=>$user->id
            ]);
            if(!$distribute->save()){
                throw new \Exception($distribute->getError());
            }

            if($enable){
                $content ="您好！{$staff_name}经理，这里有一个客户需要您处理，（姓名：{$username}，
            电话：{$phone}，进客来源：{$source}，）请登录erp系统尽快分配。";

                $res = \EmailSend::getInstance()->send($email,$staff_name,'您有一个客户待分配',$content);
                if(!empty($res)){
                    throw new \Exception($res);
                }
            }

            return json(['code'=>true,'message'=>$enable?'已经发送邮件给业务经理':'登记成功']);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 获取区域
     * @param int $parentid
     * @return \think\response\Json
     */
    public function cities($parentid=0){
        try{
            $lstArea =  Db::query("SELECT id,pid,name FROM erp.erp_qu where pid= {$parentid}");
            return json(['code'=>true,'message'=>'','data'=>$lstArea]);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage(),'data'=>[]]);
        }
    }
