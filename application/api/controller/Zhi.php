<?php

namespace app\api\controller;

use function Complex\negative;
use think\Controller;
use think\Request;
use app\api\model\Zhi as ZhiModel;
use app\api\model\Ping;
use app\api\model\Guide;
use think\Db;

class Zhi extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $mais=ZhiModel::where('type','买房')->select();
        $tus=ZhiModel::where('type','投资')->select();
        $dais=ZhiModel::where('type','贷款')->select();
        $res=[
            'mais'=>$mais,
            'tus'=>$tus,
            'dais'=>$dais,
            'code'=>200
        ];
        return json($res);
    }

    public function type(){
        $type=request()->param()['type'];
        if($type==1){
            $data=ZhiModel::where('type','eq',1)->select();
        }else if($type==2){
            $data=ZhiModel::where('type','eq',2)->select();
        }else if($type==3){
            $data=ZhiModel::where('type','eq',3)->select();
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
    public function sou(){
        $name=request()->param()['title'];
        $data=ZhiModel::where('title','like','%'.$name.'%')->select();
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
        $data['type']=$request->param()['type'];
        ZhiModel::create($data);
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
        $data=ZhiModel::where('id',$id)->find();
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
        ZhiModel::update($data,['id'=>$id]);
        return json(['code'=>200]);
    }

//    /**'
//     * 删除指定资源
//     *
//     * @param  int  $id
//     * @return \think\Response
//     */
//    public function delete($id)
//    {
//        //
//        ZhiModel::destroy($id);
//        return json(['code'=>200]);
//    }


    /**
     * 新增/编辑评论
     * @return \think\response\Json
     */
    public function save_comment(){
        try{
            $content = input('param.content');
	    if(!$content){
		return json(['code'=>500,'message'=>'内容不能为空']);
	    }
            $fuck = file_get_contents('./static/keyWords.txt');
            $keyWord = \FilterWord::strPosFuck($content,$fuck);
            if($keyWord){
                throw new \Exception("输入内容含有敏感词汇({$keyWord}),请重新编辑");
            }
	    
            $id = input('param.id',0);

            $userId = session('user')['id'];
            $bid = input('param.bid');

            $tm = time();
            $ping = Ping::get($id);
            $ping = empty($ping)?new Ping:$ping;

            $ping->content = $content;
            $ping->bid = $bid;
            $ping->create_time = $tm;
            $ping->update_time = $tm;
            $ping->sid = $userId;
            if(!$ping->save()){
               throw new \Exception($ping->getError());
            }
            $pid = $ping->id;

            Db::connect(config('database.db_config1'))
                ->execute("insert into tpshop.tpshop_ping 
 (u_id,bid,content,create_time,update_time,num,pid)
  VALUES($userId,$bid,'$content',{$tm},$tm,1,$pid)");

            return json(['code'=>200,'message'=>'操作成功']);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }

    /**
     * 获取历史评论
     * @return \think\response\Json
     */
    public function comments(){
        try{
            $bid = input('param.bid',0);
            $userId = input('param.userid',0);
            $page = input('param.page',0);
            $limit = input('param.limit',0);
            if(!empty($userId)){
                $userId = " and s.id= $userId  ";
            }else{
                $userId = '';
            }
            $sql = "
SELECT p.id,p.content,p.bid,
FROM_UNIXTIME(p.create_time,'%Y-%m-%d %H:%i:%s') as create_time,
p.sid ,s.name,b.building_name FROM erp.erp_ping p
INNER JOIN erp.erp_staff s on s.id=p.sid
INNER JOIN erp.erp_building b on p.bid =b.id
 where p.status=1 $userId and p.bid = $bid
order by create_time DESC";
            if(!empty($limit)&&!empty($page)){
                $from = ($limit-1)*$page;
                $sql = $sql." limit $from,$page ";
            }

            $comment = Db::query($sql);
            return json(['code'=>200,'data'=>$comment]);
        }catch (\Exception $e){
            return json(['code'=>500,'data'=>[],'message'=>$e->getMessage()]);
        }
    }

    /**
     * 更新评论
     * @return \think\response\Json
     */
    public function deletecomment(){

        try{
            $id = input('param.id',0);

            $num = Db::execute(" delete FROM erp.erp_ping where id= ?",[$id]);
            if(($num == false)){
                throw new \Exception('删除失败');
            }

            //额外删除表
            Db::connect(config('database.db_config1'))
                ->execute(" UPDATE tpshop.tpshop_ping set num = num-1 where pid= ? and num !=0 ",[$id]);

            return json(['code'=>200,'message'=>'删除成功']);
        }catch (\Exception $e){
            return json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }

    // 负责人
    public function fuze(){
        $bid=request()->param('bid');
        $id=request()->param('id');
        Guide::where('bid','eq',$bid)->update(['s_id'=>$id]);
        return json(['code'=>200]);
    }

    // 
}
