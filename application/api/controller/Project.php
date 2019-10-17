<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Validate;
use think\Image;
use think\Db;
use think\facade\Env;
use app\api\model\Building;
use app\api\model\Area;
use app\api\model\Attribute;
use app\api\model\Jiaoimgs;
use app\api\model\Huimgs;
use app\api\model\Yangimgs;
use app\api\model\Xiaoimgs;
use app\api\model\Shiimgs;
use app\api\model\Peiimgs;
use app\api\model\Tuan;
use app\api\model\Zong;
use app\api\model\Ditie;
use app\api\model\Huxing;
use app\api\model\Tese;
use app\api\model\Dai;
use app\api\model\Humian;
use app\api\model\Record;
use app\api\model\Guide;
use app\api\model\Staff;
use app\api\model\User;





class Project extends Controller
{
    
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $t=request()->param();
        $y=$t['y'];
        $n=$t['n'];
        $y=$y-1;
        $list = Building::order(['id'=>'desc','shen'=>'desc'])->limit($y*$n,$n)->select();
        $num=Building::count('id');
        foreach ($list as $v) {
            $n = Area::where('id', $v['cate_id'])->column('pid');
            if($n){
                $n=$n[0];
                $s = Area::where('id', $n)->column('pid')[0];
                $v['city'] = Area::where('id', $n)->column('area_name')[0];
                $v['provice'] = Area::where('id', $s)->column('area_name')[0];
            }
            $v['jin']=User::where('project','eq',$v['id'])->count('id');
            $v['dai']=Dai::where('project','eq',$v['id'])->count('id');
            $l=Guide::where('bid','eq',$v['id'])->limit(0,1)->column('s_id');
            if($l){
                $l=$l[0];
                $k=Staff::where('id','eq',$l)->column('name');
                if($k){
                    $v['fu']=$k[0];
                }else{
                    $v['fu']='';
                }
            }else{
                $v['fu']='';
            }
        }
        $res=[
            'code'=>200,
            'data'=>$list,
            'total'=>$num
        ];
        return json($res);
    }
    public function dlist($id)
    {
        $ids = Building::where('id', $id)->column('d_id')[0];
        $ids = explode(',', $ids);
        $data = Dai::where('id', 'in', $ids)->select();
        foreach($data as $v){
            $v['time']=date('Y-m-d H:i',$v['time']);
	    $v['s_id']=Staff::where('id','eq',$v['s_id'])->find();
            if($v['s_id']){
                $v['s_id']=$v['s_id']['name'];
            }
        }
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    public function clist($id)
    {
        $data = Record::where('project', $id)->select();
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    // 推广等级搜索
    public function gtype()
    {
        $type = request()->param()['type'];
        $data = Building::where('project_extend_dengji', 'eq', $type)->select();
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    // 推广项目名字搜索
    public function tsou()
    {
        $tiao=request()->param()['value'];
        $where=[];
        $where[]=['isdeng','eq','是'];
        if (array_key_exists('building_name',$tiao)) {
            if($tiao['building_name']){
                $where[]=['building_name', 'like', '%' . $tiao['building_name'] . '%'];
            }
        }
        if (array_key_exists('projectdengji',$tiao)) {
            if($tiao['projectdengji']){
                $where[]=['project_extend_dengji','eq', $tiao['projectdengji']];
            }
        }
        if (array_key_exists('branch',$tiao)) {
            if($tiao['branch']){
                $where[]=['branch','eq', $tiao['branch']];
            }
        }
        $data=Building::where($where)->order('branch','asc')->select();
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    public function list()
    {
        $data = Building::field("id,building_name")->select();
        $res = ['code' => 200, 'name' => $data];
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
        $data = Attribute::select();
        $xiao = explode(',', $data[0]['content']);
        $zhuang = explode(',', $data[1]['content']);
        $ditie = Ditie::column('ditie');
        $huxing = Huxing::column('huxing');
        $xing = explode(',', $data[4]['content']);
        $tese = Tese::column('tese');
        $zong = Zong::column('zong');
        $dan = explode(',', $data[7]['content']);
        $yang = explode(',', $data[8]['content']);
        $min = explode(',', $data[9]['content']);
        $ran = explode(',', $data[10]['content']);
        $tui = explode(',', $data[11]['content']);
        $res = [
            'code' => 200,
            'xiao' => $xiao,
            'zhuang' => $zhuang,
            'ditie' => $ditie,
            'huxing' => $huxing,
            'xing' => $xing,
            'tese' => $tese,
            'zong' => $zong,
            'dan' => $dan,
            'yang' => $yang,
            'min' => $min,
            'ran' => $ran,
            'tui' => $tui
        ];
        return json($res);
    }

    public function getSubCate()
    {
        $list = Area::where('pid', 0)->select();

        $data = [];
        foreach ($list as $v) {
            $data[$v['area_name']] = Area::where('pid', $v['id'])->select();
        }
        $ss = [];
        foreach ($data as $k => $v) {
            foreach ($v as $l) {
                $ss[$k][$l['area_name']] = Area::where('pid', $l['id'])->column('area_name');
            }
        }

        $res = [
            'code' => 200,
            'msg' => '数据获取成功',
            'data' => $ss
        ];
        return json($res);
    }

    // 单个照片上传
    public function test()
    {
        $data = request()->param()['data'];
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < 8; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        $lujing = './uploads/' . date('Ymd');
        if (!is_dir($lujing)) {
            mkdir(iconv("UTF-8", "GBK", $lujing), 0777, true);
        }
        $type = explode(',', $data)[0];
        $type = explode(';', $type)[0];
        $type = explode(':', $type)[1];
        $type = explode('/', $type)[1];
        $newFilePath = '/uploads/' . date('Ymd') . '/' . $str . '.' . $type;

        $dd = explode(',', $data)[1]; //得到post过来的二进制原始数据
        if (empty($dd)) {
            $data = file_get_contents("php://input");
        }
        $r = file_put_contents('.' . $newFilePath, base64_decode($dd));
        clearstatcache();
        return json([
            'code' => 200,
            'url' => 'api.jy1980.com'.$newFilePath
        ]);
    }
    public function ones()
    {
        $list = Area::where('pid', 0)->select();
        $res = [
            'code' => 200,
            'list' => $list
        ];
        return json($res);
    }


    public function getareas($id)
    {
        if (empty($id)) {
            $res = [
                'code' => 10000,
                'msg' => '参数错误'
            ];
            return json($res);
        }
        $list = Area::where('pid', $id)->select();
        $res = [
            'code' => 200,
            'msg' => '数据获取成功',
            'data' => $list
        ];
        return json($res);
    }
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     * 
     */
    public function save(Request $request)
    {
        //
        $data = $request->param();
        if(array_key_exists('hetong',$data)){
            $hetong = $request->param()['hetong'];
            $hetong = implode(',', $hetong);
        }
        
        $img = $request->param()['img'];
        
        $data = $data['value'];
        $data['hetong'] = $hetong;
        $data['building_img'] = $img;
        $rule = [
            'building_name' => 'require',
            'building_address' => 'require',

            'building_xiaoshou' => 'require',
            'cate_id' => 'require',
            'building_jiage' => 'require',
            'zongjia' => 'require',
            'building_zhuangxiu' => 'require',
            'building_ditie' => 'require',
            'building_huxing' => 'require',
            'building_tese' => 'require',
            'building_xingshi' => 'require',
            'introduce' => 'require',
            'traffic' => 'require',
            'hushu' => 'require',
            'guiji' => 'require',
            'rongji' => 'require',
            'jianji' => 'require',
            'zong' => 'require',

            'channian' => 'require',
            'jiaotime' => 'require',
            'mapx' => 'require',
            'mapy' => 'require',
            'cenggao' => 'require',
            'humianji' => 'require',
            'wufei' => 'require',
            'chewei' => 'require',
            'shoulou' => 'require',
            'wuye' => 'require',
            'kaifa' => 'require',
            'lvhua' => 'require',
        ];
        $msg = [
            'building_name.require' => '项目名不能为空',
            'building_address.require' => '项目地址不能为空',

            'building_xiaoshou.require' => '销售状态不能为空',
            'cate_id.require' => '项目所在城市不能为空',
            'building_jiage.require' => '项目的单价等级没选',
            'zongjia.require' => '项目的总价等级没选',
            'building_zhuangxiu.require' => '项目的装修情况没选',
            'building_ditie.require' => '临近地铁没选',
            'building_huxing.require' => '项目有哪些户型没选',
            'building_tese.require' => '项目特色没选',
            'building_xingshi.require' => '项目形式没选',
            'introduce.require' => '项目介绍没写',
            'traffic.require' => '到楼盘的交通没写',
            'hushu.require' => '项目有多少户没写',
            'guiji.require' => '规划面积没写',
            'rongji.require' => '容积率没写',
            'jianji.require' => '建筑面积没写',
            'zong.require' => '最低总价没写',

            'channian.require' => '产权年限没写',
            'jiaotime.require' => '交房时间没写',
            'mapx.require' => '楼盘经度没写',
            'mapy.require' => '楼盘维度没写',
            'cenggao.require' => '层高没写',
            'humianji.require' => '户面积没写',
            'wufei.require' => '物业费没写',
            'chewei.require' => '车位数没写',
            'shoulou.require' => '售楼处地址没写',
            'wuye.require' => '物业公司没写',
            'kaifa.require' => '开发商没写',
            'lvhua.require' => '绿化率没写',
        ];
        $validate = new Validate($rule, $msg);
        if(!$validate->check($data)){
            $error=$validate->getError();
            $res=['code'=>300,'msg'=>$error];
            return json($error);
        } 
        $di=[];
        foreach($data['building_ditie'] as $v){
            $di[]=Ditie::where('id',$v)->column('ditie')[0];
        }
        $di=implode(',',$di);
        $hu=[];
        foreach($data['building_huxing'] as $v){
            $hu[]=Huxing::where('id',$v)->column('huxing')[0];
        }
        $hu=implode(',',$hu);
        $te=[];
        foreach($data['building_tese'] as $v){
            $te[]=Tese::where('id',$v)->column('tese')[0];
        }
        $te=implode(',',$te);
        $zong=$data['zongjia'][0];
        
        $data['building_ditie'] = implode(',', $data['building_ditie']);
        $data['building_huxing'] = implode(',', $data['building_huxing']);
        $data['building_tese'] = implode(',', $data['building_tese']);
        $cate_id = Area::where('area_name', 'eq', $data['cate_id'][2])->column('id')[0];
        $data['cate_id'] = $cate_id;
        $data['humianji'] = implode(',', $data['humianji']);
        $data['zongjia'] = implode(',', $data['zongjia']);
        if(array_key_exists('hetong_time',$data)){
            $data['hetong_time'] = substr($data['hetong_time'], 0, 10);
        }
        if(array_key_exists('t_kaitime',$data)){
            $data['t_kaitime'] = substr($data['t_kaitime'], 0, 10);
        }
        if(array_key_exists('jiaotime',$data)){
            $data['jiaotime'] = substr($data['jiaotime'], 0, 10);
        }
        if(array_key_exists('o_kaitime',$data)){
            $data['o_kaitime'] = substr($data['o_kaitime'], 0, 10);
        }
        $data['building_people']=session('user.id');
        $b=[];
        $b['building_name']=$data['building_name'];
        $b['building_people']=$data['building_people'];
        $b['cate_id']=$data['cate_id'];
        $b['building_address']=$data['building_address'];
        $b['building_img']='http://'.$data['building_img'];
        $b['building_jiage']=$data['building_jiage'];
        $b['building_xiaoshou']=$data['building_xiaoshou'];
        $b['building_xingshi']=$data['building_xingshi'];
        $b['building_zhuangxiu']=$data['building_zhuangxiu'];
        $b['cenggao']=$data['cenggao'];
        $b['mapx']=$data['mapx'];
        $b['mapy']=$data['mapy'];
        $b['wufei']=$data['wufei'];
        $b['building_tejia']=$data['building_tejia'];
        $b['chewei']=$data['chewei'];
        $b['wuye']=$data['wuye'];
        $b['kaifa']=$data['kaifa'];
        $b['lvhua']=$data['lvhua'];
        $b['rongji']=$data['rongji'];
        $b['jianji']=$data['jianji'];
        $b['hushu']=$data['hushu'];
        $b['preferential']=$data['preferential'];
        $b['traffic']=$data['traffic'];
        $b['introduce']=$data['introduce'];
        $b['humianji']=$data['taomian'];
        $b['danjia']=$data['danjia'];
        $b['shoulou']=$data['shoulou'];
        $b['jiaotime']=$data['jiaotime'];
        $b['channian']=$data['channian'];
        $b['yushou']=$data['yushou'];
        $b['keys']=$data['keys'];
        $b['zong']=$data['zong'];
        $b['zongjia']=$zong;
        $b['building_tese']=$te;
        $b['building_huxing']=$hu;
        $b['building_ditie']=$di;
        $b['kaitime']=$data['o_kaitime'];
        $b['n_time']=$data['nadi_time'];
        $b['transfer']=$data['transfer'];
        $b['tdeng']=$data['project_extend_dengji'];
        $fenji=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_tell')->where('name','eq',$data['building_name'])->find();
        if(!$fenji){
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_tell')->insert(['transfer'=>$data['transfer'],'name'=>$data['building_name']]);
        }
        $ll=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->insert($b);
        unset($b['tdeng']);
        $ll=Db::connect('mysql://tpshop:zRitAk6cryrkKJCB@127.0.0.1:3306/tpshop#utf8')->table('tpshop_goods')->insert($b);
        $re = Building::create($data);
        if (!$re) {
            $res = ['code' => 301, 'msg' => '新增失败'];
            return json($res);
        } else {
            $res = ['code' => 200];
            return json($res);
        }
    }
    // 审核
    public function shen()
    {
        $data = request()->param();
        $type = $data['type'];
        if ($type == 1) {
            Building::update(['id' => $data['id'], 'shen' => 0]);
        } else if ($type == 2) {
            Building::update(['id' => $data['id'], 'shen' => 1, 'reason' => $data['value']]);
        }
        return json(['code' => 200]);
    }


    public function saveimgs()
    {
        $id = request()->param()['bid'];
        $this->upload_y($id);
        $this->upload_p($id);
        $this->upload_s($id);
        $this->upload_x($id);
        $this->upload_h($id);
        $this->upload_j($id);
        $res = ['code' => 200];
        return json($res);
    }


    public function tui()
    {
            $data = Building::where('isdeng','eq', '是')->select();
            $res = [
                'cdoe' => 200,
                'data' => $data
            ];
            return json($res);
       
    }
    

    
    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function sou()
    {
        //
        $t=request()->param();
        $n=$t['n'];
        $y=$t['y'];
        $y=$y-1;
        $ss = request()->param()['value'];
        $where = [];
        if (array_key_exists('building_xingshi',$ss)) {
            if($ss['building_xingshi']){
                $where[] = ['building_xingshi', 'eq', $ss['building_xingshi']];
            }
        }
        if (array_key_exists('building_name',$ss)) {
            if($ss['building_name']){
                $where[] = ['building_name', 'like', '%' . $ss['building_name'] . '%'];
            }
        }
        if (array_key_exists('cate_id',$ss)) {
            if($ss['cate_id']){
                $l=$ss['cate_id'][2];
                $id=Area::where('area_name','eq',$l)->column('id')[0];
                $where[] = ['cate_id', 'eq', $id];
            }
        }
        if (array_key_exists('type',$ss)) {
            if($ss['type']){
                if(array_key_exists('num',$ss)){
                    if($ss['num']){
                        $where[]=[$ss['type'],'in',$ss['num']];
                    }
                }
            }
        }
        if (array_key_exists('huxing',$ss)) {
            if($ss['huxing']){
                $where[] = ['building_huxing', 'in', $ss['huxing']];
            }
        }
        $data = Building::where($where)->limit($y*$n,$n)->select();
        foreach ($data as $v) {
            $n = Area::where('id', $v['cate_id'])->column('pid');
            if($n){
                $n=$n[0];
                $s = Area::where('id', $n)->column('pid')[0];
                $v['city'] = Area::where('id', $n)->column('area_name')[0];
                $v['provice'] = Area::where('id', $s)->column('area_name')[0];
            }
            $v['jin']=User::where('project','eq',$v['id'])->count('id');
            $v['dai']=Dai::where('project','eq',$v['id'])->count('id');
            $l=Guide::where('bid','eq',$v['id'])->limit(0,1)->column('s_id');
            if($l){
                $l=$l[0];
                $k=Staff::where('id','eq',$l)->column('name');
                if($k){
                    $v['fu']=$k[0];
                }else{
                    $v['fu']='';
                }
                
            }else{
                $v['fu']='';
            }
        }
        $num=Building::where($where)->count('id');
        $res = [
            'code' => 200,
            'data' => $data,
            'total'=>$num
        ];
        return json($res);
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
        $dd = Building::where('id', $id)->find();
        $dd['building_ditie'] = explode(',', $dd['building_ditie']);
        $dd['building_huxing'] = explode(',', $dd['building_huxing']);
        $dd['building_xingshi'] = explode(',', $dd['building_xingshi']);
        $dd['building_tese'] = explode(',', $dd['building_tese']);
        $dd['humianji']=explode(',',$dd['humianji']);
        $dd['zongjia']=explode(',',$dd['zongjia']);
        $dd['hetong'] = explode(',', $dd['hetong']);
        $one = Area::where('id', $dd['cate_id'])->find();
        $two = Area::where('id', $one['pid'])->find();
        $thr = Area::where('id', $two['pid'])->column('area_name');
        $dd['cate_id'] = [$thr[0], $two['area_name'], $one['area_name']];
        $res = [
            'code' => 200,
            'dd' => $dd
        ];
        return json($res);
    }


    // 相册显示
    public function editpic($id){
        $j_imgs=Jiaoimgs::where('bid',$id)->select();
        $h_imgs=Huimgs::where('bid',$id)->select();
        $x_imgs=Xiaoimgs::where('bid',$id)->select();
        $p_imgs=Peiimgs::where('bid',$id)->select();
        $s_imgs=Shiimgs::where('bid',$id)->select();
        $y_imgs=Yangimgs::where('bid',$id)->select();
        $res=[
            'j'=>$j_imgs,
            'h'=>$h_imgs,
            'x'=>$x_imgs,
            'p'=>$p_imgs,
            's'=>$s_imgs,
            'y'=>$y_imgs,
            'code'=>200
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
        $ll=$request->param();
        
        $data = $ll['value'];
        if(array_key_exists('hetong',$ll)){
            if($ll['hetong']){
                $hetong = $request->param()['hetong'];
                $hetong = implode(',', $hetong);
                $data['hetong'] = $hetong;
            }
        }
        if(array_key_exists('building_img',$ll)){
            $img = $request->param()['building_img'];
            $data['building_img'] = $img;
        }
        
        $rule = [
            'building_name' => 'require',
            'building_address' => 'require',

            'building_xiaoshou' => 'require',
            'building_jiage' => 'require',
            'zongjia' => 'require',
            'building_zhuangxiu' => 'require',
            'building_ditie' => 'require',
            'building_huxing' => 'require',
            'building_tese' => 'require',
            'building_xingshi' => 'require',
            'introduce' => 'require',
            'traffic' => 'require',
            'hushu' => 'require',
            'guiji' => 'require',
            'rongji' => 'require',
            'jianji' => 'require',
            'zong' => 'require',

            'channian' => 'require',
            'jiaotime' => 'require',
            'mapx' => 'require',
            'mapy' => 'require',
            'cenggao' => 'require',
            'humianji' => 'require',
            'wufei' => 'require',
            'chewei' => 'require',
            'shoulou' => 'require',
            'wuye' => 'require',
            'kaifa' => 'require',
            'lvhua' => 'require',
        ];
        $msg = [
            'building_name.require' => '项目名不能为空',
            'building_address.require' => '项目地址不能为空',

            'building_xiaoshou.require' => '销售状态不能为空',
            'building_jiage.require' => '项目的单价等级没选',
            'zongjia.require' => '项目的总价等级没选',
            'building_zhuangxiu.require' => '项目的装修情况没选',
            'building_ditie.require' => '临近地铁没选',
            'building_huxing.require' => '项目有哪些户型没选',
            'building_tese.require' => '项目特色没选',
            'building_xingshi.require' => '项目形式没选',
            'introduce.require' => '项目介绍没写',
            'traffic.require' => '到楼盘的交通没写',
            'hushu.require' => '项目有多少户没写',
            'guiji.require' => '规划面积没写',
            'rongji.require' => '容积率没写',
            'jianji.require' => '建筑面积没写',
            'zong.require' => '最低总价没写',

            'channian.require' => '产权年限没写',
            'jiaotime.require' => '交房时间没写',
            'mapx.require' => '楼盘经度没写',
            'mapy.require' => '楼盘维度没写',
            'cenggao.require' => '层高没写',
            'humianji.require' => '户面积没写',
            'wufei.require' => '物业费没写',
            'chewei.require' => '车位数没写',
            'shoulou.require' => '售楼处地址没写',
            'wuye.require' => '物业公司没写',
            'kaifa.require' => '开发商没写',
            'lvhua.require' => '绿化率没写',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            $error = $validate->getError();
            $res = ['code' => 300, 'msg' => $error];
            return json($error);
        }

        $di=[];
        foreach($data['building_ditie'] as $v){
            $di[]=Ditie::where('id',$v)->column('ditie')[0];
        }
        $di=implode(',',$di);
        $hu=[];
        foreach($data['building_huxing'] as $v){
            $hu[]=Huxing::where('id',$v)->column('huxing')[0];
        }
        $hu=implode(',',$hu);
        $te=[];
        foreach($data['building_tese'] as $v){
            $te[]=Tese::where('id',$v)->column('tese')[0];
        }
        $te=implode(',',$te);
        $zong=$data['zongjia'][0];
        $data['building_xingshi']=$data['building_xingshi'][0];
        $data['building_ditie'] = implode(',', $data['building_ditie']);
        $data['building_huxing'] = implode(',', $data['building_huxing']);
        $data['building_tese'] = implode(',', $data['building_tese']);
        $cate_id = Area::where('area_name', 'eq', $data['cate_id'][2])->column('id')[0];
        $data['cate_id'] = $cate_id;
        $data['humianji'] = implode(',', $data['humianji']);
        $data['zongjia'] = implode(',', $data['zongjia']);
        $data['hetong_time'] = substr($data['hetong_time'], 0, 10);
        $data['t_kaitime'] = substr($data['t_kaitime'], 0, 10);
        $data['jiaotime'] = substr($data['jiaotime'], 0, 10);
        $data['o_kaitime'] = substr($data['o_kaitime'], 0, 10);
        $data['building_people']=session('user.id');
        $b=[];
        $gid=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$data['building_name'])->find();
        $yid=Db::connect('mysql://tpshop:zRitAk6cryrkKJCB@127.0.0.1:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$data['building_name'])->find();
        $gid=$gid['id'];
        $yid=$yid['id'];
        $b['building_name']=$data['building_name'];
        $b['building_people']=$data['building_people'];
        $b['cate_id']=$data['cate_id'];
        $b['building_address']=$data['building_address'];
        if(array_key_exists('building_img',$data)){
            if($data['building_img']){
                $b['building_img']='http://'.$data['building_img'];
            }
        }
        $b['building_jiage']=$data['building_jiage'];
        $b['building_xiaoshou']=$data['building_xiaoshou'];
        $b['building_xingshi']=$data['building_xingshi'];
        $b['building_zhuangxiu']=$data['building_zhuangxiu'];
        $b['cenggao']=$data['cenggao'];
        $b['mapx']=$data['mapx'];
        $b['mapy']=$data['mapy'];
        $b['wufei']=$data['wufei'];
        $b['building_tejia']=$data['building_tejia'];
        $b['chewei']=$data['chewei'];
        $b['wuye']=$data['wuye'];
        $b['kaifa']=$data['kaifa'];
        $b['lvhua']=$data['lvhua'];
        $b['rongji']=$data['rongji'];
        $b['jianji']=$data['jianji'];
        $b['hushu']=$data['hushu'];
        $b['preferential']=$data['preferential'];
        $b['traffic']=$data['traffic'];
        $b['introduce']=$data['introduce'];
        $b['humianji']=$data['taomian'];
        if(array_key_exists('danjia',$data)){
            $b['danjia']=$data['danjia'];
        }
        $b['shoulou']=$data['shoulou'];
        $b['jiaotime']=$data['jiaotime'];
        $b['channian']=$data['channian'];
        $b['yushou']=$data['yushou'];
        $b['keys']=$data['keys'];
        $b['zong']=$data['zong'];
        $b['zongjia']=$zong;
        $b['building_tese']=$te;
        $b['building_huxing']=$hu;
        $b['building_ditie']=$di;
        $b['kaitime']=$data['o_kaitime'];
        $b['n_time']=$data['nadi_time'];
        $b['transfer']=$data['transfer'];
        $b['tdeng']=$data['project_extend_dengji'];
        $fenji=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_tell')->where('name','eq',$data['building_name'])->update(['transfer'=>$data['transfer']]);
        $ll=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('id',$gid)->update($b);
        unset($b['tdeng']);
        $ll=Db::connect('mysql://tpshop:zRitAk6cryrkKJCB@127.0.0.1:3306/tpshop#utf8')->table('tpshop_goods')->where('id',$yid)->update($b);
        $re = Building::update($data, ['id' => $id]);
        if (!$re) {
            $res = ['code' => 301, 'msg' => '更新失败'];
            return json($res);
        } else {
            $res = ['code' => 200];
            return json($res);
        }
    }

    // 未通过的修改
    public function updatex(Request $request, $id)
    {
        //
        $hetong = $request->param()['hetong'];
        $hetong = implode(',', $hetong);
        $img = $request->param()['building_img'];
        $data = $request->param();
        $data = $data['value'];
        $data['hetong'] = $hetong;
        $data['building_img'] = $img;
        $rule = [
            'building_name' => 'require',
            'building_address' => 'require',

            'building_xiaoshou' => 'require',
            'building_jiage' => 'require',
            'zongjia' => 'require',
            'building_zhuangxiu' => 'require',
            'building_ditie' => 'require',
            'building_huxing' => 'require',
            'building_tese' => 'require',
            'building_xingshi' => 'require',
            'introduce' => 'require',
            'traffic' => 'require',
            'hushu' => 'require',
            'guiji' => 'require',
            'rongji' => 'require',
            'jianji' => 'require',
            'zong' => 'require',

            'channian' => 'require',
            'jiaotime' => 'require',
            'mapx' => 'require',
            'mapy' => 'require',
            'cenggao' => 'require',
            'humianji' => 'require',
            'wufei' => 'require',
            'chewei' => 'require',
            'shoulou' => 'require',
            'wuye' => 'require',
            'kaifa' => 'require',
            'lvhua' => 'require',
        ];
        $msg = [
            'building_name.require' => '项目名不能为空',
            'building_address.require' => '项目地址不能为空',

            'building_xiaoshou.require' => '销售状态不能为空',
            'building_jiage.require' => '项目的单价等级没选',
            'zongjia.require' => '项目的总价等级没选',
            'building_zhuangxiu.require' => '项目的装修情况没选',
            'building_ditie.require' => '临近地铁没选',
            'building_huxing.require' => '项目有哪些户型没选',
            'building_tese.require' => '项目特色没选',
            'building_xingshi.require' => '项目形式没选',
            'introduce.require' => '项目介绍没写',
            'traffic.require' => '到楼盘的交通没写',
            'hushu.require' => '项目有多少户没写',
            'guiji.require' => '规划面积没写',
            'rongji.require' => '容积率没写',
            'jianji.require' => '建筑面积没写',
            'zong.require' => '最低总价没写',

            'channian.require' => '产权年限没写',
            'jiaotime.require' => '交房时间没写',
            'mapx.require' => '楼盘经度没写',
            'mapy.require' => '楼盘维度没写',
            'cenggao.require' => '层高没写',
            'humianji.require' => '户面积没写',
            'wufei.require' => '物业费没写',
            'chewei.require' => '车位数没写',
            'shoulou.require' => '售楼处地址没写',
            'wuye.require' => '物业公司没写',
            'kaifa.require' => '开发商没写',
            'lvhua.require' => '绿化率没写',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            $error = $validate->getError();
            $res = ['code' => 300, 'msg' => $error];
            return json($error);
        }

        $di=[];
        foreach($data['building_ditie'] as $v){
            $di[]=Ditie::where('id',$v)->column('ditie')[0];
        }
        $di=implode(',',$di);
        $hu=[];
        foreach($data['building_huxing'] as $v){
            $hu[]=Huxing::where('id',$v)->column('huxing')[0];
        }
        $hu=implode(',',$hu);
        $te=[];
        foreach($data['building_tese'] as $v){
            $te[]=Tese::where('id',$v)->column('tese')[0];
        }
        $te=implode(',',$te);
        $zong=$data['zongjia'][0];

        $data['building_ditie'] = implode(',', $data['building_ditie']);
        $data['building_huxing'] = implode(',', $data['building_huxing']);
        $data['building_tese'] = implode(',', $data['building_tese']);
        $cate_id = Area::where('area_name', 'eq', $data['cate_id'][2])->column('id')[0];
        $data['cate_id'] = $cate_id;
        $data['humianji'] = implode(',', $data['humianji']);
        $data['zongjia'] = implode(',', $data['zongjia']);
        $data['hetong_time'] = substr($data['hetong_time'], 0, 10);
        $data['t_kaitime'] = substr($data['t_kaitime'], 0, 10);
        $data['jiaotime'] = substr($data['jiaotime'], 0, 10);
        $data['o_kaitime'] = substr($data['o_kaitime'], 0, 10);
        $data['building_people']=session('user.id');
        $data['shen']=2;
        $data['id']=$id;
        $b=[];
        $gid=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$data['building_name'])->find();
        $yid=Db::connect('mysql://tpshop:zRitAk6cryrkKJCB@127.0.0.1:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$data['building_name'])->find();
        $gid=$gid['id'];
        $yid=$yid['id'];
        $b['building_name']=$data['building_name'];
        $b['building_people']=$data['building_people'];
        $b['cate_id']=$data['cate_id'];
        $b['building_address']=$data['building_address'];
        $b['building_img']='http://'.$data['building_img'];
        $b['building_jiage']=$data['building_jiage'];
        $b['building_xiaoshou']=$data['building_xiaoshou'];
        $b['building_xingshi']=$data['building_xingshi'];
        $b['building_zhuangxiu']=$data['building_zhuangxiu'];
        $b['cenggao']=$data['cenggao'];
        $b['mapx']=$data['mapx'];
        $b['mapy']=$data['mapy'];
        $b['wufei']=$data['wufei'];
        $b['building_tejia']=$data['building_tejia'];
        $b['chewei']=$data['chewei'];
        $b['wuye']=$data['wuye'];
        $b['kaifa']=$data['kaifa'];
        $b['lvhua']=$data['lvhua'];
        $b['rongji']=$data['rongji'];
        $b['jianji']=$data['jianji'];
        $b['hushu']=$data['hushu'];
        $b['preferential']=$data['preferential'];
        $b['traffic']=$data['traffic'];
        $b['introduce']=$data['introduce'];
        $b['humianji']=$data['taomian'];
        $b['danjia']=$data['danjia'];
        $b['shoulou']=$data['shoulou'];
        $b['jiaotime']=$data['jiaotime'];
        $b['channian']=$data['channian'];
        $b['yushou']=$data['yushou'];
        $b['keys']=$data['keys'];
        $b['zong']=$data['zong'];
        $b['zongjia']=$zong;
        $b['building_tese']=$te;
        $b['building_huxing']=$hu;
        $b['building_ditie']=$di;
        $b['kaitime']=$data['o_kaitime'];
        $b['n_time']=$data['nadi_time'];
        $b['transfer']=$data['transfer'];
        $b['tdeng']=$data['project_extend_dengji'];
        $ll=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('id',$gid)->update($b);
        unset($b['tdeng']);
        $ll=Db::connect('mysql://tpshop:zRitAk6cryrkKJCB@127.0.0.1:3306/tpshop#utf8')->table('tpshop_goods')->where('id',$yid)->update($b);
        $re = Building::update($data);
        if (!$re) {
            $res = ['code' => 301, 'msg' => '更新失败'];
            return json($res);
        } else {
            $res = ['code' => 200];
            return json($res);
        }
    }


    public function updatetext($id)
    {
        $bid = request()->param()['bid'];
        $data = request()->param();
        Huimgs::update($data, ['id' => $bid]);
        $res = ['code' => 200];
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
        $g = Building::where('id', $id)->column('building_img')[0];
        $s = Building::where('id', $id)->column('hetong')[0];
        $s = explode(',', $s);
        $g=explode('/',$g);
        unset($g[0]);
        $g=implode('/',$g);
        unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $g);
        foreach ($s as $v) {
            $l=explode('/',$v);
            unset($l[0]);
            $v=implode('/',$l);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $v);
        }
        $name=Building::where('id',$id)->column('building_name')[0];
        $gid=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$name)->find();
        $gid=$gid['id'];
        Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('id',$gid)->delete();
        Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_tuan')->where('bid',$gid)->delete();
        Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_text')->where('bid',$gid)->delete();
        Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_project')->where('bid',$gid)->delete();
        Building::destroy($id);
        Tuan::destroy(['bid' => $id]);
        ProjectModel::destroy(['bid' => $id]);
        Guide::destroy(['bid'=>$id]);
        $res = [
            'code' => 200
        ];
        return json($res);
    }
    public function delpics()
    {
        $data = request()->put();
        $id = $data['id'];
        if (!preg_match('/^\d+$/', $id)) {

            $res = [
                'code' => 10000,
                'msg' => '参数错误'
            ];
            return json($res);
        }
        if ($data['type'] == 'x') {
            $x = Xiaoimgs::where('id', $id)->find();
            $s=explode('/',$x['x_small']);
            $b=explode('/',$x['x_big']);
            unset($s[0]);
            unset($b[0]);
            $s=implode('/',$s);
            $b=implode('/',$b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $s);
            $big='http://'.$x['x_big'];
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_xiaoimgs')->where('x_big','eq',$big)->delete();
            Xiaoimgs::destroy($id);
        } else if ($data['type'] == 'y') {
            $x = Yangimgs::where('id', $id)->find();
            $s=explode('/',$x['y_small']);
            $b=explode('/',$x['y_big']);
            unset($s[0]);
            unset($b[0]);
            $s=implode('/',$s);
            $b=implode('/',$b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $s);
            $big='http://'.$x['y_big'];
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_yangimgs')->where('y_big','eq',$big)->delete();
            Yangimgs::destroy($id);
        } else if ($data['type'] == 'p') {
            $x = Peiimgs::where('id', $id)->find();
            $s=explode('/',$x['p_small']);
            $b=explode('/',$x['p_big']);
            unset($s[0]);
            unset($b[0]);
            $s=implode('/',$s);
            $b=implode('/',$b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $s);
            $big='http://'.$x['p_big'];
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_peiimgs')->where('p_big','eq',$big)->delete();
            Peiimgs::destroy($id);
        } else if ($data['type'] == 's') {
            $x = Shiimgs::where('id', $id)->find();
            $s=explode('/',$x['s_small']);
            $b=explode('/',$x['s_big']);
            unset($s[0]);
            unset($b[0]);
            $s=implode('/',$s);
            $b=implode('/',$b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $s);
            $big='http://'.$x['s_big'];
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_shiimgs')->where('s_big','eq',$big)->delete();
            Shiimgs::destroy($id);
        } else if ($data['type'] == 'j') {
            $x = Jiaoimgs::where('id', $id)->find();
            $s=explode('/',$x['j_small']);
            $b=explode('/',$x['j_big']);
            unset($s[0]);
            unset($b[0]);
            $s=implode('/',$s);
            $b=implode('/',$b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $s);
            $big='http://'.$x['j_big'];
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_jiaoimgs')->where('j_big','eq',$big)->delete();
            Jiaoimgs::destroy($id);
        } else if ($data['type'] == 'h') {
            $x = Huimgs::where('id', $id)->find();
            $s=explode('/',$x['h_small']);
            $b=explode('/',$x['h_big']);
            unset($s[0]);
            unset($b[0]);
            $s=implode('/',$s);
            $b=implode('/',$b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $b);
            unlink(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $s);
            $big='http://'.$x['h_big'];
            Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_huimgs')->where('h_big','eq',$big)->delete();
            Huimgs::destroy($id);
        }
        $res = [
            'code' => 200,
            'msg' => 'success'
        ];
        return json($res);
    }

    public function img()
    {
        $data = request()->param()['data'];
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < 8; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        $lujing = './uploads/' . date('Ymd');
        if (!is_dir($lujing)) {
            mkdir(iconv("UTF-8", "GBK", $lujing), 0777, true);
        }
        $type = explode(',', $data)[0];
        $type = explode(';', $type)[0];
        $type = explode(':', $type)[1];
        $type = explode('/', $type)[1];
        $newFilePath = '/uploads/' . date('Ymd') . '/' . $str . '.' . $type;

        $dd = explode(',', $data)[1]; //得到post过来的二进制原始数据
        if (empty($dd)) {
            $data = file_get_contents("php://input");
        }
        $r = file_put_contents('.' . $newFilePath, base64_decode($dd));
        clearstatcache();
        $temp = explode('/', $newFilePath);
        $pics_big =  '/uploads/' . $temp[2] . '/thumb_800_' . $temp[3];
        $pics_small = '/uploads/' .  $temp[2] .  '/thumb_400_' . $temp[3];
        $image = Image::open('.' . $newFilePath);
        $image->water('./logo.png',\think\Image::WATER_SOUTHEAST,100)->save('.' . $newFilePath);
        $image = Image::open('.' . $newFilePath);
        $image->thumb(1200, 1200)->save('.' . $pics_big);
        $image->thumb(400, 400)->save('.' . $pics_small);
        $row = [
            'code' => 200,
            'big' => 'api.jy1980.com'.$pics_big,
            'small' => 'api.jy1980.com'.$pics_small
        ];
        return json($row);
    }

    public function upload_y($bid)
    {
        $flies = request()->param()['ybimglist'];
        $pics_data = [];
        $l=Building::where('id',$bid)->column('building_name')[0];
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$l)->find();
        $id=$id['id'];
        $pic=[];
        foreach ($flies as $v) {
            $big=explode('-',$v)[0];
            $sma=explode('-',$v)[1];
            $row = [
                'bid' => $bid,
                'y_big' => $big,
                'y_small' => $sma
            ];
            $rows = [
                'bid' => $id,
                'y_big' => 'http://'.$big,
                'y_small' => 'http://'.$sma
            ];
            $pic[]=$rows;
            $pics_data[] = $row;
        }
        $goodspic = new Yangimgs();
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_yangimgs')->insertAll($pic);
        $goodspic->saveAll($pics_data);
    }
    public function upload_p($bid)
    {
        $flies = request()->param()['ptimglist'];
        $l=Building::where('id',$bid)->column('building_name')[0];
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$l)->find();
        $id=$id['id'];
        $pic=[];
        $pics_data = [];
        foreach ($flies as $v) {
            $big=explode('-',$v)[0];
            $sma=explode('-',$v)[1];
            $row = [
                'bid' => $bid,
                'p_big' => $big,
                'p_small' => $sma
            ];
            $rows = [
                'bid' => $id,
                'p_big' => 'http://'.$big,
                'p_small' => 'http://'.$sma
            ];
            $pics_data[] = $row;
            $pic[]=$rows;
        }
        $goodspic = new Peiimgs();
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_peiimgs')->insertAll($pic);
        $goodspic->saveAll($pics_data);
    }
    public function upload_x($bid)
    {
        $flies = request()->param()['xgimglist'];
        $l=Building::where('id',$bid)->column('building_name')[0];
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$l)->find();
        $id=$id['id'];
        $pic=[];
        $pics_data = [];
        foreach ($flies as $v) {
            $big=explode('-',$v)[0];
            $sma=explode('-',$v)[1];
            $row = [
                'bid' => $bid,
                'x_big' => $big,
                'x_small' => $sma
            ];
            $rows = [
                'bid' => $id,
                'x_big' => 'http://'.$big,
                'x_small' => 'http://'.$sma
            ];
            $pics_data[] = $row;
            $pic[]=$rows;
        }
        $goodspic = new Xiaoimgs();
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_xiaoimgs')->insertAll($pic);
        $goodspic->saveAll($pics_data);
    }
    public function upload_h($bid)
    {
        $flies = request()->param()['hximglist'];
        $data = request()->param();
        $data['ting']=array_values(array_filter($data['ting']));
        $data['area']=array_values(array_filter($data['area']));
        $data['price']=array_values(array_filter($data['price']));
        $data['te']=array_values(array_filter($data['te']));
        $data['lei']=array_values(array_filter($data['lei']));
        $data['fen']=array_values(array_filter($data['fen']));
        $pics_data = [];
        $l=Building::where('id',$bid)->column('building_name')[0];
	
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$l)->find();
	
        $id=$id['id'];
        $pic=[];
        if (is_array($flies)) {
            foreach ($flies as $k => $v) {
                $big=explode('-',$v)[0];
                $sma=explode('-',$v)[1];
                $row = [
                    'bid' => $bid,
                    'h_big' => $big,
                    'h_small' => $sma,
                    'content' => $data['ting'][$k],
                    'mian' => $data['area'][$k],
                    'jia' => $data['price'][$k],
                    'te' => $data['te'][$k],
                    'lei' => $data['lei'][$k],
                    'fen' => $data['fen'][$k]
                ];
                $rows = [
                    'bid' => $id,
                    'h_big' => 'http://'.$big,
                    'h_small' => 'http://'.$sma,
                    'content' => $data['ting'][$k],
                    'mian' => $data['area'][$k],
                    'jia' => $data['price'][$k],
                    'te' => $data['te'][$k],
                    'lei' => $data['lei'][$k],
                    'fen' => $data['fen'][$k]
                ];
                $pic[]=$rows;
                $pics_data[] = $row;
            }
        }
	
        
        $goodspic = new Huimgs();
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_huimgs')->insertAll($pic);
        $goodspic->saveAll($pics_data);
    }
    public function upload_s($bid)
    {
        $flies = request()->param()['sjimglist'];
        $l=Building::where('id',$bid)->column('building_name')[0];
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$l)->find();
        $id=$id['id'];
        $pic=[];
        $pics_data = [];
        foreach ($flies as $v) {
            $big=explode('-',$v)[0];
            $sma=explode('-',$v)[1];
            $row = [
                'bid' => $bid,
                's_big' => $big,
                's_small' => $sma
            ];
            $rows = [
                'bid' => $id,
                's_big' => 'http://'.$big,
                's_small' => 'http://'.$sma
            ];
            $pics_data[] = $row;
            $pic[]=$rows;
        }
        $goodspic = new Shiimgs();
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_shiimgs')->insertAll($pic);
        $goodspic->saveAll($pics_data);
    }
    public function upload_j($bid)
    {
        $flies = request()->param()['jtimglist'];
        $l=Building::where('id',$bid)->column('building_name')[0];
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$l)->find();
        $id=$id['id'];
        $pic=[];
        $pics_data = [];
        foreach ($flies as $v) {
            $big=explode('-',$v)[0];
            $sma=explode('-',$v)[1];
            $row = [
                'bid' => $bid,
                'j_big' => $big,
                'j_small' => $sma
            ];
            $rows = [
                'bid' => $id,
                'j_big' => 'http://'.$big,
                'j_small' => 'http://'.$sma
            ];
            $pic[]=$rows;
            $pics_data[] = $row;
        }
        $goodspic = new Jiaoimgs();
        $id=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_jiaoimgs')->insertAll($pic);
        $goodspic->saveAll($pics_data);
    }

    function sc_send(  $text , $desp = '' , $key = 'SCU62287T4b511de39ab8c1e3109087eb363ba7025d89b51e63104'  )
    {
        $postdata = http_build_query(
        array(
            'text' => $text,
            'desp' => $desp
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context  = stream_context_create($opts);
    return $result = file_get_contents('https://sc.ftqq.com/'.$key.'.send', false, $context);

    }
    // 改变上下架
    public function isdeng(){
        $data=request()->param();
        $type=$data['type'];
        $id=$data['id'];
        $time=Building::where('id',$id)->column('d_time')[0];
        $d=date('z',time());
        
        if($time){
            if(true){
                if($type==2){
                    $name=Building::where('id','eq',$id)->column('building_name')[0];
                    $this->sc_send($name.'下架','123');
                    Building::update(['id'=>$id,'isdeng'=>'否','d_time'=>$d]);
                }else{
                    $name=Building::where('id','eq',$id)->column('building_name')[0];
                    $this->sc_send($name.'上架','123');
                    Building::update(['id'=>$id,'isdeng'=>'是','d_time'=>$d]);
                }
                return json(['code'=>200]);
            }else{
                return json(['code'=>300]);
            }
        }else{
            if($type==1){
                $name=Building::where('id','eq',$id)->column('building_name')[0];
                    $this->sc_send($name.'下架','123');
                Building::update(['id'=>$id,'isdeng'=>'否','d_time'=>$d]);
            }else{
                $name=Building::where('id','eq',$id)->column('building_name')[0];
                    $this->sc_send($name.'上架','123');
                Building::update(['id'=>$id,'isdeng'=>'是','d_time'=>$d]);
            }
            return json(['code'=>200]);
        }
    }

    

    public function tuitong()
    {
        $tiao=request()->param()['value'];
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Building::where('branch','eq',$tiao['city'])->column('id');
            }
        }
        
        $type = request()->param()['type'];
        $where = [
            ['id', 'in', $ids],
            ['isdeng', 'eq', '是']
        ];
        if ($type == 1) {
            $t = strtotime('-12days');
            $data = Building::where($where)->whereTime('update_time', [date('Y-m-d', $t), date('Y-m-d', time())])->field("DATE_FORMAT(FROM_UNIXTIME(update_time),'%m-%d') as year,count(*) as total")
                ->group("DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')")->select();
        } else if ($type == 2) {
            $s = strtotime('-12week');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        } else if ($type == 3) {
            $s = strtotime('-12month');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        } else if ($type == 4) {
            $s = strtotime('-12quarter');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        } else if ($type == 5) {
            $s = strtotime('-12year');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        }
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    public function xiatong()
    {
        
        $tiao=request()->param()['value'];
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $ids=Building::where('branch','eq',$tiao['city'])->column('id');
            }
        }
        
        $type = request()->param()['type'];
        $where = [
            ['id', 'in', $ids],
            ['isdeng', 'eq', '否']
        ];
        
        if ($type == 1) {
            $t = strtotime('-12days');
            $data = Building::where($where)->whereTime('update_time', [date('Y-m-d', $t), date('Y-m-d', time())])->field("DATE_FORMAT(FROM_UNIXTIME(update_time),'%m-%d') as year,count(*) as total")
                ->group("DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')")->select();
        } else if ($type == 2) {
            $s = strtotime('-12week');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        } else if ($type == 3) {
            $s = strtotime('-12month');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        } else if ($type == 4) {
            $s = strtotime('-12quarter');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        } else if ($type == 5) {
            $s = strtotime('-12year');
            $data = Db::name('building')->where($where)->whereTime('update_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d')) as year,count(*) as total")
                ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(update_time),'%Y-%m-%d'))")->select();
        }
        $res = [
            'code' => 200,
            'data' => $data
        ];
        return json($res);
    }
    public function tong()
    { 
        $tiao=request()->param()['value'];
        $type=request()->param()['type'];
        $where=[];
        if (array_key_exists('city',$tiao)) {
            if($tiao['city']){
                $where[]=['branch','eq',$tiao['city']];
            }
        }
        if ($type == 1) {
            $t = strtotime('-12days');
            $data = Db::name('building')->where($where)->whereTime('create_time', [date('Y-m-d', $t), date('Y-m-d', time())])->field("DATE_FORMAT(FROM_UNIXTIME(create_time),'%d') as year,count(*) as sales")
                ->group("DATE_FORMAT(FROM_UNIXTIME(create_time),'%d')")->select();
        } else if ($type == 2) {
            $s = strtotime('-12week');
            $data = Db::name('building')->where($where)->whereTime('create_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as sales")
                ->group("WEEK(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        } else if ($type == 3) {
            $s = strtotime('-12month');
            $data = Db::name('building')->where($where)->whereTime('create_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as sales")
                ->group("MONTH(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        } else if ($type == 4) {
            $s = strtotime('-12quarter');
            $data = Db::name('building')->where($where)->whereTime('create_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as sales")
                ->group("QUARTER(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        } else if ($type == 5) {
            $s = strtotime('-12year');
            $data = Db::name('building')->where($where)->whereTime('create_time', [date('Y-m-d', $s), date('Y-m-d', time())])->field("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d')) as year,count(*) as sales")
                ->group("YEAR(DATE_FORMAT(FROM_UNIXTIME(create_time),'%Y-%m-%d'))")->select();
        }
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }
}
