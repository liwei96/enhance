<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\api\model\Dai as DaiModel;
use app\api\model\Staff;
use app\api\model\Building;
use app\api\model\User;
use app\api\model\Area;
use app\api\model\Integral;

class Dai extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($id)
    {
        //
        $data=DaiModel::where([['u_id','eq',$id]])->order('id','desc')->select();
        foreach($data as $v){
            $v['name']=Staff::where('id',$v['s_id'])->column('name');
            $v['time']=date('Y-m-d H:i',$v['time']);
            $ids=explode('/',$v['project']);
            $name=[];
            foreach($ids as $j){
                $ll=Building::where('id',$j)->column('building_name');
                if($ll){
                    $name[]=$ll[0];
                }
                
            }
            $name=implode('/',$name);
            $v['project']=$name;
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

    // 统计分析带看列表
    public function tongdailist(){
        $ids=request()->param()['ids'];
        $ids=DaiModel::where('id','in',$ids)->order('time','desc')->column('u_id');
        $ids=array_unique($ids);
        $ids=implode(',',$ids);
        $data=Db::query("select * from erp_user where id in ($ids) order by FIELD(id,$ids)");
        $dd=[];
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            
            if($v['project']){
                $v['project']=$v['project'][0];
            }
            $ll=DaiModel::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('time');
            if($ll){
                $v['d_time']=date('Y-m-d H:i',$ll[0]);
            }
            
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $dd[]=$v;
        }
        $res=[
            'code'=>200,
            'data'=>$dd
        ];
        return json($res);
    }

    // 
    public function list(){
        $data=DaiModel::where('s_id','eq',session('user.id'))->order('id','desc')->select();
        foreach($data as $v){
            $v['name']=User::where('id',$v['u_id'])->column('name');
            $v['from']=User::where('id',$v['u_id'])->column('from');
            $v['people']=Staff::where('id',$v['s_id'])->column('name');
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    // 带看客户列表
    public function duser(){
        $n=request()->param()['n'];
        $y=request()->param()['y'];
        $y=$y-1;
        $where=[];
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
        $ids=DaiModel::where($where)->order('time','desc')->column('u_id');
        $ids=array_unique($ids);
        $ids=implode(',',$ids);
        // $data=User::where('id','in',$ids)->order(FIND_IN_SET(id,$ids))->select();
        $al=$n*$y;
        $data=Db::query("select * from erp_user where id in ($ids) order by FIELD(id,$ids) limit $al,$n");
        $total=User::where('id','in',$ids)->count('id');
        $dd=[];
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            
            if($v['project']){
                $v['project']=$v['project'][0];
            }
            $ll=DaiModel::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('time');
            if($ll){
                $v['d_time']=date('Y-m-d H:i',$ll[0]);
            }
            
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $dd[]=$v;
        }
        $res=[
            'code'=>200,
            'data'=>$dd,
            'total'=>$total
        ];
        return json($res);
    }

    // 带看客户搜索
    public function dusersou(){
        $tiao=request()->param()['value'];
        $n=request()->param()['n'];
        $y=request()->param()['y'];
        $y=$y-1;
        if(session('user.super')!=1){
            if (array_key_exists('time',$tiao)) {
                if($tiao['time']){
                    $start=date('Y-m-d',strtotime($tiao['time'][0])).' 00:00:00';
                    $end=date('Y-m-d',strtotime($tiao['time'][1])).' 23:59:59';
                    if(session('user.guide')!=1){
                        $ids=Staff::where('pid',session('user.id'))->select();
                        $ids=$this->gets($ids);
                        $ids[]=session('user.id');
                        $ids=Db::name('dai')->where('s_id','in',$ids)->whereTime('time',[$start,$end])->column('u_id');
                    }else{
                        $ids=Db::name('dai')->where('s_id','eq',session('user.id'))->column('u_id');
                    }
                }else{
                    if(session('user.guide')!=1){
                        $ids=Staff::where('pid',session('user.id'))->select();
                        $ids=$this->gets($ids);
                        $ids[]=session('user.id');
                        $ids=Db::name('dai')->where('s_id','in',$ids)->column('u_id');
                    }else{
                        $ids=Db::name('dai')->where('s_id','eq',session('user.id'))->column('u_id');
                    }
                }
            }else{
                if(session('user.guide')!=1){
                    $ids=Staff::where('pid',session('user.id'))->select();
                    $ids=$this->gets($ids);
                    $ids[]=session('user.id');
                    $ids=Db::name('dai')->where('s_id','in',$ids)->column('u_id');
                }else{
                    $ids=Db::name('dai')->where('s_id','eq',session('user.id'))->column('u_id');
                }
            }
        }else if(session('user.super')==1){
            if (array_key_exists('time',$tiao)) {
                if($tiao['time']){
                    $start=date('Y-m-d',strtotime($tiao['time'][0])).' 00:00:00';
                    $end=date('Y-m-d',strtotime($tiao['time'][1])).' 23:59:59';
                    $ids=Db::name('dai')->whereTime('time',[$start,$end])->column('u_id');
                }else{
                    $ids=DaiModel::column('u_id');
                }
            }else{
                $ids=DaiModel::column('u_id');
            }
        }
        $ids=array_unique($ids);
        $where=[];
        $where[]=['id','in',$ids];
        
        if (array_key_exists('type',$tiao)) {
            if($tiao['type']){
                $ids=Building::where('building_xingshi','eq',$tiao['type'])->column('id');
                $where[]=['project','in',$ids];
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
        if (array_key_exists('tel',$tiao)) {
            if($tiao['tel']){
                $where[]=['tel','like','%'.$tiao['tel'].'%'];
            }
        }
        if (array_key_exists('port',$tiao)) {
            if($tiao['port']){
                $where[]=['port','eq',$tiao['port']];
            }
        }
        $data=User::where($where)->limit($n*$y,$n)->select();
        $total=User::where($where)->count('id');
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            
            if($v['project']){
                $v['project']=$v['project'][0];
            }
            $ll=DaiModel::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('time');
            if($ll){
                $v['d_time']=date('Y-m-d H:i',$ll[0]);
            }
            
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }
            $dd[]=$v;
        }
        $res=['code'=>200,'data'=>$data,'total'=>$total];
        return json($res);
    }

    // 带看客户类型
    public function dusertype(){
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
        $data['time']=strtotime($data['time']);
        $data['s_id']=session('user.id');
        $data['project']=array_filter($data['project']);
        $uid=$request->param()['u_id'];
        $list=DaiModel::where([['s_id','eq',session('user.id')],['u_id','eq',$uid]])->select();
        $ll=[];
        
        foreach($list as $v){
            $b=explode('/',$v['project']);
            $ll[]=$b;
        }
        $o=[];
        foreach($ll as $v){
            $o=array_merge($o,$v);
        }
        shuffle($o);
        foreach($o as $v){
            foreach($data['project'] as $j){
                if($j==$v){
                    $ss='复看';
                    User::update(['fu'=>$ss,'id'=>$uid]);
                }
            }
        }
        $data['num']=count($data['project']);
        $ids=$data['project'];
       
        // $i=Integral::where([['id','eq',session('user.id')],['bid','eq',$ids[0]]])->order('id','desc')->limit(0,1)->select();
        // foreach($i as $k=>$v){
        //     if($k==0){
        //         dump($v);die();
        //     }
        //     dump($k);
        // }
        // dump($i);dump($i);die();
        // if($i){
        //     dump(1);
        //     die();
        //     $i=$i[0];
        // die();
        if(count($ids)>1){
            $ss='一客多看';
            User::update(['label'=>$ss,'id'=>$uid]);
            $n=count($ids);
            // $a=[];
            // $a['sid']=session('user.id');
            // $a['integral']="+$n";
            // $a['total_integral']=$i['total_integral']+$n;
            // $a['action']='一客多看';
            // $a['bid']=$ids[0];
            // Integral::create($a);
        } 
        $data['project']=implode('/',$data['project']);
        $data['u_id']=$uid;
        $data['type']=$request->param('type');
        $time=User::where('id',$data['u_id'])->column('create_time')[0];
        if(time()-$time<(3600*24*3)){
            $data['label']='新客';
            $ss='新客';
            User::update(['dai'=>$ss,'id'=>$uid]);
            // $a=[];
            // $a['sid']=session('user.id');
            // $a['integral']='+5';
            // $a['total_integral']=$i['total_integral']+5;
            // $a['action']='新客带看';
            // $a['bid']=$ids[0];
            // Integral::create($a);
        }else if(time()-$time>(3600*24*3)){
            $data['label']='老客';
            $ss='老客';
            User::update(['dai'=>$ss,'id'=>$uid]);
            // $a=[];
            // $a['sid']=session('user.id');
            // $a['integral']='+2';
            // $a['total_integral']=$i['total_integral']+2;
            // $a['action']='老客带看';
            // $a['bid']=$ids[0];
            // Integral::create($a);
        }
    // }else{
        // if(count($ids)>1){
        //     $ss=User::where('id',$uid)->column('label');
        //     if($ss){
        //         $ss=$ss[0];
        //         $ss=explode(',',$ss);
        //         if(!in_array('一客多看',$ss)){
        //             $ss=$ss.',一客多看';
        //             User::update(['label'=>$ss,'id'=>$uid]);
        //         }
        //     }else{
        //         $ss='一客多看';
        //         User::update(['label'=>$ss,'id'=>$uid]);
        //     }
        //     $n=count($ids);
        //     $a=[];
        //     $a['sid']=session('user.id');
        //     $a['integral']="+$n";
        //     $a['total_integral']=$n;
        //     $a['action']='一客多看';
        //     $a['bid']=$ids[0];
        //     Integral::create($a);
        // }
        // $data['project']=implode('/',$data['project']);
        // $data['u_id']=$uid;
        // $data['type']=$request->param('type');
        // $time=User::where('id',$data['u_id'])->column('create_time')[0];
        // if(time()-$time<(3600*24*3)){
        //     $data['label']='新客';
        //     $a=[];
        //     $a['sid']=session('user.id');
        //     $a['integral']='+5';
        //     $a['total_integral']=5;
        //     $a['action']='新客带看';
        //     $a['bid']=$ids[0];
        //     Integral::create($a);
        // }else if(time()-$time>(3600*24*3)){
        //     $data['label']='老客';
        //     $a=[];
        //     $a['sid']=session('user.id');
        //     $a['integral']='+2';
        //     $a['total_integral']=2;
        //     $a['action']='老客带看';
        //     $a['bid']=$ids[0];
        //     Integral::create($a);
        // }
    // }
        $user=DaiModel::create($data);
        $id=$user->id;
        foreach($ids as $v){
            if($v){
                $ll=Building::where('id',$v)->column('d_id');
                if($ll){
                    $ll=$ll[0];
                    $ll=explode(',',$ll);
                    if(!in_array($id,$ll)){
                        $ll[]=$id;
                    }
                    $ll=implode(',',$ll);
                }else{
                    $ll=$id;
                }
                Building::update(['d_id'=>$ll,'id'=>$v]);
            }
            
        }
        return json(['code'=>200]);
    }

    /**
     * 显示指定的资源
     *   带看的图片
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
        $img=request()->param()['img'];
        $img=array_filter($img);
        $img=implode(',',$img);
        DaiModel::where('id',$id)->update(['img'=>$img]);
        return json(['code'=>200]);
    }
    // 带看图片展示
    public function reimg($id){
        $img=DaiModel::where('id',$id)->column('img');
        if($img){
            $img=explode(',',$img[0]);
        }
        return json(['code'=>200,'data'=>$img]);
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
        $data=DaiModel::where('id',$id)->find();
        $data['project']=explode('/',$data['project']);
        $name=[];
        foreach($data['project'] as $v){
            $name[]=Building::where('id',$v)->column('building_name');
        }
        $data['time']=date('Y-m-d H:i',$data['time']);
        
        $res=[
            'code'=>200,
            'data'=>$data,
            'name'=>$name
        ];
        return json($res);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     * /www/server/php/73/bin/phpize
     * /usr/local/php-5.2.17/bin/phpize
     * ./configure --with-php-config=/www/server/php/73/bin/php-config
     * extension=/usr/local/php/lib/php/extensions/no-debug-non-zts-20060613/zip.so
     */
    public function update(Request $request, $id)
    {
        //
        $data=$request->param()['value'];
        $data['time']=strtotime($data['time']);
        $data['project']=array_filter($data['project']);
        $data['num']=count($data['project']);
        $data['project']=implode('/',$data['project']);
        DaiModel::update($data,['id'=>$id]);
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
        //
        $ids=DaiModel::where('id',$id)->column('project')[0];
        $ids=explode('/',$ids);
        foreach($ids as $v){
            $data=Building::where('id',$v)->column('d_id')[0];
            $data=explode(',',$data);
            foreach($data as $k=>$j){
                if($j==$id){
                    unset($data[$k]);
                }
            }
            $data=implode(',',$data);
            Building::update(['d_id'=>$data,'id'=>$v]);
        }

        DaiModel::destroy($id);
        return json(['code'=>200]);
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
    // 项目带看量
    public function tong(){
        $type=request()->param()['type'];
        $where=[];
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->select();
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
        
        if($type==1){
            $s=strtotime('-12days');
            $data=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->field("DATE_FORMAT(FROM_UNIXTIME(time),'%d') as date,count(*) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')")->select();
            $ids=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->column('id');
            $list=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->field("DATE_FORMAT(FROM_UNIXTIME(time),'%d') as date,sum(num) as s")
            ->group("DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')")->select();
        }else if($type==2){
            $s=strtotime('-12week');
            $data=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,count(*) as total")
            ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $list=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,sum(num) as s")
            ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $ids=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->column('id');
        }else if($type==3){
            $s=strtotime('-12month');
            $data=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,count(*) as total")
            ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $list=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,sum(num) as s")
            ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $ids=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->column('id');
        }else if($type==4){
            $s=strtotime('-4quarter');
            $data=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,count(*) as total")
            ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $list=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,sum(num) as s")
            ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $ids=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->column('id');
        }else if($type==5){
            $s=strtotime('-12year');
            $data=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,count(*) as total")
            ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $list=Db::name('dai')->where($where)->whereTime('time',[date('Y-m-d',$s),date('Y-m-d',time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d')) as date,sum(num) as s")
            ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d'))")->select();
            $ids=Db::name('dai')->where($where)->whereTime('time', 'between', [date('Y-m-d',$s), date('Y-m-d',time())])->column('id');
        }
        
        $res=[
            'code'=>200,
            'data'=>$data,
            'list'=>$list,
            'ids'=>$ids
        ];
        return json($res);
    }

    public function tonglist(){
        $ids=request()->param()['ids'];
        $n=request()->param()['n'];
        $y=request()->param()['y'];
        $y=$y-1;
        $data=DaiModel::where('id','in',$ids)->limit($y*$n,$n)->select();
        $num=DaiModel::where('id','in',$ids)->count('id');
        foreach($data as $v){
            $ps=explode('/',$v['project']);
            $project=Building::where('id','in',$ps)->column('building_name');
            $v['project']=implode('/',$project);
            $v['name']=User::where('id','eq',$v['u_id'])->column('name');
            if($v['name']){
                $v['name']=$v['name'][0];
            }else{
                $v['name']='未定义';
            }
            $v['staff']=Staff::where('id','eq',$v['s_id'])->column('name');
            if($v['staff']){
                $v['staff']=$v['staff'][0];
            }else{
                $v['staff']='不知道什么情况';
            }

            $v['department']=Staff::where('id','eq',$v['s_id'])->column('department');
            if($v['department']){
                $v['department']=$v['department'][0];
            }else{
                $v['department']='未选定';
            }
            $v['deng']=User::where('id','eq',$v['u_id'])->column('create_time');
            if($v['deng']){
                $v['deng']=$v['deng'][0];
            }else{
                $v['deng']=time();
            }
            $v['deng']=date('Y-m-d',$v['deng']);
            $v['time']=date('Y-m-d',$v['time']);
        }
        $res=[
            'code'=>200,
            'data'=>$data,
            'total'=>$num
        ];
        return json($res);
    }
    // 新老款带看量
    public function utong(){
        $where=[];
        if(session('user.super')!=1){
            if(session('user.guide')!=1){
                $ids=Staff::where('pid','eq',session('user.id'))->select();
                $ids=$this->gets($ids);
                $ids[]=session('user.id');
                $where[]=['s_id','in',$ids];
            }else{
                $where[]=['s_id','eq',session('user.id')];
            }
        }
        
        $type=request()->param()['type'];
        if (array_key_exists('value',request()->param())) {
            $tiao=request()->param()['value'];
        }else{
            $tiao=[];
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
        if($type==1){
            $newids=Db::name('user')->where($where)->whereTime('create_time','week')->column('id');
            $oldids=Db::name('user')->where($where)->whereTime('create_time','week')->column('id');
            if($newids){
                $new=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->count("*");
                $newids=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->column("id");
            }else{
                $new=0;
                $newids=[];
            }
            if($oldids){
                $old=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->count('*');
                $oldids=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->column('id');
            }else{
                $old=0;
                $oldids=[];
            }
           
        }else if($type==2){
            $newids=Db::name('user')->where($where)->whereTime('create_time','month')->column('id');
            $oldids=Db::name('user')->where($where)->whereTime('create_time','month')->column('id');
            if($newids){
                $new=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->count("*");
                $newids=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->column("id");
            }else{
                $new=0;
                $newids=[];
            }
            if($oldids){
                $old=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->count('*');
                $oldids=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->column('id');
            }else{
                $old=0;
                $oldids=[];
            }
        }else if($type==3){
            $newids=Db::name('user')->where($where)->whereTime('create_time','quarter')->column('id');
            $oldids=Db::name('user')->where($where)->whereTime('create_time','quarter')->column('id');
            if($newids){
                $new=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->count("*");
                $newids=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->column("id");
            }else{
                $new=0;
                $newids=[];
            }
            if($oldids){
                $old=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->count('*');
                $oldids=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->column('id');
            }else{
                $old=0;
                $oldids=[];
            }
        }else if($type==4){
            $newids=Db::name('user')->where($where)->whereTime('create_time','year')->column('id');
            $oldids=Db::name('user')->where($where)->whereTime('create_time','year')->column('id');
            if($newids){
                $new=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->count("*");
                $newids=DaiModel::where([['u_id','in',$newids],['label','eq','新客']])->column("id");
            }else{
                $new=0;
                $newids=[];
            }
            if($oldids){
                $old=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->count('*');
                $oldids=DaiModel::where([['u_id','in',$oldids],['label','eq','老客']])->column('id');
            }else{
                $old=0;
                $oldids=[];
            }
        }
        $n=[];
        $l=[
            'item'=>'新客',
            'count'=>$new
        ];
        $l=json_encode($l);
        $k=[
            'item'=>'老客',
            'count'=>$old
        ];
        $k=json_encode($k);
        $n[]=$l;
        $n[]=$k;
        $newids=DaiModel::where('id','in',$newids)->column('u_id');
        $newids=array_unique($newids);
        $oldids=DaiModel::where('id','in',$oldids)->column('u_id');
        $oldids=array_unique($oldids);
        $res=[
            'code'=>200,
            'n'=>$n,
            'newids'=>$newids,
            'oldids'=>$oldids
        ];
        return json($res);
    }
    // 新客带看列表
    public function xinlist(){
        $ids=request()->param()['ids'];
        $data=User::where('id','in',$ids)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name')[0];
            $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            if($tt){
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
            }
           
            
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
}
