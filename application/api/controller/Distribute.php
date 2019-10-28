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
use app\api\model\Distribute as DistributeModel;
use think\Validate;

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
            $charge_id = input('param.chargeid',0);

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

            $result = User::update([
                's_id' => $staffid
            ],['id' => $userid]);

            if(!$result){
                throw new \Exception('分配失败');
            }

            DistributeModel::update(['finish'=>1],['userid'=>$userid]);

            return json(['code'=>true,'message'=>'客户已经分配成功']);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }


    /**
     * 查找主管
     * @param int $project_id 项目id
     * @param int $city_id 区域id
     * @param int $city 城市
     * @return int
     * @throws \Exception
     */
    private function findCharge($project_id = 0,$city_id = 0,$city=0){
        //宁波地区写死是陶泽威
        if($city == 36){
            return 114;
        }

        $areaCharges = [];//竞争队列
        if(!empty($project_id)){   //项目
            $lstCharge = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area ");
            $building = Building::get($project_id);
            foreach ($lstCharge as $item){
                if(strpos($building->branch,$item['city'])!==false){//如果项目跟业务经理区域一样，加入竞争队列
                    $areaCharges[] = $item;
                }
            }
        }elseif (!empty($city_id)){//区域 如城东 城西
            $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.id={$city_id} ");
        }elseif (!empty($city)){
            switch ($city){
                case 1://杭州
                    $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.name in('城东','城西','萧山')");
                    break;
                case 3://贵阳
                    $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.name='贵阳' ");
                    break;
                case 36://宁波 分配给陶泽威
                    break;
                case 38://嘉兴 分配给城东
                    $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.name='城东' ");
                    break;
                case 41://重庆
                    $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.name='重庆' ");
                    break;
                case 73://绍兴 分配给萧山
                    $areaCharges = Db::query("SELECT s.id,s.name as username,r.name as rolename,s.urgent,q.name as city 
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' and r.id=28 
INNER JOIN erp.erp_qu q on q.id = s.area and q.name='萧山' ");
                    break;
                default:
                    throw new \Exception('未在约定的城市范围');
                    break;
            }
        }else{
            throw new \Exception('项目、区域、城市参数全都没有传');
        }

        //最少的拿客户
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
     * 确认邮件收到
     * @return string
     */
    public function sure(){
        $userid = input('param.userid');
        Db::execute(" UPDATE erp_distribute set sure=1 where userid =$userid ");
        return "<h1>谢谢您的确认</h1>";
    }

    /**
     * 发送邮件通知主管
     * @return \think\response\Json
     */
    public function send(){
        $area_id = input('param.area_id',0);//区域id
        $timestamp = input('param.sign');//验签
        $enable = input('param.enable',1);//1有效 0无效
        $username = input('param.username'); //用户名
        $source = input('param.source');//来源
        $reg_id = input('param.reg_id',0);//登记人
        $remark = input('param.remark');//备注
        $phone = input('param.phone');//客户姓名
        $project_id = input('param.project',0);//项目id
        $city_id = input('param.city_id',1);//城市 id  SELECT id,area_name FROM erp.erp_area  where pid in(66,68,67)
        try{

            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }
            $rule = [
                'username'  => 'require',
                'phone'   => 'require',
                'source' => 'require',
                'remark' => 'require',
            ];
            $msg = [
                'username.require' => '客户名称必填',
                'phone.require'   => '电话未填写',
                'source.require'  => '进客来源未填写',
                'remark.require'  => '备注未填写',
            ];
            $data = input('param.');
            $validate = new Validate($rule,$msg);
            $result = $validate->check($data);
            if(!$result){
                throw  new \Exception($validate->getError());
            }

            //选择主管
            $staff_id = $this->findCharge($project_id,$area_id,$city_id);
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
            $user->s_id= $enable?$staff_id:0;//无效录为公客
            $user->sid = $staff_id;
            $user->remark = $remark;
            $user->update_time = time();
            $user->create_time = time();
            if(!$user->save()){
                throw new \Exception($user->getError());
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
                'userid'=>empty($user)?0:$user->id,
                'city_id'=>$city_id
            ]);
            if(!$distribute->save()){
                throw new \Exception($distribute->getError());
            }
            $building_name = '未知';
            $branch = '未知';
            if(!empty($project_id)){
                $building_name =  Building::where('id',$project_id)->value('building_name');
                $branch =  Building::where('id',$project_id)->value('branch');
            }

            $url = "http://api.jy1980.com/index.php/distribute/sure?userid=".$user->id;

            //发邮件
            $content ="您好！{$staff_name}经理：<br/>".
                "这里有一个客户需要您处理。<br/>" .
                "姓名：{$username}，<br/>".
                "电话：{$phone}，<br/>"
                ."进客来源：{$source}，<br/>"
                ."项目名称：{$building_name}，<br/>".
                "项目区域：{$branch}，<br/>".
                "备注：{$remark}<br/>请登录erp系统尽快分配。<br>".
                "收到请点击<a href='{$url}'>确认</a>";

            $res = \EmailSend::getInstance()->send($email,$staff_name,'ERP系统邮件',$content);
            if(!empty($res)){
                throw new \Exception($res);
            }
            model('Distribute')::update(['content'=>$content],['id'=>$distribute->id]);

            if(!$enable||$city_id == 36){
                Db::execute(" UPDATE erp_distribute set finish=1 where id =$distribute->id ");
            }

            return json(['code'=>true,'message'=>'已经发送邮件给业务经理']);
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

    public function index(){
        $sources = ['线上推广1','线上推广2','线上电话1','线上电话2','推广4','电话4','自主','咨询','新媒体','家有会员','家园会员','百度文库','留言'];
        $buildings = Db::query(" SELECT id,building_name as name FROM erp.erp_building ");
        $first_levels = Db::query("SELECT id,pid,name FROM erp.erp_qu where pid=0");
        $zones = Db::query("SELECT id,name,pid FROM erp.erp_qu ");//区域
        $zones = json_encode($zones);

        return view('',compact('sources','buildings','first_levels','zones'));

    }
}