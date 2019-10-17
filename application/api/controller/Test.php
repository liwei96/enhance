<?php

namespace app\api\controller;
use think\Controller;
use think\facade\Session;
use think\Request;
use app\api\model\Admin;
use app\api\model\Building;
use app\api\model\Area;
use app\api\model\Information;
use function GuzzleHttp\json_decode;


class Test extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

    }

    public function test(Request $request){

        $data=Building::where('id','eq',177)->column('building_img')[0];//实验居
        $time=date('w',time());
        $con=$request->controller();
        $ss=Bb('admin')::select();
        return json($data);
    }

   

    function getids($id){
        static $ids=[];
        $data=Area::where('pid',$id)->select();
        foreach($data as $v){
            $dd=Area::where('pid',$v['id'])->select();
            if($dd){
                $this->getids($v['id']);
                $ids[]=$v['id'];
            }
            $ids[]=$v['id'];
        }
        return $ids;
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        $f=$this->getids(1);
        dump($f);
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
        $where['time'] = array(
    array('egt', strtotime(date('Y-m', time()))),
    array('lt', strtotime(date('Y-m', time()) . '+1 month')),
);


$where['time'] = array(
    array('egt',strtotime(date('Y-m-d',time())).'-'.date('w',time()).' day'),
    array('lt',strtotime(date('Y-m-d',time())).'+1 week -'.date('w',time()).' day')
    );
    
    }
}
