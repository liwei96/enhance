<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use app\api\model\User as UserModel;
use app\api\model\Staff;
use app\api\model\Shouhou;
use app\api\model\Shouqian;
use app\api\model\Shouzhong;
use app\api\model\Area;
use app\api\model\Building;
use app\api\model\Gen;

class User extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
     
        $t=request()->param();
        $n=$t['n'];
        $y=$t['y'];
        $y=$y-1;
        $userid = session('user.id');
        if(session('user.super')==1){
            $data=UserModel::where(" s_id!=0 and status !=0"  )->limit($n*$y,$n)->order('id','desc')->select();
            $num=UserModel::where(" s_id!=0 and status !=0"  )->count();
        }else if(session('user.guide')!=1){
            $ids=Staff::where('pid',$userid)->select();
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $data=UserModel::where(" s_id in(".implode(',',$ids)." ) and  status !=0 ")->limit($y*$n,$n)->order('id','desc')->select();
            $num=UserModel::where(" s_id in(".implode(',',$ids)." ) and  status !=0 ")->count('id');
        }else{
            $data=UserModel::where("s_id= $userid  and  status !=0 ")->order('id','desc')->limit($n*$y,$n)->select();
            $num=UserModel::where(" s_id= $userid  and  status !=0 ")->count();
        }
        foreach($data as $v){
            if(Building::where('id',$v['project'])->column('building_name')){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            }else{
                $v['project']='未定义';
            }
            $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            $v['isnow']=0;
            if($tt){
                if($v['s_id']==0){
                    $v['peo']='公客';
                }else{
                    $v['peo']='私客';
                }
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
                $g=strtotime($v['g_time']);
                $g=date('Y-m-d',$g);
                $now=date('Y-m-d',time());
                if($g==$now){
                    $v['isnow']=1;
                }
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['sid']=Staff::where('id',$v['sid'])->column('name');
            if($v['sid']){
                $v['sid']=$v['sid'][0];
            }

        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$num
        ];
        return json($res);
    }

    public function lists(){
        $list=Building::field("building_name,id")->select();
        return json(['code'=>200,'list'=>$list]);
    }
    public function area(){
        $list = Area::where('pid', 0)->select();

        $data = [];
        foreach ($list as $v) {
            $data[$v['area_name']] = Area::where('pid', $v['id'])->select();
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function changeg($id)
    {
        //
        UserModel::update(['s_id'=>0],['id'=>$id]);
        return json(['code'=>200]);
    }

    public function changes($id){
        UserModel::update(['s_id'=>session('user.id')],['id'=>$id]);
        return json(['code'=>200]);
    }

    public function change(){
        $yuans=Staff::where('guide',3)->field('id,name')->select();

        $res=[
            'code'=>200,
            'data'=>$yuans
        ];
        return json($res);
    }
    function gets($data){
        static $ids=[];

        foreach($data as $v){
            $dd=Staff::where('pid','eq',$v['id'])->select();
            if($dd){
                $this->gets($dd);
                $ids[]=$v['id'];
            }else{
                $ids[]=$v['id'];
            }
        }
        return $ids;
    }
    public function get($id){
        $data=Staff::where('pid',$id)->select();
        $ids=$this->gets($data);
        $ids[]=session('user.id');
        $id=[];
        foreach($ids as $v){
            $id[]=Staff::where('id',$v)->field('id,name')->find();
        }
        $res=[
            'code'=>200,
            'data'=>$id
        ];
        return json($res);
    }
    public function changed(){
        $data=request()->param();
        UserModel::update(['s_id'=>$data['s_id']],['id'=>$data['id']]);
        return json(['code'=>200]);
    }
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
        $data=$request->param()['value'];
        $data['leixing']=implode(',',$data['leixing']);
        $data['ceng']=implode(',',$data['ceng']);
        $dd=[];
        foreach($data['region'] as $v){
            $dd[]=Area::where('area_name',$v)->column('id')[0];
        }
        $data['region']=implode(',',$dd);
        $data['huxing']=implode(',',$data['huxing']);
        $data['mianji']=implode(',',$data['mianji']);
        $data['zong']=implode(',',$data['zong']);
        $data['k_time']=substr($data['k_time'],0,10);
        $data['time']=substr($data['time'],0,10);
        // 默认测试数据
        $data['s_id']=session('user.id');
        $data['sid']=session('user.id');
        UserModel::create($data);
        return json(['code'=>200]);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }
    // 这是显示列表
    public function qindex(){
        $data=Shouqian::select();
        $res=['code'=>200,'data'=>$data];
        return json($res);
    }
    public function qedit($id){
        $data=Shouqian::where('id',$id)->find();
        $res=['code'=>200,'data'=>$data];
        return json($res);
    }
    public function hindex($id){
        $data=Shouhou::where('u_id','eq',$id)->select();
        $res=['code'=>200,'data'=>$data];
        return json($res);
    }
    public function hedit($id){
        $data=Shouhou::where('id',$id)->find();
        $res=['code'=>200,'data'=>$data];
        return json($res);
    }
    public function qsave(){
        $data=request()->param()['value'];
        $data['u_id']=request()->param()['u_id'];
        $data['time']=substr($data['time'],0,10);
        Shouqian::create($data);
        return json(['code'=>200]);
    }
    public function hsave(){
        $data=request()->param()['value'];
        $data['u_id']=request()->param()['u_id'];
        Shouhou::create($data);
        return json(['code'=>200]);
    }
    public function zsave(){
        $data=request()->param();
        $data['content']=implode(',',$data['content']);
        Shouzhong::create($data);
        return json(['code'=>200]);
    }
    public function qupdate($id){
        $data=request()->param()['value'];
        Shouqian::update($data,['id'=>$id]);
        return json(['code'=>200]);
    }
    public function hupdate($id){
        $data=request()->param()['value'];
        Shouhou::update($data,['id'=>$id]);
        return json(['code'=>200]);
    }
    public function qdelete($id){
        Shouqian::destroy($id);
        return json(['code'=>200]);
    }
    public function hdelete($id){
        Shouhou::destroy($id);
        return json(['code'=>200]);
    }

    public function type(){
        $type=request()->param()['type'];
        $t=request()->param();
        $n=$t['n'];
        if($type=='公客'){
            $data=UserModel::where('s_id','eq','0')->order('id','desc')->limit(0,$n)->select();
            $num=UserModel::where('s_id','eq','0')->count('id');
        }else if($type=='私客'){
            if(session('user.super')==1){
                $data=UserModel::where('s_id','<>','0')->limit($n*$y,$n)->order('id','desc')->select();
                $num=UserModel::where('s_id','<>','0')->count('id');
            }else if(session('user.guide')!=1){
                $ids=Staff::where('pid',session('user.id'))->select();
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $data=UserModel::where('s_id','in',$ids)->limit($y*$n,$n)->order('id','desc')->select();
                $num=UserModel::where('s_id','in',$ids)->count('id');
            }else{
                $data=UserModel::where('s_id','eq',session('user.id'))->order('id','desc')->limit($n*$y,$n)->select();
                $num=UserModel::where('s_id','eq',session('user.id'))->count('id');
            }
        }
        foreach($data as $v){
            if($v['s_id']==0){
                $v['peo']='公客';
            }else{
                $v['peo']='私客';
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['sid']=Staff::where('id',$v['sid'])->column('name');
            if($v['sid']){
                $v['sid']=$v['sid'][0];
            }
            if(Building::where('id',$v['project'])->column('building_name')){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            }else{
                $v['project']='未定义';
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$num
        ];
        return json($res);
    }
    public function g(){
        $t=request()->param();
        $n=$t['n'];
        $y=$t['y'];
        $y=$y-1;
        $num=UserModel::where('s_id','eq',0)->count('id');
        $data=UserModel::where('s_id','eq',0)->order('id','desc')->limit($n*$y,$n)->select();
        foreach($data as $v){
            if($v['s_id']==0){
                $v['peo']='公客';
            }else{
                $v['peo']='私客';
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['sid']=Staff::where('id',$v['sid'])->column('name');
            if($v['sid']){
                $v['sid']=$v['sid'][0];
            }
            if(Building::where('id',$v['project'])->column('building_name')){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            }else{
                $v['project']='未定义';
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$num
        ];
        return json($res);
    }

    public function sou(){
        try{
            $type = input('param.type');
            $tiao = input('param.value');
            $t=request()->param();
            $n= input('param.n');
            $y= input('param.y');
            $y=$y-1;
            $where=[];
            $where[]=['status','neq',0];
            if($type =='公客'){
                $where[]=['s_id','eq',0];
            }else{
                if(session('user.super')==1){
                    $where[]=['s_id','neq','0'];
                }else if(session('user.guide')!=1){
                    $ids=Staff::where('pid',session('user.id'))->select();
                    $ids=$this->gets($ids);
                    $ids[]=session('user.id');
                    $where[]=['s_id','in',$ids];
                }else{
                    $where[]=['s_id','eq',session('user.id')];
                }
            }
            if (array_key_exists('building_name',$tiao)) {
                if($tiao['building_name']){
                    $id=Building::where('building_name','like','%'.$tiao['building_name'].'%')->column('id');
                    $where[]=['project','in',$id];
                }
            }
            if (array_key_exists('region',$tiao)) {
                if($tiao['region']){
                    $ss=$tiao['region'][2];
                    $l=Area::where('area_name','eq',$ss)->column('id')[0];
                    $where[]=['region','in',$l];
                }
            }
            if (array_key_exists('tel',$tiao)) {
                if($tiao['tel']){
                    $where[]=['tel','like','%'.$tiao['tel'].'%'];
                }
            }
            if (array_key_exists('city',$tiao)) {
                if($tiao['city']){
                    $ids=Staff::where('city','eq',$tiao['city'])->column('id');
                    $where[]=['s_id','in',$ids];
                }
            }
            if (array_key_exists('area',$tiao)) {
                if($tiao['area']){
                    $ids=Staff::where('area','eq',$tiao['area'])->column('id');
                    $where[]=['s_id','in',$ids];
                }
            }
            if (array_key_exists('department',$tiao)) {
                if($tiao['department']){
                    $ids=Staff::where('department','eq',$tiao['department'])->column('id');
                    $where[]=['s_id','in',$ids];
                }
            }
            if (array_key_exists('id',$tiao)) {
                if($tiao['id']){
                    $where[]=['s_id','eq',$tiao['id']];
                }
            }
            if (array_key_exists('grade',$tiao)) {
                if($tiao['grade']){
                    $where[]=['grade','eq',$tiao['grade']];
                }
            }
            if (array_key_exists('port',$tiao)) {
                if($tiao['port']){
                    $where[]=['port','eq',$tiao['port']];
                }
            }
            if (array_key_exists('time',$tiao)) {
                if($tiao['time']){
                    $ll=[];
                    $ll[]=date('Y-m-d',strtotime($tiao['time'][0]));
                    $ll[]=date('Y-m-d',strtotime($tiao['time'][1]));
                    $where[]=['time','between',$ll];
                }
            }
            if (array_key_exists('name',$tiao)) {
                if($tiao['name']){
                    $where[]=['name','like','%'.$tiao['name'].'%'];
                }
            }

            $pai=request()->param();
            if (array_key_exists('sheng',$pai)) {
                if (array_key_exists('sheng',$pai)) {
                    $sheng=$pai['sheng'];
                    $sortnum=$pai['sortnum'];
                    $data=UserModel::where($where)->order($sortnum,$sheng)->limit($y*$n,$n)->select();
                }
            }else{
                $data=UserModel::where($where)->order('id','desc')->limit($y*$n,$n)->select();
            }




            $total=UserModel::where($where)->count('id');
            foreach($data as $v){
                if($v['s_id']==0){
                    $v['peo']='公客';
                }else{
                    $v['peo']='私客';
                }
                if(Building::where('id',$v['project'])->column('building_name')){
                    $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
                }else{
                    $v['project']='未定义';
                }
                $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
                if($tt){
                    $v['t_time']=date('Y-m-d H:i',$tt[0]);
                    $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
                    $g=strtotime($v['g_time']);
                    $g=date('Y-m-d',$g);
                    $now=date('Y-m-d',time());
                    if($g==$now){
                        $v['isnow']=1;
                    }
                }
                $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
                if($v['s_id']){
                    $v['s_id']=$v['s_id'][0];
                }
                $v['sid']=Staff::where('id',$v['sid'])->column('name');
                if($v['sid']){
                    $v['sid']=$v['sid'][0];
                }
            }
            $res=[
                'code'=>200,
                'data'=>$data,
                'total'=>$total
            ];
            return json($res);
        }catch (\Exception $e){
            $res=[
                'code'=>500,
                'data'=>[],
                'total'=>0,
                'message'=>$e->getMessage()
            ];
            return json($res);
        }
    }


    public function gsou(){
        $type=request()->param()['type'];
        $tiao=request()->param()['value'];
        $t=request()->param();
        $n=$t['n'];
        $y=$t['y'];
        $y=$y-1;
        $where=[];
        if($type =='公客'){
            $where[]=['s_id','eq',0];
        }else{
            if(session('user.super')==1){
                $where[]=['s_id','neq','0'];
            }else if(session('user.guide')!=1){
                $ids=Staff::where('pid',session('user.id'))->select();
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }
        if (array_key_exists('building_name',$tiao)) {
            if($tiao['building_name']){
                $id=Building::where('building_name','like','%'.$tiao['building_name'].'%')->column('id');
                $where[]=['project','in',$id];
            }
        }
        if (array_key_exists('region',$tiao)) {
            if($tiao['region']){
                $ss=$tiao['region'][2];
                $l=Area::where('area_name','eq',$ss)->column('id')[0];
                $where[]=['region','in',$l];
            }
        }
        if (array_key_exists('tel',$tiao)) {
            if($tiao['tel']){
                $where[]=['tel','like','%'.$tiao['tel'].'%'];
            }
        }
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Staff::where('city','eq',$tiao['city'])->column('id');
                $where[]=['sid','in',$ids];
            }
        }
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $ids=Staff::where('area','eq',$tiao['area'])->column('id');
                $where[]=['sid','in',$ids];
            }
        }
        if (array_key_exists('department',$tiao)) {
            if($tiao['department']){
                $ids=Staff::where('department','eq',$tiao['department'])->column('id');
                $where[]=['sid','in',$ids];
            }
        }
        if (array_key_exists('id',$tiao)) {
            if($tiao['id']){
                $where[]=['sid','eq',$tiao['id']];
            }
        }
        if (array_key_exists('grade',$tiao)) {
            if($tiao['grade']){
                $where[]=['grade','eq',$tiao['grade']];
            }
        }
        if (array_key_exists('port',$tiao)) {
            if($tiao['port']){
                $where[]=['port','eq',$tiao['port']];
            }
        }
        if (array_key_exists('time',$tiao)) {
            if($tiao['time']){
                $ll=[];
                $ll[]=date('Y-m-d',strtotime($tiao['time'][0]));
                $ll[]=date('Y-m-d',strtotime($tiao['time'][1]));
                $where[]=['time','between',$ll];
            }
        }
        if (array_key_exists('name',$tiao)) {
            if($tiao['name']){
                $where[]=['name','like','%'.$tiao['name'].'%'];
            }
        }

        $pai=request()->param();
        if (array_key_exists('sheng',$pai)) {
            if (array_key_exists('sheng',$pai)) {
                $sheng=$pai['sheng'];
                $sortnum=$pai['sortnum'];
                $data=UserModel::where($where)->order($sortnum,$sheng)->limit($y*$n,$n)->select();
            }
        }else{
            $data=UserModel::where($where)->order('id','desc')->limit($y*$n,$n)->select();
        }
        $total=UserModel::where($where)->count('id');
        foreach($data as $v){
            if($v['s_id']==0){
                $v['peo']='公客';
            }else{
                $v['peo']='私客';
            }
            if(Building::where('id',$v['project'])->column('building_name')){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            }else{
                $v['project']='未定义';
            }
            $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            if($tt){
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
                $g=strtotime($v['g_time']);
                $g=date('Y-m-d',$g);
                $now=date('Y-m-d',time());
                if($g==$now){
                    $v['isnow']=1;
                }
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['sid']=Staff::where('id',$v['sid'])->column('name');
            if($v['sid']){
                $v['sid']=$v['sid'][0];
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$total
        ];
        return json($res);
    }

    public function sous(){
        $type=request()->param()['type'];
        $tiao=request()->param()['value'];
        $t=request()->param();
        $where=[];
        if($type =='公客'){
            $where[]=['s_id','eq',0];
        }else{
            if(session('user.super')==1){
                $where[]=['s_id','neq','0'];
            }else if(session('user.guide')!=1){
                $ids=Staff::where('pid',session('user.id'))->select();
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }
        if (array_key_exists('building_name',$tiao)) {
            if($tiao['building_name']){
                $id=Building::where('building_name','like','%'.$tiao['building_name'].'%')->column('id');
                $where[]=['project','in',$id];
            }
        }
        if (array_key_exists('region',$tiao)) {
            if($tiao['region']){
                $ss=$tiao['region'][2];
                $l=Area::where('area_name','eq',$ss)->column('id')[0];
                $where[]=['region','in',$l];
            }
        }
        if (array_key_exists('tel',$tiao)) {
            if($tiao['tel']){
                $where[]=['tel','like','%'.$tiao['tel'].'%'];
            }
        }
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Staff::where('city','eq',$tiao['city'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $ids=Staff::where('area','eq',$tiao['area'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('department',$tiao)) {
            if($tiao['department']){
                $ids=Staff::where('department','eq',$tiao['department'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('id',$tiao)) {
            if($tiao['id']){
                $where[]=['s_id','eq',$tiao['id']];
            }
        }
        if (array_key_exists('grade',$tiao)) {
            if($tiao['grade']){
                $where[]=['grade','eq',$tiao['grade']];
            }
        }
        if (array_key_exists('port',$tiao)) {
            if($tiao['port']){
                $where[]=['port','eq',$tiao['port']];
            }
        }
        if (array_key_exists('time',$tiao)) {
            if($tiao['time']){
                $ll=[];
                $ll[]=date('Y-m-d',strtotime($tiao['time'][0]));
                $ll[]=date('Y-m-d',strtotime($tiao['time'][1]));
                $where[]=['time','between',$ll];
            }
        }
        if (array_key_exists('name',$tiao)) {
            if($tiao['name']){
                $where[]=['name','like','%'.$tiao['name'].'%'];
            }
        }

        $data=UserModel::where($where)->order('id','desc')->select();
        $total=UserModel::where($where)->count('id');
        foreach($data as $v){
            if($v['s_id']==0){
                $v['peo']='公客';
            }else{
                $v['peo']='私客';
            }
            if(Building::where('id',$v['project'])->column('building_name')){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            }else{
                $v['project']='未定义';
            }
            $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            if($tt){
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
                $g=strtotime($v['g_time']);
                $g=date('Y-m-d',$g);
                $now=date('Y-m-d',time());
                if($g==$now){
                    $v['isnow']=1;
                }
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['sid']=Staff::where('id',$v['sid'])->column('name');
            if($v['sid']){
                $v['sid']=$v['sid'][0];
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$total
        ];
        return json($res);
    }


    // 关键字联想搜索
    public function lsou(){
        $n=request()->param()['n'];
        $data=Building::where('building_name','like','%'.$n.'%')->field('building_name,id')->select();
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        $data=UserModel::where('id',$id)->find();
        $data['leixing']=explode(',',$data['leixing']);
        $data['ceng']=explode(',',$data['ceng']);
        $data['region']=explode(',',$data['region']);
        $l=$data['region'][0];
        if($l!=''){
            $two=Area::where('id',$l)->column('pid');
            if($two){
                $two=$two[0];
                $two=Area::where('id',$two)->find();
                $one=Area::where('id',$two['pid'])->column('area_name')[0];
                $data['one']=[$one,$two['area_name']];
                $data['two']=Area::where('pid','eq',$two['id'])->field('area_name,id')->select();
            }

        }
        $data['huxing']=explode(',',$data['huxing']);
        $data['zong']=explode(',',$data['zong']);
        $data['mianji']=explode(',',$data['mianji']);
        if($data['project']){
            $data['project']=Building::where('id',$data['project'])->column('building_name')[0];
        }
        $qian=Shouqian::where('u_id',$id)->select();
        $hou=Shouhou::where('u_id',$id)->select();
        $zhong=Shouzhong::where('U_id',$id)->select();
        foreach($zhong as $v){
            $v['content']=explode(',',$v['content']);
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'qian'=>$qian,
            'hou'=>$hou,
            'zhong'=>$zhong
        ];
        return json($res);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
        $data=$request->param()['value'];
        $data['leixing']=implode(',',$data['leixing']);
        $data['ceng']=implode(',',$data['ceng']);
        unset($data['abc']);
        $name=Building::where('building_name','eq',$data['project'])->column('id');
        if($name){
            $data['project']=$name[0];
        }
        $data['region']=implode(',',$data['region']);
        $data['mianji']=implode(',',$data['mianji']);
        $data['zong']=implode(',',$data['zong']);
        $data['huxing']=implode(',',$data['huxing']);
        $data['k_time']=substr($data['k_time'],0,10);
        $data['time']=substr($data['time'],0,10);
        UserModel::update($data,['id'=>$id]);
        return json(['code'=>200]);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        Db::execute(" UPDATE erp.erp_user set `status` =0 where id= ? ",[$id]);
        return json(['code'=>200]);
    }
    // 客户量
    public function tong(){
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->column('id');
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }else{
            $where=[];
        }
        $tiao=request()->param()['value'];
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Staff::where('city','eq',$tiao['city'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $ids=Staff::where('area','eq',$tiao['area'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('department',$tiao)) {
            if($tiao['department']){
                $ids=Staff::where('department','eq',$tiao['department'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('id',$tiao)) {
            if($tiao['id']){
                $where[]=['s_id','eq',$tiao['id']];
            }
        }
        if (array_key_exists('port',$tiao)) {
            if($tiao['port']){
                $where[]=['port','eq',$tiao['port']];
            }
        }


        $type=request()->param()['type'];
        if($type==1){
            $s=strtotime('-12days');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("DATE_FORMAT(FROM_UNIXTIME(create_time),'%d') as date,count(*) as total")
                ->group("DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')")->select();
        }else if($type==2){
            $s=strtotime('-12week');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,count(*) as total")
                ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==3){
            $s=strtotime('-12month');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,count(*) as total")
                ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==4){
            $s=strtotime('-12quarter');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,count(*) as total")
                ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==5){
            $s=strtotime('-12year');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,count(*) as total")
                ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }


    public function tongsou(){
        $tiao=request()->param();
        $s=$tiao[0];
        $e=$tiao[1];
        $s=strtotime($s);
        $e=strtotime($e);
        $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',$e)])->field("DATE_FORMAT(FROM_UNIXTIME(create_time),'%d') as date,count(*) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')")->select();
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }



    public function bing(){
        $where=[];
        $type=request()->param()['type'];
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->column('id');
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }

        $tiao=request()->param()['value'];

        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Staff::where('city','eq',$tiao['city'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $ids=Staff::where('area','eq',$tiao['area'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('department',$tiao)) {
            if($tiao['department']){
                $ids=Staff::where('department','eq',$tiao['department'])->column('id');
                $where[]=['s_id','in',$ids];
            }
        }
        if (array_key_exists('id',$tiao)) {
            if($tiao['id']){
                $where[]=['s_id','eq',$tiao['id']];
            }
        }

        $s=strtotime('-7days');
        if($type==1){
            $t=strtotime('-7days');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where1=$where;
            $where1[]=['label','eq','新客'];
            $where2=$where;
            $where2[]=['label','eq','老客'];
            $new=Db::name('dai')->where($where1)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("count(*) as total")->select();
            $old=Db::name('dai')->where($where2)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',$s)])->field("count(*) as total")->select();
        }else if($type==2){
            $t=strtotime('-1month');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where1=$where;
            $where1[]=['label','eq','新客'];
            $where2=$where;
            $where2[]=['label','eq','老客'];
            $new=Db::name('dai')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("count(*) as total")->select();
            $old=Db::name('dai')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',$s)])->field("count(*) as total")->select();
        }else if($type==3){
            $t=strtotime('-1quarter');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where1=$where;
            $where1[]=['label','eq','新客'];
            $where2=$where;
            $where2[]=['label','eq','老客'];
            $new=Db::name('dai')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("count(*) as total")->select();
            $old=Db::name('dai')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',$s)])->field("count(*) as total")->select();
        }else if($type==4){
            $t=strtotime('-1year');
            $data=Db::name('user')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where1=$where;
            $where1[]=['label','eq','新客'];
            $where2=$where;
            $where2[]=['label','eq','老客'];
            $new=Db::name('dai')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("count(*) as total")->select();
            $old=Db::name('dai')->where($where)->whereTime('create_time',[date('Y-m-d',$t),date('Y-m-d',$s)])->field("count(*) as total")->select();
        }

        $n=[];
        $data=$data[0]['total'];
        $new=$new[0]['total'];
        $old=$old[0]['total'];
        $n[]=[
            'item'=>'新客利用率',
            'count'=>$new
        ];
        $n[]=[
            'item'=>'老客利用率',
            'count'=>$old
        ];
        $data=$data-$new-$old;
        // $n[]=[
        //     'item'=>'其他',
        //     'count'=>$data
        // ];
        $res=[
            'code'=>200,
            'n'=>$n
        ];
        return json($res);
    }

    public function like($id){
        $data=UserModel::where('id',$id)->find();
        $where=[];
        // if($data['zong']){
        //     $zong=explode(',',$data['zong']);
        //     $where[]=['zong','in',$zong];
        // }
        // if($data['leixing']){
        //     $leixing=explode(',',$data['leixing']);
        //     $where[]=['building_xingshi','in',$leixing];
        // }
        if($data['ceng']){
            $ceng=explode(',',$data['ceng']);
            $where[]=['cenggao','in',$ceng];
        }
        if($data['region']){
            $region=explode(',',$data['region']);
            $where[]=['cate_id','in',$region];
        }
        // if($data['huxing']){
        //     $huxing=explode(',',$data['huxing']);
        //     $where[]=['building_huxing','in',$huxing];
        // }
        $data=Building::where($where)->select();
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    public function xiang(){
        $where=[];
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->column('id');
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }


        $type=request()->param()['type'];
        if($type==1){
            $building=Db::name('user')->where($where)->whereTime('create_time','today')->column('project');
            $ids=Db::name('dai')->where($where)->whereTime('create_time','today')->column('id');
            $bold=Db::name('user')->where($where)->whereTime('create_time','yesterday')->field("project")->select();
            $dold=Db::name('dai')->where($where)->whereTime('create_time','yesterday')->column('id');
            $building=array_unique($building);
            $list=[];
            foreach($building as $v){
                $li=[];
                $kk=Building::where('id',$v)->column('building_name');
                if($kk){
                    $li[]=$kk[0];
                }else{
                    $li[]='未定义';
                }
                $li[]=Db::name('user')->where('project',$v)->whereTime('create_time','today')->count("id");
                $dai=Building::where('id',$v)->column('d_id');
                $dai=array_intersect($dai,$ids);
                $li[]=count($dai);
                if($li[1]==0){
                    $li[]=0;
                }else{
                    $li[]=round($li[2]/$li[1],2)*100;
                }
                $bold=Db::name('user')->where('project',$v)->whereTime('create_time','yesterday')->count("id");
                $ss=array_intersect($dai,$dold);
                $ss=count($ss);
                if($bold==0){
                    $li[]=0;
                }else{
                    $li[]=round($ss/$bold,2)*100;
                }
                $li[]=$li[4]-$li[3];
                $list[]=$li;
            }
        }else if($type==2){
            $building=Db::name('user')->where($where)->whereTime('create_time','week')->column('project');
            $ids=Db::name('dai')->where($where)->whereTime('create_time','week')->column('id');
            $bold=Db::name('user')->where($where)->whereTime('create_time','last week')->field("project")->select();
            $dold=Db::name('dai')->where($where)->whereTime('create_time','last week')->column('id');
            $building=array_unique($building);
            $list=[];
            foreach($building as $v){
                $li=[];
                $kk=Building::where('id',$v)->column('building_name');
                if($kk){
                    $li[]=$kk[0];
                }else{
                    $li[]='未定义';
                }
                $li[]=Db::name('user')->where('project',$v)->whereTime('create_time','week')->count("id");
                $dai=Building::where('id',$v)->column('d_id');
                $dai=array_intersect($dai,$ids);
                $li[]=count($dai);
                if($li[1]==0){
                    $li[]=0;
                }else{
                    $li[]=round($li[2]/$li[1],2)*100;
                }

                $bold=Db::name('user')->where('project',$v)->whereTime('create_time','last week')->count("id");
                $ss=array_intersect($dai,$dold);
                $ss=count($ss);
                if($bold==0){
                    $li[]=0;
                }else{
                    $li[]=round($ss/$bold,2)*100;
                }

                $li[]=$li[4]-$li[3];
                $list[]=$li;
            }
        }else if($type==3){
            $building=Db::name('user')->where($where)->whereTime('create_time','month')->column('project');
            $ids=Db::name('dai')->where($where)->whereTime('create_time','month')->column('id');
            $bold=Db::name('user')->where($where)->whereTime('create_time','last month')->field("project")->select();
            $dold=Db::name('dai')->where($where)->whereTime('create_time','last month')->column('id');
            $building=array_unique($building);
            $list=[];
            foreach($building as $v){
                $li=[];
                $kk=Building::where('id',$v)->column('building_name');
                if($kk){
                    $li[]=$kk[0];
                }else{
                    $li[]='未定义';
                }
                $li[]=Db::name('user')->where('project',$v)->whereTime('create_time','month')->count("id");
                $dai=Building::where('id',$v)->column('d_id');
                $dai=array_intersect($dai,$ids);
                $li[]=count($dai);
                if($li[1]==0){
                    $li[]=0;
                }else{
                    $li[]=round($li[2]/$li[1],2)*100;
                }
                $bold=Db::name('user')->where('project',$v)->whereTime('create_time','last month')->count("id");
                $ss=array_intersect($dai,$dold);
                $ss=count($ss);
                if($bold==0){
                    $li[]=0;
                }else{
                    $li[]=round($ss/$bold,2)*100;
                }
                $li[]=$li[4]-$li[3];
                $list[]=$li;
            }
        }else if($type==4){
            $building=Db::name('user')->where($where)->whereTime('create_time','year')->column('project');
            $ids=Db::name('dai')->where($where)->whereTime('create_time','year')->column('id');
            $bold=Db::name('user')->where($where)->whereTime('create_time','last year')->field("project")->select();
            $dold=Db::name('dai')->where($where)->whereTime('create_time','last year')->column('id');
            $building=array_unique($building);
            $list=[];
            foreach($building as $v){
                $li=[];
                $kk=Building::where('id',$v)->column('building_name');
                if($kk){
                    $li[]=$kk[0];
                }else{
                    $li[]='未定义';
                }
                $li[]=Db::name('user')->where('project',$v)->whereTime('create_time','year')->count("id");
                $dai=Building::where('id',$v)->column('d_id');
                $dai=array_intersect($dai,$ids);
                $li[]=count($dai);
                if($li[1]==0){
                    $li[]=0;
                }else{
                    $li[]=round($li[2]/$li[1],2)*100;
                }
                $bold=Db::name('user')->where('project',$v)->whereTime('create_time','last year')->count("id");
                $ss=array_intersect($dai,$dold);
                $ss=count($ss);
                if($bold==0){
                    $li[]=0;
                }else{
                    $li[]=round($ss/$bold,2)*100;
                }
                $li[]=$li[4]-$li[3];
                $list[]=$li;
            }
        }
        $res=[
            'code'=>200,
            'data'=>$list
        ];
        return json($res);
    }

    // 统计分析带过来的客户列表
    public function tonglist(){
        $ids=request()->param()['ids'];
        $data=UserModel::where('id','in',$ids)->select();
        foreach($data as $v){
            if($v['s_id']==0){
                $v['peo']='公客';
            }else{
                $v['peo']='私客';
            }
            if(Building::where('id',$v['project'])->column('building_name')){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            }else{
                $v['project']='未定义';
            }
            $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            if($tt){
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
                $g=strtotime($v['g_time']);
                $g=date('Y-m-d',$g);
                $now=date('Y-m-d',time());
                if($g==$now){
                    $v['isnow']=1;
                }
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['sid']=Staff::where('id',$v['sid'])->column('name');
            if($v['sid']){
                $v['sid']=$v['sid'][0];
            }
        }
        return json(['code'=>200,'data'=>$data]);
    }


    // 客户等级分布
    public function dengfen(){
        $where=[];
        $type=request()->param()['type'];
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->column('id');
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }

        $tiao=request()->param()['value'];

        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Staff::where('city','eq',$tiao['city'])->column('id');
                $where[]=['sid','in',$ids];
            }
        }
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $ids=Staff::where('area','eq',$tiao['area'])->column('id');
                $where[]=['sid','in',$ids];
            }
        }
        if (array_key_exists('department',$tiao)) {
            if($tiao['department']){
                $ids=Staff::where('department','eq',$tiao['department'])->column('id');
                $where[]=['sid','in',$ids];
            }
        }
        if (array_key_exists('id',$tiao)) {
            if($tiao['id']){
                $where[]=['sid','eq',$tiao['id']];
            }
        }

        $s=strtotime('-3days');
        if($type==1){
            $t=strtotime('-7days');
            $where1=$where;
            $where1[]=['grade','eq','A类'];
            $A=Db::name('user')->where($where1)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where2=$where;
            $where2[]=['grade','eq','B类'];
            $B=Db::name('user')->where($where2)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where3=$where;
            $where3[]=['grade','eq','C类'];
            $C=Db::name('user')->where($where3)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where4=$where;
            $where4[]=['grade','eq','D类'];
            $D=Db::name('user')->where($where4)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
        }else if($type==2){
            $t=strtotime('-1month');
            $where1=$where;
            $where1[]=['grade','eq','A类'];
            $A=Db::name('user')->where($where1)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where2=$where;
            $where2[]=['grade','eq','B类'];
            $B=Db::name('user')->where($where2)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where3=$where;
            $where3[]=['grade','eq','C类'];
            $C=Db::name('user')->where($where3)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where4=$where;
            $where4[]=['grade','eq','D类'];
            $D=Db::name('user')->where($where4)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
        }else if($type==3){
            $t=strtotime('-1quarter');
            $where1=$where;
            $where1[]=['grade','eq','A类'];
            $A=Db::name('user')->where($where1)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where2=$where;
            $where2[]=['grade','eq','B类'];
            $B=Db::name('user')->where($where2)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where3=$where;
            $where3[]=['grade','eq','C类'];
            $C=Db::name('user')->where($where3)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where4=$where;
            $where4[]=['grade','eq','D类'];
            $D=Db::name('user')->where($where4)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
        }else if($type==4){
            $t=strtotime('-1year');
            $where1=$where;
            $where1[]=['grade','eq','A类'];
            $A=Db::name('user')->where($where1)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where2=$where;
            $where2[]=['grade','eq','B类'];
            $B=Db::name('user')->where($where2)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where3=$where;
            $where3[]=['grade','eq','C类'];
            $C=Db::name('user')->where($where3)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
            $where4=$where;
            $where4[]=['grade','eq','D类'];
            $D=Db::name('user')->where($where4)->whereTime('time',[date('Y-m-d',$t),date('Y-m-d',time())])->field("count(*) as total")->select();
        }

        $n=[];
        $A=$A[0]['total'];
        $B=$B[0]['total'];
        $C=$C[0]['total'];
        $D=$D[0]['total'];
        $n[]=[
            'item'=>'A类客户',
            'count'=>$A
        ];
        $n[]=[
            'item'=>'B类客户',
            'count'=>$B
        ];
        $n[]=[
            'item'=>'C类客户',
            'count'=>$C
        ];
        $n[]=[
            'item'=>'D类客户',
            'count'=>$D
        ];

        $res=[
            'code'=>200,
            'n'=>$n
        ];
        return json($res);
    }

    /**
     * 恢复用户
     * @return \think\response\Json
     */
    public function recover(){
        try{
            $id = input('param.id',0);
            $num = Db::execute("UPDATE  erp.erp_user SET `status`=1 where id = ? ",[$id]);
            if(!$num){
                throw new \Exception('恢复失败');
            }
            return json(['code'=>200,'message'=>'恢复成功']);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }
}
