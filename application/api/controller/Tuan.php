<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\api\model\Tuan as TuanModel;
use app\api\model\Building;

class Tuan extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
        $s=TuanModel::where('bid',$id)->select();
        $res=[
            'code'=>200,
            'data'=>$s
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
    public function update(Request $request)
    {
        //
        $data=$request->param()['value'];
        $bid=$request->param()['bid'];
            $data['bid']=$bid;
            $id=Building::where('id',$bid)->column('building_name')[0];
            $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$id)->find();
            $id=$id['id'];
            TuanModel::create($data,true);
            $data['bid']=$id;
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_tuan')->insert($data);            
        $res=[
            'code'=>200,
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
    }
}
