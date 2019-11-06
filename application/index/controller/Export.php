<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\api\model\Building;
use app\api\model\Staff;

class Export extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //设置sheet的名字  两种方法
        $sheet->setTitle('phpspreadsheet——demo');
        $spreadsheet->getActiveSheet()->setTitle('Hello');
        //设置第一行小标题
        $k = 1;
        $sheet->setCellValue('b'.$k, '进客量');
        $sheet->setCellValue('c'.$k, '带看量');
        $sheet->setCellValue('f'.$k, '跟进量');
        $sheet->setCellValue('i'.$k, '客户跟进周期');
        $sheet->setCellValue('m'.$k, '成交量');
        $sheet->setCellValue('o'.$k, '客户利用率');
        $sheet->setCellValue('c2','总带看量');
        $sheet->setCellValue('d2','新客');
        $sheet->setCellValue('e2','老客');
        $sheet->setCellValue('f2','跟进量');
        $sheet->setCellValue('g2','私客');
        $sheet->setCellValue('h2','公客');
        $sheet->setCellValue('i2','1-3');
        $sheet->setCellValue('j2','4-6');
        $sheet->setCellValue('k2','7-10');
        $sheet->setCellValue('l2','11+');
        $sheet->setCellValue('m2','套数');
        $sheet->setCellValue('n2','金额');
        $sheet->setCellValue('o2','总进客');
        $sheet->setCellValue('p2','新客');
        $sheet->setCellValue('q2','总利用率');
        
        
        //将A3到D4合并成一个单元格
        $spreadsheet->getActiveSheet()->mergeCells('c1:e1');
        $spreadsheet->getActiveSheet()->mergeCells('f1:h1');
        $spreadsheet->getActiveSheet()->mergeCells('i1:l1');
        $spreadsheet->getActiveSheet()->mergeCells('m1:n1');
        $spreadsheet->getActiveSheet()->mergeCells('o1:q1');
        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('a1:t1')->applyFromArray($styleArray);
        $sheet->getStyle('a2:t2')->applyFromArray($styleArray);
        
        //循环赋值
        
        $file_name = date('Y-m-d', time()).rand(1000, 9999);
        //第一种保存方式
        $writer = new Xlsx($spreadsheet);
        // 保存的路径可自行设置
        $file_name = '../'.$file_name . ".xlsx";
        $writer->save($file_name);
        //第二种直接页面上显示下载
        $file_name = $file_name . ".xlsx";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$file_name.'"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        //注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
        $writer->save('php://output');
        

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        // $str=file_get_contents('./1.jpg');
        // $ss=base64_encode($str);
        // $ss='';
        // $ss=explode(',',$ss);
        // // $data=base64_decode($ss);
        // // file_put_contents('./ss.jpg',$data);
        // dump($ss);
        // $l=null+5;
        // dump($l);
        // $file_name = "1";
        // $uploadwork    = "C:\Users\Administrator\Desktop\城市\二线城市（30个）27575条\\";
        // $uploadfile    = $uploadwork.$file_name.'.xlsx';
        // $reader        = \PHPExcel_IOFactory::createReader('excel2007'); //设置以Excel5格式(Excel97-2003工作簿)
        // $PHPExcel      = $reader->load($uploadfile); // 载入excel文件
        // $sheet         = $PHPExcel->getSheet(0); // 读取第一個工作表
        // $highestRow    = $sheet->getHighestRow(); // 取得总行数
        // $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        // $data          = [];
        // for ($row = 2; $row <= $highestRow; $row++) //行号从1开始
        // {
        //     for ($column = 'A'; $column <= $highestColumm; $column++) //列数是以A列开始
        //     {
        //         if (empty($sheet->getCell($column . $row)->getValue()) == false) {
        //             if (empty($data[$row]) == false) {
        //                 $str = $sheet->getCell($column . $row)->getValue();
                        
        //             } else {
        //                 $data[$row]['name'] = $sheet->getCell($column . $row)->getValue();
        //             }
        //         }
        //     }
        // }
        // dump($data);
        //    $data=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@127.0.0.1:3306/tpshop#utf8') ->table('tpshop_goods')->where('id','between','408,429')->select();
        //    $dd=[];
        //    foreach($data as $v){
        //        unset($v['n_time']);
        //        $dd[]=$v;
        //    }
        // //    Building::create($data,true);
        // Db::name('building')->insertAll($dd,true);
        // dump('success');
        //    $data=Building::select();
           

        // $inputFileType = IOFactory::identify('./1.xls'); //传入Excel路径
        // $excelReader   = IOFactory::createReader($inputFileType); //Xlsx
        // $PHPExcel      = $excelReader->load('./1.xls'); // 载入excel文件
        // $sheet         = $PHPExcel->getSheet(6); // 读取第一個工作表
        // $sheetdata = $sheet->toArray();
        // $data=[];
        // foreach($sheetdata as $v){
        //     $l=[];
        //     $l['transfer']=$v[2];
        //     $l['name']=$v[1];
        //     $data[]=$l;
        // }
        // unset($data[0]);
        // unset($data[1]);  project_extend_dengji
        // $s=Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq','中粮大悦城')->find();
        // unset($s['id']);
        // unset($s['n_time']);
        // $l=Db::connect('mysql://erp:ZkMFXYZ2H7MBtW4i@127.0.0.1:3306/erp#utf8')->table('erp_building')->field('building_name,project_extend_dengji')->select();
        
        // foreach($l as $v){
        //     Db::connect('mysql://root:BmaGRa6mBNdbKTNw@47.92.241.83:3306/tpshop#utf8')->table('tpshop_goods')->where('building_name','eq',$v['building_name'])->update(['tdeng'=>$v['project_extend_dengji']]);
        // }
        // dump('321');die();
        // $n=array_diff($s,$l);

        // dump($n);
        // Db::name('staff')->insertAll($data,true);
        $data=Db::connect('mysql://erp:ZkMFXYZ2H7MBtW4i@127.0.0.1:3306/erp#utf8')->table('erp_user')->where('s_id','eq',95)->whereTime('update_time','<','2019-10-1')->update(['s_id'=>0]);
        dump($data);die();
        // Db::connect('mysql://erp:ZkMFXYZ2H7MBtW4i@127.0.0.1:3306/erp#utf8')->table('erp_user')->where('sid','eq',131)->update(['sid'=>125,'s_id'=>125]);

        // foreach($sheetdata as $k=>$v){
        //     if($k!=0 ){
        //         $l=[];
        //        $l['name']=$v[0];
        //        $l['port']=$v[3];
        //        $l['grade']=$v[4];
        //        if($v[7]==''){
        //         $l['s_id']=0;
        //        }else{
        //            $kl=Staff::where('name','eq',$v[3])->column('id');
        //            if($kl){
        //                 $l['s_id']=$kl[0];
        //            }
                   
        //        }
               
        //        $l['time']=date('Y-m-d',strtotime($v[2]));
        //        $l['tel']=$v[9];
        //        $data[]=$l;
        //     }
        // }
        // dump($data);die();
        // $data=array_unique($data);
        // // $data=Staff::select();
        // Db::name('user')->insertAll($data,true);
        // dump($data); // --- 直接返回数组数据
        




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
    }
}
