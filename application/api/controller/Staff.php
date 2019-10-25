<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Validate;
use think\Db;
use app\api\model\Staff as StaffModel;
use app\api\model\Role;
use app\api\model\Qu;

class Staff extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $data=StaffModel::where('enable','eq',1)->select();
        foreach($data as $v){
            if(Role::where('id',$v['job'])->column('name')){
                $v['job']=Role::where('id',$v['job'])->column('name')[0];
            }else{
                $v['job']='';
            }
            $c=Qu::where('id',$v['city'])->column('name');
            if($c){
                $v['city']=$c[0];
            }
            $d=Qu::where('id',$v['department'])->column('name');
            if($d){
                $v['department']=$d[0];
            }
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
    
    public function list($id){
        $data=StaffModel::where('guide','eq',$id+1)->field("id,name")->select();
        return json(['code'=>200,'data'=>$data]);
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
        $data['password']=encrypt_password($data['password']);
        $data['r_time']=substr($data['r_time'],0,10);
        $data['birth1']=substr($data['birth1'],0,10);
        $data['birth2']=substr($data['birth2'],0,10);
        StaffModel::create($data);
        $res=['code'=>200];
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
        $data=StaffModel::where('id',$id)->find();
        $data['job']=Role::where('id',$data['job'])->column('name')[0];
        $data['pid']=StaffModel::where('id',$data['pid'])->column('name');
        if($data['pid']){
            $data['pid']=$data['pid'][0];
        }
        $c=Qu::where('id',$data['city'])->column('name');
            if($c){
                $data['city']=$c[0];
            }
            $d=Qu::where('id',$data['department'])->column('name');
            if($d){
                $data['department']=$d[0];
            }
        $a=Qu::where('id',$data['area'])->column('name');
        if($a){
            $data['area']=$a[0];
        }
        return json(['code'=>200,'data'=>$data]);
    }

    public function my(){
        $data=StaffModel::where('id',session('user.id'))->find();
        $data['job']=Role::where('id',$data['job'])->column('name')[0];
        return json(['code'=>200,'data'=>$data]);
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
        $job=Role::where('name','eq',$data['job'])->column('id');
	
        if($job){
            $data['job']=$job[0];
        }
        $data['id']=$id;
	
        if(array_key_exists('pid',$data)){
	    if($data['pid']==[]){
		$data['pid']=0;
		
	    }
            
        }
	
        Db::table('erp_staff')->where('id',$id)->update($data);
        $res=['code'=>200];
        return json($res);
    }

    public function type(){
        $t=request()->param()['id'];
        $data=StaffModel::where('job','eq',$t)->select();
        foreach($data as $v){
            if(Role::where('id',$v['job'])->column('name')){
                $v['job']=Role::where('id',$v['job'])->column('name')[0];
            }else{
                $v['job']='';
            }
            
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    public function sou(){
        $tiao=request()->param()['value'];
        $where=[];
	$where['enable']=['enable','eq',1];
        if(array_key_exists('name',$tiao)){
            $where[]=['name','like','%'.$tiao['name'].'%'];
        }
        if(array_key_exists('city',$tiao)){
            $where[]=['city','like','%'.$tiao['city'].'%'];
        }
        if(array_key_exists('department',$tiao)){
            $where[]=['department','like','%'.$tiao['department'].'%'];
        }
        if(array_key_exists('job',$tiao)){
            $where[]=['job','eq',$tiao['job']];
        }
        $data=StaffModel::where($where)->select();
        foreach($data as $v){
            if(Role::where('id',$v['job'])->column('name')){
                $v['job']=Role::where('id',$v['job'])->column('name')[0];
            }else{
                $v['job']='';
            }
            $c=Qu::where('id',$v['city'])->column('name');
            if($c){
                $v['city']=$c[0];
            }
            $d=Qu::where('id',$v['department'])->column('name');
            if($d){
                $v['department']=$d[0];
            }
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    public function gang(){
        $data=Role::field('id,name')->select();
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
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
        StaffModel::where('id','eq',$id)->update(['enable'=>0]);
        $res=['code'=>200];
        return json($res);
    }

    // 修改密码
    public function change(){
        $data=request()->param()['value'];
        $new=$data['new'];
        $old=$data['old'];
        $l=StaffModel::where('id','eq',session('user.id'))->column('password')[0];
        $old=encrypt_password($old);
        if($old==$l){
            $new=encrypt_password($new);
            StaffModel::update(['password'=>$new,'id'=>session('user.id')]);
            return json(['code'=>200]);
        }else{
            return json(['code'=>501]);
        }
    }
}
