<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\api\model\Gen as GenModel;
use app\api\model\Staff;
use app\api\model\User;
use app\api\model\Building;

class Gen extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($id)
    {
        //
        $data = GenModel::where('u_id', $id)->order('id', 'desc')->select();
        foreach ($data as $v) {
            $v['name'] = Staff::where('id', $v['s_id'])->column('name');
            $v['time'] = date('Y-m-d H:i', $v['time']);
            $v['t_time'] = date('Y-m-d H:i', $v['t_time']);
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
        }
        $res = [
            'code' => 200,
            'data' => $data
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
        $data = $request->param()['value'];
        $data['s_id']=session('user.id');
        $data['time'] = time();
        $data['t_time'] = strtotime('+3days', $data['time']);
        $data['u_id'] = $request->param()['u_id'];
        $l=User::where('id',$data['u_id'])->column('s_id')[0];
        if($l==0){
            $data['label']='公客';
        }else{
            $data['label']='私客';
        }
        $data['type'] = $request->param('type');
        GenModel::create($data);
        return json(['code' => 200]);
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
        $data = GenModel::where('id', $id)->find();
        $data['time'] = date('Y-m-d H:i:s', $data['time']);
        $data['t_time'] = date('Y-m-d H:i:s', $data['t_time']);
        $res = [
            'code' => 200,
            'data' => $data
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
        $data = $request->param()['value'];
        $data['time'] = strtotime($data['time']);
        $data['t_time'] = strtotime('+3days', $data['time']);
        GenModel::update($data, ['id' => $id]);
        return json(['code' => 200]);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        GenModel::destroy($id);
        return json(['code' => 200]);
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
    // 客户跟进周期
    public function tong(){
        if(session('user.guide')!=1){
            $ids=Staff::where('pid','eq',session('user.id'))->select();
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $ids=implode(',',$ids);
            $data=Db::query("select * from (select * from erp_gen where s_id in ($ids) order by update_time desc) as data group by u_id order by update_time desc");
        }else{
            $id=session('user.id');
            $data=Db::query("select * from (select * from erp_gen where s_id=$id order by update_time desc) as data group by u_id order by update_time desc");
        }
        
        $time=time();
        $s1=0;
        $s2=0;
        $s3=0;
        $s4=0;
        $ids=[];
        foreach($data as $v){
            if($time-$v['create_time']<(3600*24*3)){
                $ids['one'][]=$v['id'];
                $s1=$s1+1;
            }else if($time-$v['create_time']<(3600*24*6) && $time-$v['create_time']>(3600*24*4)){
                $ids['two'][]=$v['id'];
                $s2=$s2+1;
            }else if($time-$v['create_time']<(3600*24*11) && $time-$v['create_time']>(3600*24*7)){
                $ids['thir'][]=$v['id'];
                $s3=$s3+1;
            }else if($time-$v['create_time']>(3600*24*11)){
                $ids['four'][]=$v['id'];
                $s4=$s4+1;
            }
        }
        $res=[];
        $n=[];
        $l=[
            'item'=>'1-3',
            'count'=>$s1
        ];
        $l=json_encode($l);
        $n[]=$l;
        $a=[
            'item'=>'4-6',
            'count'=>$s2
        ];
        $a=json_encode($a);
        $n[]=$a;
        $s=[
            'item'=>'7-11',
            'count'=>$s3
        ];
        $s=json_encode($s);
        $n[]=$s;
        $d=[
            'item'=>'11+',
            'count'=>$s4
        ];
        $s=json_encode($d);
        $n[]=$s;
        $res['ids']=$ids;
        $res=[
            'code'=>200,
            'n'=>$n,
            'ids'=>$ids
        ];
        return json($res);
    } 
    // 客户跟进周期列表
    public function tonglist(){
        $ids=request()->param()['ids'];
        $ids=explode(',',$ids);
        $data=User::where('id','in',$ids)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            if($v['project']){
                $v['project']=$v['project'][0];
            }
            $tt=GenModel::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            if($tt){
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',GenModel::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    // 电话跟进
    public function diangen(){
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
        if($type==1){
            $where1=$where;
            $where2=$where;
            $where1[]=['label','eq','公客'];
            $where2[]=['label','eq','私客'];
            $si=Db::name('gen')->where($where2)->whereTime('create_time','today')->count('id');
            $gong=Db::name('gen')->where($where1)->whereTime('create_time','today')->count('id');
            $sids=Db::name('gen')->where($where2)->whereTime('create_time','today')->column('id');
            $gids=Db::name('gen')->where($where1)->whereTime('create_time','today')->column('id');
        }else if($type==2){
            $where1=$where;
            $where2=$where;
            $where1[]=['label','eq','公客'];
            $where2[]=['label','eq','私客'];
            $si=Db::name('gen')->where($where2)->whereTime('create_time','week')->count('id');
            $gong=Db::name('gen')->where($where1)->whereTime('create_time','week')->count('id');
            $sids=Db::name('gen')->where($where2)->whereTime('create_time','week')->column('id');
            $gids=Db::name('gen')->where($where1)->whereTime('create_time','week')->column('id');
        }else if($type==3){
            $where1=$where;
            $where2=$where;
            $where1[]=['label','eq','公客'];
            $where2[]=['label','eq','私客'];
            $si=Db::name('gen')->where($where2)->whereTime('create_time','month')->count('id');
            $gong=Db::name('gen')->where($where1)->whereTime('create_time','month')->count('id');
            $sids=Db::name('gen')->where($where2)->whereTime('create_time','month')->column('id');
            $gids=Db::name('gen')->where($where1)->whereTime('create_time','month')->column('id');
        }
        else if($type==4){
            $where1=$where;
            $where2=$where;
            $where1[]=['label','eq','公客'];
            $where2[]=['label','eq','私客'];
            $si=Db::name('gen')->where($where2)->whereTime('create_time','year')->count('id');
            $gong=Db::name('gen')->where($where1)->whereTime('create_time','year')->count('id');
            $sids=Db::name('gen')->where($where2)->whereTime('create_time','year')->column('id');
            $gids=Db::name('gen')->where($where1)->whereTime('create_time','year')->column('id');
        }
        $n=[];
        $l=[
            'item'=>'私客量',
            'count'=>$si
        ];
        $n[]=$l;
        $k=[
            'item'=>'公客量',
            'count'=>$gong
        ];
        $n[]=$k;
        $res=[
            'code'=>200,
            'data'=>$n,
            'sids'=>$sids,
            'gids'=>$gids
        ];
        return json($res);
    }

    // 电话跟进列表
    public function tellist(){
        $ids=request()->param()['ids'];
        $n=request()->param()['n'];
        $p=request()->param()['p'];
        $p=$p-1;
        $data=GenModel::where('id','in',$ids)->limit($n*$p,$n)->select();
        $num=GenModel::where('id','in',$ids)->count('id');
        foreach($data as $v){
            $v['wei']=Staff::where('id',$v['s_id'])->column('name')[0];
            $id=User::where('id',$v['u_id'])->column('s_id')[0];
            // dump($id);die();
            $v['deng']=Staff::where('id','eq',$id)->column('name');
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$num
        ];
        return json($res);
    }

    // 电话跟进搜索
    public function telsou(){
        $tiao=request()->param();
        $ids=request()->param()['ids'];
        $where=[];
        $where[]=['id','in',$ids];
        if (array_key_exists('name',$tiao)) {
            if($tiao['name']){
                $id=Staff::where('name','like','%'.$tiao['name'].'%')->column('id');
                $where[]=['s_id','in',$id];
            }
        }
        if (array_key_exists('area',$tiao)) {
            if($tiao['area']){
                $as=Area::where('area_name','eq',$tiao['area'])->column('area_name');
                $bs=User::where('area','in',$as)->column('id');
                $where[]=['u_id','in',$bs];
            }
        }
        $data=GenModel::where($where)->select();
        foreach($data as $v){
            $v['wei']=Staff::where('id',$v['s_id'])->column('name')[0];
            $id=User::where('id',$v['u_id'])->column('s_id')[0];
            $v['deng']=Staff::where('id',$id)->column('name');
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    // 统计分析跟进
    public function tonggenlist(){
        $ids=request()->param()['ids'];
        $data=GenModel::where('id','in',$ids)->select();
        foreach ($data as $v) {
            $u=User::where('id','eq',$v['u_id'])->find();
            $v['name'] = $u['name'];
            
            $v['time'] = date('Y-m-d H:i', $v['time']);
            $v['t_time'] = date('Y-m-d H:i', $v['t_time']);
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $v['pro']=$u['project'];
            if($v['pro']){
                $v['pro']=Building::where('id','eq',$v['pro'])->column('building_name');
                if($v['pro']){
                    $v['pro']=$v['pro'][0];
                }
            }
        }
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
   
}
