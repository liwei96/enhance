<?php

namespace app\api\controller;

use think\Controller;
use think\Request; 
use think\Db; 
use think\facade\Cache;
use app\api\model\Guide as GuideModel;
use app\api\model\Area;
use app\api\model\Building;
use app\api\model\Staff;

class Guide extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $data=GuideModel::select();
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
        $bid=$request->param()['bid'];
        $l=Building::where('id',$bid)->column('building_name')[0];
        $id=Db::connect('db_config1')->table('tpshop.tpshop_goods')->where('building_name','eq',$l)->find();
        $id=$id['id'];
        $list=$request->param()['value'];
        $list['bid']=$id;
        $ma=mt_rand(00000,99999);
        $list['gid']=$ma;
        $list['create_time']=time();
        $list['update_time']=time();
        Db::connect('db_config1')->table('tpshop.tpshop_text')->insert($list);
        Db::connect('db_config2')->table('tpshop.tpshop_text')->insert($list);
        $data['bid']=$bid;
        $data['gid']=$ma;
        $l=Guide::where('bid','eq',$v['id'])->limit(1)->column('s_id');
        if($l){
            $data['s_id']=$l[0];
        }else{
            $sid=session('user.id');
            $data['s_id']=$sid;
        }
        
        
        GuideModel::create($data);
        Building::where('id','eq',$bid)->update(['old'=>0]);
        // $i=Integral::where([['id','eq',session('user.id')],['bid','eq',$bid]])->order('id','desc')->paginate(1);
        // $i=$i[0];
        // $a=[];
        //     $a['sid']=session('user.id');
        //     $a['integral']='+2';
        //     $a['total_integral']=$i['total_integral']+2;
        //     $a['action']='新增动态';
        //     $a['bid']=$bid;
        //     Integral::create($a);
        // Record::create($data);
        $ids=[];
        if(Cache::get('check')){
            $ids=Cache::get('check');
            $sid=Staff::where('id','eq',$data['s_id'])->column('pid')[0];
            $iid = Db::name('erp_guide')->getLastInsID();
            GuideModel::where('id','eq',$iid)->update(['sid'=>$sid]);
            $key = array_search($bid, $ids);
            if ($key !== false)
            array_splice($ids, $key, 1);
            Cache::set('check',$ids);
            $count=count($ids);
            if($count==0){
                Staff::where('id','eq',$id)->update(['check'=>0]);

                Cache::rm('check');
            }else{
                $ids=Cache::get('check');
            }
            
            // dump($ids);die();
        }
        $ids=Cache::get('check');
        return json(['code'=>200,'ids'=>$ids]);
    }


    public function fus(){
        $sid=session('user.id');
        $did=Staff::where('id','eq',$sid)->column('department')[0];
        $pid=Staff::where([['department','eq',$did],['job','eq',28]])->field('id,name')->select();
        return json(['code'=>200,'fu'=>$pid]);
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
        $data=GuideModel::where('bid',$id)->select();
        foreach($data as $v){
            $l=Staff::where('id','eq',$v['s_id'])->column('name');
            if($l){
                $v['name']=$l[0];
            }else{
                $v['name']='没有';
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data
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
        $data=$request->param();
        $data['s_id']=session('user.id');
        GuideModel::update($data,['id'=>$id]);
        $gid=$data['gid'];

        Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_text')->where('gid','eq',$gid)->update(['introduce'=>$data['introduce']]);
        Db::connect('mysql://tpshop:zRitAk6cryrkKJCB@39.98.227.114:3306/tpshop#utf8')->table('tpshop_text')->where('gid','eq',$gid)->update(['introduce'=>$data['introduce']]);
        $res=['code'=>200];
        return json($res);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     *
     */
    public function delete($id)
    {
        //
        $gid=GuideModel::where('id',$id)->column('gid')[0];
        Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_text')->where('gid','eq',$gid)->delete();
        GuideModel::destroy($id);
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
    public function tong(){
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->column('id');
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['building_people','in',$ids];
            }else{
                $where[]=['building_people','eq',session('user.id')];
            }
        }
        
        $tiao=request()->param()['value'];
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Building::where('branch','eq',$tiao['city'])->column('id');
                $where[]=['bid','in',$ids];
            }
        }
        
        $type=request()->param()['type'];
        if($type==1){
            $s=strtotime('-12days');
            $data=Db::name('guide')->where($where)->whereTime('create_time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->field("DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d') as year,count(*) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')")->select();
        }else if($type==2){
            $s=strtotime('-12week');
            $data=Db::name('guide')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as total")
            ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==3){
            $s=strtotime('-12month');
            $data=Db::name('guide')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as total")
            ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==4){
            $s=strtotime('-12quarter');
            $data=Db::name('guide')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as total")
            ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }else if($type==5){
            $s=strtotime('-12year');
            $data=Db::name('guide')->where($where)->whereTime('create_time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as total")
            ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
}
