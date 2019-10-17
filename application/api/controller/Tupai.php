<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\api\model\Tupai as TupaiModel;

class Tupai extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($id)
    {
        //
        $data=TupaiModel::where('bid',$id)->order('id','desc')->select();
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
        $data['bid']=$request->param()['bid'];
        $data['time']=substr($data['time'],0,10);
        TupaiModel::create($data);
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

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        $data=TupaiModel::where('id',$id)->find();
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
        $data['time']=substr($data['time'],0,10);
        TupaiModel::update($data,['id'=>$id]);
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
        TupaiModel::destroy($id);
        return json(['code'=>200]);
    }
}
