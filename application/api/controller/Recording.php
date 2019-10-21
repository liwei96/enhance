<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\facade\Env;
use app\api\model\Record;
use app\api\model\Building;
use app\api\model\User;
use app\api\model\Dai;
use app\api\model\Area;
use app\api\model\Integral;
use app\api\model\Staff;

class Recording extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $ids = Record::column('project');
        $ids = array_unique($ids);
        $data = Building::where('id','in',$ids)->select();
        foreach($data as $v){
            $v['total'] = Record::where('project','eq',$v['id'])->count('id');
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'ids'=>$ids
        ];
        return json($res);
    }
    public function uindex($id){
        if(session('user.super')==1){
            $data=Record::where('number','eq',$id)->select();
        }else{
            $data=Record::where([['number','eq',$id],['s_id','eq',session('user.id')]])->select();
        }
        
        foreach($data as $v){
            $ll=Building::where('id',$v['project'])->column('building_name');
            if($ll){
                $v['project']=$ll[0];
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    public function sou(){
        $ids=request()->param()['ids'];
        $tiao=request()->param()['tiao'];
        $where=[];
        $where[]=['id','in',$ids];
        if($tiao['building_xingshi']){
            $where[]=['building_xingshi','eq',$tiao['building_xingshi']];
        }
        if($tiao['building_name']){
            $where[]=['building_name','like','%'.$tiao['building_name'].'%'];
        }
        if($tiao['cate_id']){
            $where[]=['cate_id','eq',$tiao['cate_id']];
        }
        if($tiao['jiage']){
            $where[]=['jiage','eq',$tiao['jiage']];
        }
        if($tiao['zong']){
            $where[]=['zong','in',$tiao['zong']];
        }
        if($tiao['huxing']){
            $where[]=['huxing','in',$where['huxing']];
        }
        $data=Building::where($where)->select();
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    // 成交项目列表
    public function recordProject(){
        $ss=Record::where('s_id','eq',session('user.id'))->column('project');
        $ss=array_unique($ss);
        $data=Building::where('id','in',$ss)->select();
        foreach ($data as $v) {
            $n = Area::where('id', $v['cate_id'])->column('pid')[0];
            $s = Area::where('id', $n)->column('pid')[0];
            $v['city'] = Area::where('id', $n)->column('area_name')[0];
            $v['provice'] = Area::where('id', $s)->column('area_name')[0];
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    // 成交项目带看
    public function recordProjectDai($id){
        $ids=Building::where('id',$id)->column('d_id')[0];
        $ids=explode(',',$ids);
        $data=Dai::where('id','in',$ids)->select();
        foreach($data as $v){
            $v['s_id']=Staff::where('id','eq',$v['s_id'])->column('name')[0];
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    // 成交项目的成交情况
    public function recordProjects($id){
        $data=Record::where('project','eq',$id)->select();
        foreach($data as $v){      
            $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            $v['s_id']=Staff::where('id','eq',$v['s_id'])->column('name')[0];
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    // 成交项目的搜索
    public function recordProjectSou(){
        $ss = request()->param()['value'];
        $where = [];
        $ids=Record::where('s_id','eq',session('user.id'))->column('project');
        $ids=array_unique($ids);
        $where[]=['id','in',$ids];
        if (array_key_exists('building_xingshi',$ss)) {
            if($ss['building_xingshi']){
                $where[] = ['building_xingshi', 'eq', $ss['building_xingshi']];
            }
        }
        if (array_key_exists('building_name',$ss)) {
            if($ss['building_name']){
                $where[] = ['building_name', 'like', '%' . $ss['building_name'] . '%'];
            }
        }
        if (array_key_exists('cate_id',$ss)) {
            if($ss['cate_id']){
                $l=$ss['cate_id'][2];
                $id=Area::where('area_name','eq',$l)->column('id')[0];
                $where[] = ['cate_id', 'eq', $id];
            }
        }
        if (array_key_exists('type',$ss)) {
            if($ss['type']){
                if(array_key_exists('num',$ss)){
                    if($ss['num']){
                        $where[]=[$ss['type'],'in',$ss['num']];
                    }
                }
            }
        }
        if (array_key_exists('huxing',$ss)) {
            if($ss['huxing']){
                $where[] = ['building_huxing', 'in', $ss['huxing']];
            }
        }
        $data = Building::where($where)->select();
        foreach ($data as $v) {
            $n = Area::where('id', $v['cate_id'])->column('pid')[0];
            $s = Area::where('id', $n)->column('pid')[0];
            $v['city'] = Area::where('id', $n)->column('area_name')[0];
            $v['provice'] = Area::where('id', $s)->column('area_name')[0];
        }
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    // 成交客户列表
    public function recordUser(){
        if(session('user.super')==1){
            $ids=Record::column('number');
        }else if(session('user.guide')!=1){
            $ids=Staff::where('pid',session('user.id'))->select();
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $ids=Record::where('s_id','in',$ids)->column('number');
        }else if(session('user.super')!=1){
            $ids=Record::where('s_id','eq',session('user.id'))->column('number');
        }
        
        $ids=array_unique($ids);
        $data=User::where('id','in',$ids)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            if($v['project']){
                $v['project']=$v['project'][0];
            }else{
                $v['project']='';
            }
            $ll=Record::where('number',$v['id'])->order('id','desc')->limit(1)->column('time');
            if($ll){
                $v['r_time']=$ll[0];
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    // 成交客户搜索
    public function recordUserSou(){
        $tiao=request()->param()['value'];
        if(session('user.super')!=1){
            if (array_key_exists('time',$tiao)) {
                if($tiao['time']){
                    if(session('user.guide')!=1){
                        $ids=Staff::where('pid',session('user.id'))->select();
                        $ids=$this->gets($ids);
                        $ids[]=session('user.id');
                        $ids=Db::name('record')->where('s_id','in',$ids)->whereTime('time',$tiao['time'])->column('number');
                    }else{
                        $ids=Db::name('record')->where('s_id','eq',session('user.id'))->column('number');
                    }
                }else{
                    if(session('user.guide')!=1){
                        $ids=Staff::where('pid',session('user.id'))->select();
                        $ids=$this->gets($ids);
                        $ids[]=session('user.id');
                        $ids=Db::name('record')->where('s_id','in',$ids)->column('number');
                    }else{
                        $ids=Db::name('record')->where('s_id','eq',session('user.id'))->column('number');
                    }
                }
            }else{
                if(session('user.guide')!=1){
                    $ids=Staff::where('pid',session('user.id'))->select();
                    $ids=$this->gets($ids);
                    $ids[]=session('user.id');
                    $ids=Db::name('record')->where('s_id','in',$ids)->column('number');
                }else{
                    $ids=Db::name('record')->where('s_id','eq',session('user.id'))->column('number');
                }
            }
        }else if(session('user.super')==1){
            if (array_key_exists('time',$tiao)) {
                if($tiao['time']){
                    $ids=Db::name('record')->whereTime('time',$tiao['time'])->column('number');
                }else{
                    $ids=Record::column('number');
                }
            }else{
                $ids=Record::column('number');
            }
        }
        
        $ids=array_unique($ids);
        $where=[];
        $where[]=['id','in',$ids];
        
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
        if (array_key_exists('port',$tiao)) {
            if($tiao['port']){
                $where[]=['port','eq',$tiao['port']];
            }
        }
        if(array_key_exists('type',$tiao)){
            if($tiao['type']){
                $ids=Building::where('building_xingshi','eq',$tiao['type'])->column('id');
                $ids=Record::where('project','in',$ids)->column('number');
                $where[]=['id','in',$ids];
            }
        }

        
        $data=User::where($where)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            if($v['project']){
                $v['project']=$v['project'][0];
            }else{
                $v['project']='';
            }
            $ll=Record::where('number',$v['id'])->order('id','desc')->limit(1)->column('time');
            if($ll){
                $v['r_time']=$ll[0];
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
        }
        $res=['code'=>200,'data'=>$data];
        return json($res);
    }
    // 成交客户类型
    public function recordUserType(){
        $type=request()->param()['type'];
        $ids=request()->param()['ids'];
        $where=[];
        $where[]=['id','in',$ids];
        if($type=='公客'){
            $where[]=['s_id','eq','0'];
        }else if($type=='私客'){
            $where[]=['s_id','eq',session('user.id')];
        }
        $data=UserModel::where($where)->select();
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
    public function create()
    {
        //
        
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
        $data['site']=$request->param()['site'];
        $data['site']=implode(',',$data['site']);
        $data['element']=implode(',',$request->param()['element']);
        $data['protocol']=implode(',',$request->param()['protocol']);
        $data['confirm']=implode(',',$request->param()['confirm']);
        $data['time']=substr($data['time'],0,10);
        $data['q_time']=substr($data['q_time'],0,10);
        $data['p_time']=substr($data['p_time'],0,10);
        $data['number']=$request->param()['id'];
        $id=$request->param()['id'];
        $user=User::where('id',$id)->find();
        // $label=
        $data['from']=$request->param()['from'];
        $data['s_id']=session('user.id');
        $uid=$data['number'];
        // $i=Integral::where([['id','eq',session('user.id')],['bid','eq',$data['project']]])->order('id','desc')->limit(0,1)->select();
        // $i=$i[0];
        // $a=[];
        // if($i){
        //     $a['total_integral']=$i['total_integral']+1;
        // }else{
        //     $a['total_integral']=1;
        // }
        //     $a['sid']=session('user.id');
        //     $a['integral']='+1';
        //     $a['action']='成交';
        //     $a['bid']=$data['project'];
        //     Integral::create($a);
        $ss='成交';
        User::update(['re'=>$ss,'id'=>$uid]);
        Record::create($data);
        $res=[
            'code'=>200
        ];
        return json($res);

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

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        $data=Record::where('id',$id)->find();
        $data['element']=explode(',',$data['element']);
        $data['confirm']=explode(',',$data['confirm']);
        $data['site']=explode(',',$data['site']);
        $data['protocol']=explode(',',$data['protocol']);
        $data['project']=Building::where('id',$data['project'])->column('building_name')[0];
        $res=['code'=>200,'data'=>$data];
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
        $data['site']=$request->param()['site'];
        $data['site']=implode(',',$data['site']);
        $data['element']=implode(',',$request->param()['element']);
        $data['protocol']=implode(',',$request->param()['protocol']);
        $data['confirm']=implode(',',$request->param()['confirm']);
        $data['project']=Building::where('building_name','eq',$data['project'])->column('id')[0];
        $data['time']=substr($data['time'],0,10);
        $data['q_time']=substr($data['q_time'],0,10);
        $data['p_time']=substr($data['p_time'],0,10);
        Record::update($data,['id'=>$id]);
        $res=[
            'code'=>200
        ];
        return json($res);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delpic(){
        $url=request()->param()['url'];
        $url=explode('/',$url);
        unset($url[0]);
        $url=implode('/',$url);
        unlink($url);
        return json(['code'=>200]);
    }
    public function delete($id)
    {
        //
        $data=Record::where('id',$id)->find();
        if($data['element']){
            $element=explode(',',$data['element']);
            foreach($element as $v){
                $l=explode('/',$v);
                unset($l[0]);
                $v=implode('/',$l);
                unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $v);
            }
        }
        if($data['site']){
            $site=explode(',',$data['site']);
            foreach($site as $v){
                $l=explode('/',$v);
                unset($l[0]);
                $v=implode('/',$l);
                unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $v);
            }
        }
        if($data['protocol']){
            $protocol=explode(',',$data['protocol']);
            foreach($protocol as $v){
                $l=explode('/',$v);
                unset($l[0]);
                $v=implode('/',$l);
                unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $v);
            }
        }
        if($data['confirm']){
            $confirm=explode(',',$data['confirm']);
            foreach($confirm as $v){
                $l=explode('/',$v);
                unset($l[0]);
                $v=implode('/',$l);
                unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $v);
            }
        }
        
        
        Record::destroy($id);
        $res=['code'=>200];
        return json($res);
    }
    function gets($data){
        static $ids=[];
        
        foreach($data as $v){
            $dd=Staff::where('pid',$v['id'])->select();
            if($dd){
                $this->gets($dd);
                $ids[]=$v['id'];
            }else{
                $ids[]=$v['id'];
            }
        }
        return $ids;
    }
    // 成交量统计图
    public function tong(){
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

        $type=request()->param()['type'];
        if($type==1){
            $s=strtotime('-12days');
            $data=Db::name('record')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("DATE_FORMAT(FROM_UNIXTIME(create_time),'%d') as date,sum(yeji) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(create_time),'%d')")->select();
        }else if($type==2){
            $s=strtotime('-12week');
            $data=Db::name('record')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,sum(yeji) as total")
            ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==3){
            $s=strtotime('-12month');
            $data=Db::name('record')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,sum(yeji) as total")
            ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==4){
            $s=strtotime('-12quarter');
            $data=Db::name('record')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,sum(yeji) as total")
            ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==5){
            $s=strtotime('-12year');
            $data=Db::name('record')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as date,sum(yeji) as total")
            ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    // 个人成交列表
    public function project(){
        if(session('user.guide')!=1){
            $ids=Staff::where('pid','eq',session('user.id'))->column('id');
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $where[]=['s_id','in',$ids];
        }else{
            $where[]=['s_id','eq',session('user.id')];
        }

        $data=Record::where($where)->select();
        $ids=Record::where($where)->column('id');
        if($data){
            foreach($data as $v){
                $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
                // $v['time']=date('Y-m-d',$v['time']);
            }
        }
        
        
        $res=[
            'code'=>200,
            'data'=>$data,
            'ids'=>$ids
        ];
        return json($res);
    }

    // 个人成交搜索
    public function projectSou(){
        $ids=request()->param()['ids'];
        $tiao=request()->param()['value'];
        $where=[];
        $where1=[];
        $where[]=['id','in',$ids];
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $city = $tiao['area'][1];
                $as = Area::where('area_name', $city)->column('id')[0];
                $as=Area::where('pid','eq',$as)->column('id');
                $where1[]=['cate_id','in',$as];
            }
        }
        if (array_key_exists('type',$tiao)) {
            if($tiao['type']){
                if(array_key_exists('num',$tiao)){
                    if($tiao['num']){
                        $where1[]=[$tiao['type'],'in',$tiao['num']];
                    }
                }
            }
        }
        if (array_key_exists('huxing',$tiao)) {
            if($tiao['huxing']){
                $where1[] = ['building_huxing', 'in', $tiao['huxing']];
            }
        }
        if($where1!=[]){
            $cs=Building::where($where1)->column('id');
            $where[]=['project','in',$cs];
        }
        $data=Record::where($where)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            $v['time']=date('Y-m-d',$v['time']);
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }



    public function tongrelist(){
        $ids=request()->param()['ids'];
        $ids=Record::where('id','in',$ids)->column('number');

        $ids=array_unique($ids);
        $data=User::where('id','in',$ids)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            if($v['project']){
                $v['project']=$v['project'][0];
            }else{
                $v['project']='';
            }
            $ll=Record::where('number',$v['id'])->order('id','desc')->limit(1)->column('time');
            if($ll){
                $v['r_time']=$ll[0];
            }
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
}
