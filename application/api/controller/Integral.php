<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\api\model\Integral as IntegralModel;
use app\api\model\Building;

class Integral extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $id=session('user.id');
        $data=Db::query("select * from (select * from erp_integral order by id desc) as data where sid=$id group by bid order by id desc");
        foreach($data as &$v){
            $list=IntegralModel::where('bid',$v['bid'])->order('total_integral','desc')->column('total_integral');
            $v['name']=Building::where('id',$v['bid'])->column('building_name')[0];
            $ll=$v['total_integral'];
            foreach($list as $k=>$n){
                if($ll==$n){
                    $v['pai']=($k+1);
                }
            }
        }
        return json(['code'=>200,'data'=>$data]);
    }
 
    public function sou(){
        $name=request()->param()['names'];
        $id=session('user.id');
        $ids=Building::where('building_name','like','%'.$name.'%')->column('id');
        $ids=implode(',',$ids);
        $data=Db::query("select * from (select * from erp_integral where bid in ($ids) order by id desc) as data where sid=$id group by bid order by id desc");
        foreach($data as &$v){
            $list=IntegralModel::where('bid',$v['bid'])->order('total_integral','desc')->column('total_integral');
            $v['name']=Building::where('id',$v['bid'])->column('building_name')[0];
            $ll=$v['total_integral'];
            foreach($list as $k=>$n){
                if($ll==$n){
                    $v['pai']=($k+1);
                }
            }
        }
        return json(['code'=>200,'data'=>$data]);
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
        $list=IntegralModel::where('bid',$id)->select();
        
        return json(['code'=>200,'data'=>$list]);
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
        IntegralModel::destroy($id);
        return json(['code'=>200]);
    }
}
