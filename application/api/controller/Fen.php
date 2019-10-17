<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\api\model\Fen as FenModel;
use app\api\model\Building;

class Fen extends Controller
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
        $t=request()->param()['type'];
        $data=FenModel::where([['bid','eq',$id],['type','eq',$t]])->find();
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
    public function update(Request $request)
    {
        //
        $data=$request->param()['value'];
        $id=$request->param()['bid'];
        $l=FenModel::where([['bid','eq',$id],['type','eq',$data['type']]])->find();
        $name=Building::where('id',$id)->column('building_name')[0];
        $bid=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$name)->find();
        $bid=$bid['id'];
        $list=$request->param()['value'];
        $list['bid']=$bid;
        $data['bid']=$id;
        if($l){
            FenModel::where([['bid','eq',$id],['type','eq',$data['type']]])->update($data);
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_fen')->where([['bid','eq',$bid],['type','eq',$data['type']]])->update($list);
        }else{
            FenModel::create($data);
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_fen')->insert($list);
        }
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
    public function delete($id)
    {
        //
    }
}
