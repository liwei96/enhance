<?php


/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/21
 * Time: 10:27
 */

namespace app\api\controller;


use app\api\model\Area;
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

            if(empty($staffid)||empty($userid)){
                throw new \Exception('参数错误');
            }

            $timestamp = input('param.sign');
            if(date('Y-m-d',$timestamp) != date('Y-m-d')){
                throw new \Exception('加密验签错误');
            }

            $result = User::update([
                's_id' => $staffid,
                'sid'=>$staffid
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
        //所有的负责人
        $lstCharge = Db::query("SELECT s.id,s.name as username,q.name as city ,q.id as cit_id
FROM erp.erp_staff s INNER JOIN erp.erp_role  r on s.job=r.id and urgent !='' 
and s.id in(63,69,77,87,91,96,74) INNER JOIN erp.erp_qu q on q.id = s.area");

        if(!empty($project_id)) {   //项目
            $building = Building::get($project_id);
            foreach ($lstCharge as $item){
                if(strpos($building->branch,'宁波')!==false&&$item['city']=='萧山'){//宁波地区分配给萧山
                    return $item['id'];
                }

                if(strpos($building->branch,$item['city'])!==false){//
                    return $item['id'];
                }
            }
        }else if(!empty($city_id)){//区域
            foreach ($lstCharge as $item){
                if($item['cit_id']==$city_id){//
                    return $item['id'];
                }
            }
        }else if(!empty($city)) {//城市
            switch ($city) {
                case 1://杭州
                    $zone_obj = Db::query("SELECT q.`name`
FROM erp.erp_distribute e 
INNER join erp.erp_user u on e.userid = u.id and u.status=1
LEFT JOIN erp.erp_building b on b.id=e.poroject_id
LEFT JOIN erp.erp_staff s on e.charge_id=s.id
LEFT JOIN erp.erp_staff s1 on s1.id=e.register_id
LEFT JOIN erp.erp_area a on a.id=e.city_id 
LEFT JOIN erp.erp_qu q on q.id=s.area 
where 1=1 and q.`name`in('城东','城西','萧山') order by e.id DESC limit 1");
                    $zone_name = current($zone_obj)['name'];

                    if($zone_name == '城西'){
                        $zone_name = '城东';
                    }else if($zone_name == '城东'){
                        $zone_name ='萧山';
                    }else{
                        $zone_name ='城西';
                    }

                    foreach ($lstCharge as $item) {
                        if (strpos($zone_name, $item['city']) !== false) {//
                            return $item['id'];
                        }
                    }
                    break;
                case 3://贵阳
                    foreach ($lstCharge as $item) {
                        if ($item['city'] == '贵阳') {
                            return $item['id'];
                        }
                    }
                    break;
                case 36://宁波 的暂时给萧山的
                    foreach ($lstCharge as $item) {
                        if ($item['city'] == '萧山') {
                            return $item['id'];
                        }
                    }
                    break;
                case 38://嘉兴 分配给城东
                    foreach ($lstCharge as $item) {
                        if ($item['city'] == '城东') {
                            return $item['id'];
                        }
                    }
                    break;
                case 41://重庆
                    foreach ($lstCharge as $item) {
                        if ($item['city'] == '重庆') {
                            return $item['id'];
                        }
                    }
                    break;
                case 47://成都
                    foreach ($lstCharge as $item) {
                        if ($item['city'] == '成都') {
                            return $item['id'];
                        }
                    }
                    break;

                case 73://绍兴 分配给萧山
                    foreach ($lstCharge as $item) {
                        if ($item['city'] == '萧山') {
                            return $item['id'];
                        }
                    }
                    break;
                default:
                    throw new \Exception('未在约定的城市范围');
                    break;
            }
        }else{
            throw new \Exception('未在约定的城市范围');
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
        $channel = input('param.channel','');//渠道
        $remark = input('param.remark');//备注
        $phone = trim(input('param.phone'));//客户姓名
        $input_time = input('param.input_time',date('Y-m-d H:i:s'));//进客时间
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

            $send_flag = true;

            $before = '';

            $last_one = Db::query(" SELECT d.* FROM erp.erp_distribute d INNER JOIN erp.erp_user u on u.id=d.userid and u.status=1 and  u.tel='{$phone}' 
 and  d.create_time>='".date('Y-m-d',strtotime($input_time))."' and d.create_time<'".date('Y-m-d',strtotime('+1 day',strtotime($input_time)))."'  ");
            if(!empty($last_one)){
                $last_one = current($last_one);
                //同一区域
                $last_charge_id = $last_one['charge_id'];


                if($last_charge_id == $staff_id){
                    //同一天内相同手机号相同主管就不需要发邮件
                    $send_flag = false;
                }else  if(in_array($last_charge_id,[95,96,97,69,87])&&in_array($staff_id,[95,96,97,69,87])&& $project_id==0){
                    //没有项目相同城市的归属上一个主管，不发邮件
                    $send_flag = false;
                    $staff_id = $last_charge_id;
                }
                $customer = User::get($last_one['userid']);

                $before = $last_one['finish']==1?"该客户之前有被分配":'该客户尚未被分配';
                if($last_one['finish']==1){
                    if(empty($customer->sid)){
                        $before = $before."，该客户被拉为了公客";
                    }else{
                        $child = Staff::get($customer->sid);
                        if(!empty($child)){
                            $before = $before."，该客户被分配给了".$child->name;
                        }
                    }
                }
            }

            $staff = Staff::get($staff_id);
            if(empty($staff)||empty($staff->email)){
                throw new \Exception('业务主管'.$staff->name.'未绑定邮箱，无法通知');
            }
            $staff_name = $staff->name;
            $email = $staff->email;
            $user_id = 0;
            if($send_flag){
                $user = new User;
                $user->name = $username;
                $user->project = $project_id;
                $user->tel = $phone;
                $user->area = '';
                $user->port = $source;
                $user->time = date('Y-m-d');
                $user->s_id= $enable?$staff_id:0;//无效录为公客
                if(!$enable){
                    $user->grade = 'D类';
                }
                $user->sid = $staff_id;
                $user->remark = $remark;
                $user->update_time = time();
                $user->create_time =empty($input_time)? time():strtotime($input_time);
                if(!$user->save()){
                    throw new \Exception($user->getError());
                }
                $user_id = $user->id;
            }else{
                $user = null;
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
                'create_time'=>empty($input_time)? date('Y-m-d H:i:s'):$input_time,
                'register_id'=>$reg_id,
                'userid'=> empty($user)||!$send_flag?0:$user_id,//不发邮件的不在客户列表展示
                'city_id'=>$city_id,
                'channel'=>$channel,
                'sure'=>$enable?0:1,
                'area_id'=>$area_id??0
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

            $url = "http://api.jy1980.com/index.php/distribute/sure?userid=".$user_id;

            $date_time = date('Y-m-d');
            $is_sure =Db::connect(config('database.db_config1'))
                ->query("select phone FROM tpshop.tpshop_port1 where is_toke=1 and FROM_UNIXTIME(create_time,'%Y-%m-%d')='$date_time' and phone='{$phone}'");

            //发邮件
            if($send_flag){
                $content ="尊敬的【{$staff_name}】经理：<br/>".
                    "这里有一个客户需要您处理。<br/>" .
                    "姓名：{$username}，<br/>".
                    "电话：{$phone}，<br/>"
                    ."进客来源：{$source}，<br/>"
                    ."项目名称：{$building_name}，<br/>".
                    "项目区域：{$branch}，<br/>".
                    "备注：{$remark}<br/>请登录erp系统尽快分配。<br>".
                    "时间：".$input_time."<br/>".
                    "收到点击<a href='{$url}'>确认</a>的长得帅<br>";

                if(in_array($source,['线上推广1','线上推广2'])){
                    if(!empty($is_sure)){
                        $content = $content."客户验证码:已验证";
                    }else{
                        $content = $content."客户验证码:未验证";
                    }
                }

                if($enable){
                    $res = \EmailSend::getInstance()->send($email,$staff_name,'ERP系统邮件',$content);
                    if(!empty($res)){
                        throw new \Exception($res);
                    }
                }
            }else{
                $content ="尊敬的{$staff_name}经理：<br/>".
                    "这里有一个客户需要您处理。<br/>" .
                    "姓名：{$username}，<br/>".
                    "电话：{$phone}，<br/>"
                    ."进客来源：{$source}，<br/>"
                    ."项目名称：{$building_name}，<br/>".
                    "项目区域：{$branch}，<br/>".
                    "备注：{$remark}<br/>".
                    "提醒：".$before."<br/>";
                if(false){
                    $res = \EmailSend::getInstance()->send($email,$staff_name,'ERP系统邮件',$content);
                    if(!empty($res)){
                        throw new \Exception($res);
                    }
                }
            }

            model('Distribute')::update(['content'=>$content],['id'=>$distribute->id]);
            if(!$enable||$city_id == 36){
                Db::execute(" UPDATE erp_distribute set finish=1 where id =$distribute->id ");
            }
            Db::execute(" UPDATE erp.erp_distribute set finish=1 and sure=1 where userid=0 or `enable`=0 ");
            return json(['code'=>true,'message'=>'已经发送邮件给业务经理']);
        }catch (\Exception $e){
            return json(['code'=>false,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 定时提醒客服
     */
    public function interval(){
        //帮忙同步时间
        Db::execute("UPDATE erp.erp_distribute d INNER JOIN erp.erp_user u on d.userid = u.id  set d.create_time = from_unixtime(u.create_time)");
        $lst = Db::query("select * FROM erp.erp_distribute where sure = 0 and userid!=0");
        if(!empty($lst)&&count($lst)>0){
            $lst_mobile = array_column($lst,'mobile');
            $mobiles = implode(',',$lst_mobile);
            if(date('H')>8){
                $content = "尊敬的客服组长您好。客服系统存在客户未被确认,请尽快联系客户经理，客户手机号是$mobiles"."请点击 <a href='http://mrw.so/4BWVBE'>http://mrw.so/4BWVBE</a>查看详情";
                $res = \EmailSend::getInstance()->send('shixiuying@edefang.net','客服组长石秀英','ERP系统邮件',$content);
            }
        }

        $datas = Db::query("SELECT * FROM erp.erp_distribute  where content is null and userid>0 and sure!=1");
        foreach ($datas as $item){
            $url = "http://api.jy1980.com/index.php/distribute/sure?userid=".$item['userid'];
            $staff = Staff::get($item['charge_id']);

            $staff_name =  Staff::where('id',$item['charge_id'])->value('name');
            $building_name = Building::where('id',$item['poroject_id'])->value('building_name')??'未知';
            $branch =  Building::where('id',$item['poroject_id'])->value('branch')??'未知';
            $content ="您好！{$staff_name}经理：<br/>".
                "这里有一个客户需要您处理。<br/>" .
                "姓名：{$item['customer_name']}，<br/>".
                "电话：{$item['mobile']}，<br/>"
                ."进客来源：{$item['source']}，<br/>"
                ."项目名称：{$building_name}，<br/>".
                "项目区域：{$branch}，<br/>".
                "备注：{$item['remark']}<br/>请登录erp系统尽快分配。<br>".
                "收到请点击<a href='{$url}'>确认</a>";


            $res = \EmailSend::getInstance()->send($staff['email'],$staff['email'],'ERP系统邮件',$content);
            if(!empty($res)){
                return $content;
            }else{
                model('Distribute')::update(['content'=>$content],['id'=>$item['id']]);
            }

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