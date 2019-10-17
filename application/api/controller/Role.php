<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\api\model\Role as RoleModel;

class Role extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $data=RoleModel::select();
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    public function list(){
        $data=RoleModel::field("id,name")->select();
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
        $data=$request->param()['value'];
        
        RoleModel::create($data);
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
        $data=RoleModel::where('id',$id)->find();
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
        $data=$request->param()['value'];
        RoleModel::update($data,['id'=>$id]);
        $res=['code'=>200];
        return json($res);
    }

    // 分配权限
    public function fen(Request $request, $id){
        $data=$request->param()['value'];
        $data=implode(',',$data['ids']);
        RoleModel::update(['ids'=>$data],['id'=>$id]);
        $res=['code'=>200];
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
        RoleModel::destroy($id);
        $res=['code'=>200];
        return json($res);
    }
}
