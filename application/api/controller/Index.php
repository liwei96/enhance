<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\facade\Cache;
use app\api\model\Staff;
use app\api\model\User;
use app\api\model\Dai;
use app\api\model\CheckDong;
use app\api\model\Guide;
use app\api\model\Gen;
use app\api\model\Record;
use app\api\model\Qu;
use app\api\model\Building;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class Index
{
    public function list(){
        $list=Staff::where('job','eq','28')->field("id,name,department")->select();
        foreach($list as $v){
            $v['department']=Qu::where('id','eq',$v['department'])->column('name');
            if($v['department']){
                $v['department']=$v['department'][0];
            }else{
                unset($v['department']);
            }
        }
        $res=[
            'code'=>200,
            'list'=>$list
        ];
        return json($res);
    }
    
    public function out()
    {
        $id=session('user.id');
        $ids=Staff::where('pid',$id)->select();
        $type=request()->param()['type'];
        $ids=$this->ids($ids);
       
        $shu=[];
        foreach($ids as $v){
            $lin=[];
            if($type==1){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','today')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','today')->field("sum(yeji) as num")->select();
                
            }else if($type==2){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','week')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','week')->field("sum(yeji) as num")->select();
            }else if($type==3){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','month')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','month')->field("sum(yeji) as num")->select();
            }else if($type==4){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','-3 month')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("sum(yeji) as num")->select();
            }else if($type==5){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','year')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','year')->field("sum(yeji) as num")->select();
            }
            $zongdan=$zongdan[0]['total'];
            $gen=$gen[0]['total'];
            $gong=$gong[0]['total'];
            $si=$si[0]['total'];
            $xin=$xin[0]['total'];
            $jinke=$jinke[0]['total'];
            $old=$old[0]['total'];
            $jin=$jin[0]['num'];
            if($jinke==0){
                $xinli=0;
                $oldli=0;
                $zongli=0;
            }else{
                $xinli=round($xin/$jinke,2)*100;
                $oldli=round($old/$jinke,2)*100;
                $zongli=round($zongdan/$jinke,2)*100;
            }
            $lin[]=Staff::where('id',$v['id'])->column('name')[0];
            $lin[]=$jinke;
                $lin[]=$zongdan;
                $lin[]=$xin;
                $lin[]=$old;
                $lin[]=$gen;
                $lin[]=$si;
                $lin[]=$gong;
                $lin[]=$s1;
                $lin[]=$s2;
                $lin[]=$s3;
                $lin[]=$s4;
                $lin[]=$tao;
                $lin[]=$jin;
                $lin[]=$zongli;
                $lin[]=$xinli;
                $lin[]=$oldli;
            $shu[]=$lin;
        }
        
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
        $row=3;
        foreach($shu as $k=>$v){
            $column=1;
            foreach($v as $l=>$n){
                $sheet->setCellValueByColumnAndRow($column,$row,$n);
                $column++;
            }
            $row++;
        }
        
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



    public function index(){
        $id=session('user.id');
        $ids=Staff::where('pid',$id)->select();
        $type=request()->param()['type'];
        $ids=$this->gets($ids);
        // 测试
        $ids[]=session('user.id');
        $ids[]=$id;
        $shu=[];
        foreach($ids as $v){
            $lin=[];
            if($type==1){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $jinkeid=User::where('s_id','eq',$v)->whereTime('create_time','today')->field("id")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','today')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','today')->field("sum(yeji) as num")->select();
                
            }else if($type==2){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','week')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','week')->field("sum(yeji) as num")->select();
            }else if($type==3){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','month')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','month')->field("sum(yeji) as num")->select();
            }else if($type==4){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','-3 month')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("sum(yeji) as num")->select();
            }else if($type==5){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','year')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','year')->field("sum(yeji) as num")->select();
            }else if($type==6){
                $time=request()->param()['time'];
                $start=date('Y-m-d',strtotime($time[0]));
                $end=date('Y-m-d',strtotime($time[1]));
                $jinke=User::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','between',[$start,$end])->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','between',[$start,$end])->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','between',[$start,$end])->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen where DATE_FORMAT(time,'%Y-%m-%d')>=$start and DATE_FORMAT(time,'%Y-%m-%d')<=$end order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','between',[$start,$end])->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','between',[$start,$end])->field("sum(yeji) as num")->select();
            }
            $zongdan=$zongdan[0]['total'];
            $gen=$gen[0]['total'];
            $gong=$gong[0]['total'];
            $si=$si[0]['total'];
            $xin=$xin[0]['total'];
            $jinke=$jinke[0]['total'];
            $old=$old[0]['total'];
            $jin=$jin[0]['num'];
            if($jinke==0){
                $xinli=0;
                $oldli=0;
                $zongli=0;
            }else{
                $xinli=round($xin/$jinke,2)*100;
                $xinli=round($xinli,2).'%';
                $oldli=round($old/$jinke,2)*100;
                $oldli=round($oldli,2).'%';
                $zongli=round($zongdan/$jinke,2)*100;
                $zongli=round($zongli,2).'%';
            }
            $lin[]=Staff::where('id',$v)->column('name')[0];
            $lin[]=$jinke;
                $lin[]=$zongdan;
                $lin[]=$xin;
                $lin[]=$old;
                $lin[]=$gen;
                $lin[]=$si;
                $lin[]=$gong;
                $lin[]=$s1;
                $lin[]=$s2;
                $lin[]=$s3;
                $lin[]=$s4;
                $lin[]=$tao;
                $lin[]=$jin;
                $lin[]=$zongli;
                $lin[]=$xinli;
                $lin[]=$oldli;
            $shu[]=$lin;
        }
        return json(['code'=>200,'data'=>$shu]);
    }
    
    public function sou($id){
        $ids=Staff::where('pid','eq',$id)->select();
        $type=request()->param()['type'];
        $ids=$this->gets($ids);
        // 测试
        $ids[]=session('user.id');
        $ids[]=$id;
        $shu=[];
        $jins=0;
        $zongdais=0;
        $xins=0;
        $olds=0;
        $gens=0;
        $sis=0;
        $gongs=0;
        $s1s=0;
        $s2s=0;
        $s3s=0;
        $s4s=0;
        $taos=0;
        $mons=0;
        $zonglis=0;
        $xinlis=0;
        $laolis=0;
        foreach($ids as $v){
            $lin=[];
            if($type==1){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','today')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','today')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','today')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','today')->field("sum(yeji) as num")->select();
                
            }else if($type==2){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','week')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','week')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','week')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','week')->field("sum(yeji) as num")->select();
            }else if($type==3){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','month')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','month')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','month')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','month')->field("sum(yeji) as num")->select();
            }else if($type==4){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','-3 month')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','-3 month')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','-3 month')->field("sum(yeji) as num")->select();
            }else if($type==5){
                $jinke=User::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $jinke2=User::where('sid','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $jinke=$jinke[0]['total']+$jinke2[0]['total'];
                $zongdan=Dai::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $gen=Gen::where('s_id','eq',$v)->whereTime('create_time','year')->field("count(*) as total")->select();
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('create_time','year')->field("count(*) as total")->select();
                $data=Db::query("select * from (select * from erp_gen order by update_time desc) as data where s_id=$v group by u_id order by update_time desc");
                $time=time();
                $s1=0;
                $s2=0;
                $s3=0;
                $s4=0;
                foreach($data as $j){
                    if($time-$j['create_time']<(3600*24*3)){
                        $s1=$s1+1;
                    }else if($time-$j['create_time']<(3600*24*6) && $time-$j['create_time']>(3600*24*4)){
                        $s2=$s2+1;
                    }else if($time-$j['create_time']<(3600*24*11) && $time-$j['create_time']>(3600*24*7)){
                        $s3=$s3+1;
                    }else if($time-$j['create_time']>(3600*24*11)){
                        $s4=$s4+1;
                    }
                }
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','year')->count("*");
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','year')->field("sum(yeji) as num")->select();
            }else if($type==6){
                $time=request()->param()['time'];
                $start=date('Y-m-d',strtotime($time[0]));
                $start=$start.' 00:00:00';
                $s=strtotime($time[0]);
                $end=date('Y-m-d',strtotime($time[1]));
                $end=$end.' 23:59:59';
                $e=strtotime($time[1]);
                $jinke=User::where('sid','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $jinkeid=User::where('sid','eq',$v)->whereTime('time','between',[$start,$end])->column('id');
                
                // $jinke2=User::where('sid','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                // $jinke2id=User::where('sid','eq',$v)->whereTime('time','between',[$start,$end])->column('id');
                $jinke=$jinke[0]['total'];
                // $jinkeid=array_merge($jinkeid,$jinke2id);
                // $zongdan=Dai::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $zongdanid=Dai::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->column('id');
                $ids=Dai::where('id','in',$zongdanid)->order('time','desc')->column('u_id');
                
                $zongdan=array_unique($ids);
                $zongdan=count($zongdan);
                // $zongdan=Dai::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->field("DATE_FORMAT(FROM_UNIXTIME(time),'%d') as date,count(*) as s")
                // ->select();
                // $zongdan=$zongdan[0]['s'];
                // if(!$zongdan){
                //     $zongdan=0;
                // }
                // dump($zongdan);die();
                // $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $xinid=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('time','between',[$start,$end])->column('id');
                $ids=Dai::where('id','in',$xinid)->order('time','desc')->column('u_id');
                $xin=array_unique($ids);
                $xin=count($xin);
                // $xin=Dai::where([['s_id','eq',$v],['label','eq','新客']])->whereTime('time','between',[$start,$end])->field("DATE_FORMAT(FROM_UNIXTIME(time),'%d') as date,count(*) as s")->select();
                // $xin=$xin[0]['s'];
                // if(!$xin){
                //     $xin=0;
                // }
                // $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $oldid=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('time','between',[$start,$end])->column('id');
                $ids=Dai::where('id','in',$oldid)->order('time','desc')->column('u_id');
                $old=array_unique($ids);
                // $old=Dai::where([['s_id','eq',$v],['label','eq','老客']])->whereTime('time','between',[$start,$end])->field("DATE_FORMAT(FROM_UNIXTIME(time),'%d') as date,count(*) as s")->select();
                // $old=$old[0]['s'];
                // if(!$old){
                //     $old=0;
                // }
                $old=count($old);
                $gen=Gen::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $genid=Gen::where('s_id','eq',$v)->whereTime('time','between',[$start,$end])->column('id');
                $gong=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $gongid=Gen::where([['s_id','eq',$v],['label','eq','公客']])->whereTime('time','between',[$start,$end])->column('id');
                $si=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $siid=Gen::where([['s_id','eq',$v],['label','eq','私客']])->whereTime('time','between',[$start,$end])->column('id');
                // $data=Db::query("select * from (select * from erp_gen order by id desc) as data where s_id=$v and label='私客' group by u_id order by id desc");
                // $dete=Db::query("select u_id from (select * from erp_gen order by id desc) as data where s_id=$v and label='私客' group by u_id order by id desc");
                // $sdf=[];
                // foreach($dete as $v){
                //     $sdf[]=$v['u_id'];
                // }
                // $sdf=array_unique($sdf);

                // dump($sdf);die();
                // $time=time();
                // $s1=0;
                // $s1id=[];
                // $s2=0;
                // $s2id=[];
                // $s3=0;
                // $s3id=[];
                // $s4=0;
                // $s4id=[];
                // foreach($data as $j){
                //     if($time-$j['time']<(3600*24*3)){
                //         $s1=$s1+1;
                //         $s1id[]=$j['id'];
                //     }else if($time-$j['time']<(3600*24*6) && $time-$j['time']>(3600*24*4)){
                //         $s2=$s2+1;
                //         $s2id[]=$j['id'];
                //     }else if($time-$j['time']<(3600*24*11) && $time-$j['time']>(3600*24*7)){
                //         $s3=$s3+1;
                //         $s3id[]=$j['id'];
                //     }else if($time-$j['time']>(3600*24*11)){
                //         $s4=$s4+1;
                //         $s4id[]=$j['id'];
                //     }
                // }

                $s1=User::where([['sid','eq',$v],['grade','eq','A类']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $s1id=User::where([['sid','eq',$v],['grade','eq','A类']])->whereTime('time','between',[$start,$end])->column('id');
                $s1=$s1[0]['total'];
                $s2=User::where([['sid','eq',$v],['grade','eq','B类']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $s2id=User::where([['sid','eq',$v],['grade','eq','B类']])->whereTime('time','between',[$start,$end])->column('id');
                $s2=$s2[0]['total'];
                $s3=User::where([['sid','eq',$v],['grade','eq','C类']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $s3id=User::where([['sid','eq',$v],['grade','eq','C类']])->whereTime('time','between',[$start,$end])->column('id');
                $s3=$s3[0]['total'];
                $s4=User::where([['sid','eq',$v],['grade','eq','D类']])->whereTime('time','between',[$start,$end])->field("count(*) as total")->select();
                $s4id=User::where([['sid','eq',$v],['grade','eq','D类']])->whereTime('time','between',[$start,$end])->column('id');
                $s4=$s4[0]['total'];
                $tao=Record::where('s_id','eq',$v)->whereTime('create_time','between',[$start,$end])->count("*");
                $taoid=Record::where('s_id','eq',$v)->whereTime('create_time','between',[$start,$end])->column('id');
                $jin=Record::where('s_id','eq',$v)->whereTime('create_time','between',[$start,$end])->field("sum(yeji) as num")->select();
            }
            // $zongdan=$zongdan[0]['total'];
            $gen=$gen[0]['total'];
            $gong=$gong[0]['total'];
            $si=$si[0]['total'];
            // $xin=$xin[0]['total'];
            // $jinke=$jinke[0]['total'];
            // $old=$old[0]['total'];
            $jin=$jin[0]['num'];
            if(!$jin){
                $jin=0;
            }
            if($jinke==0){
                $xinli=0;
                $oldli=0;
                $zongli=0;
            }else{
                $xinli=round($xin/$jinke,2)*100;
                $xinli=round($xinli,2);
                $oldli=round($old/$jinke,2)*100;
                $oldli=round($oldli,2);
                $zongli=round($zongdan/$jinke,2)*100;
                $zongli=round($zongli,2);
            }
            $lin[]=Staff::where('id',$v)->column('name')[0];
            $lin[]=$jinke;
            
            // $jins=$jins+$jinke;
                // $zongdais=$zongdan+$zongdais;
                // $xins=$xin+$xins;
                // $olds=$olds+$old;
                // $gens=$gen+$gens;
                // $sis=$sis+$si;
                // $gongs=$gong+$gongs;
                // $s1s=$s1s+$s1;
                // $s2s=$s2s+$s2;
                // $s3s=$s3s+$s3;
                // $s4s=$s4s+$s4;
                // $taos=$taos+$tao;
                // $mons=$mons+$jin;
                // $zonglis=$zonglis+$zongli;
                // $xinlis=$xinlis+$xinli;
                // $laolis=$laolis+$oldli;
                $lin[]=$zongdan;
                $lin[]=$xin;
                $lin[]=$old;
                $lin[]=$gen;
                $lin[]=$si;
                $lin[]=$gong;
                $lin[]=$s1;
                $lin[]=$s2;
                $lin[]=$s3;
                $lin[]=$s4;
                $lin[]=$tao;
                $lin[]=$jin;
                $lin[]=$zongli.'%';
                $lin[]=$xinli.'%';
                $lin[]=$oldli.'%';
                
                $lin[]=$jinkeid;
                $lin[]=$zongdanid;
                $lin[]=$xinid;
                $lin[]=$oldid;
                $lin[]=$genid;
                $lin[]=$siid;
                $lin[]=$gongid;
                $lin[]=$s1id;
                $lin[]=$s2id;
                $lin[]=$s3id;
                $lin[]=$s4id;
                $lin[]=$taoid;
                $shu[]=$lin;
        }
        // $lin[]='总计';
        // $lin[]=$jins;
        // $lin[]=$zongdais;
        // $lin[]=$xins;
        // $lin[]=$olds;
        // $lin[]=$gens;
        // $lin[]=$sis;
        // $lin[]=$gongs;
        // $lin[]=$s1s;
        // $lin[]=$s2s;
        // $lin[]=$s3s;
        // $lin[]=$s4s;
        // $lin[]=$taos;
        // $lin[]=$mons;
        // $lin[]=$zonglis;
        // $lin[]=$xinlis;
        // $lin[]=$laolis;
        // $shu[]=$lin;
        
        return json(['code'=>200,'data'=>$shu]);
    }
    function gets($data){
        static $ids=[];
        
        foreach($data as $v){
            $dd=Staff::where('pid',$v['id'])->select();
            if($dd){
                $this->gets($dd);
                $ids[]=$v['id'];
            }else{
                $ids[]=$v['id'];
            }
        }
        return $ids;
    }

    // 漏斗
    public function lou(){
        if(session('user.guide')!=1){
            $ids=Staff::where('pid','eq',session('user.id'))->select();
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $where[]=['s_id','in',$ids];
        }else{
            $where[]=['s_id','eq',session('user.id')];
        }
        $jin=User::count('id');
        $si=User::where($where)->count('id');
        $dai=Dai::where($where)->count('id');
        $jiao=Record::where($where)->count('id');
        $data=[];
        $data[]=[
            'action'=>'进客',
            'pv'=>$jin
        ];
        $data[]=[
            'action'=>'私客',
            'pv'=>$si
        ];
        $data[]=[
            'action'=>'带看',
            'pv'=>$dai
        ];
        $data[]=[
            'action'=>'成交',
            'pv'=>$jiao
        ];
        $res=[
            'code'=>200,
            'data'=>$data
        ];
        return json($res);
    }

    // 按渠道分进客
    public function jinfen(){
        if(session('user.guide')!=1){
            $ids=Staff::where('pid','eq',session('user.id'))->select();
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $where[]=['s_id','in',$ids];
        }else{
            $where[]=['s_id','eq',session('user.id')];
        }
        $data=Db::name('user')->where($where)->field("count('id') as total,port")->group('port')->select();
        $res=['code'=>200,'data'=>$data];
        return json($res);
    }

    // 重点客户
    public function zhong(){
        $where=[];
        $where[]=['grade','eq','A类'];
        if(session('user.guide')!=1){
            $ids=Staff::where('pid','eq',session('user.id'))->select();
            $ids=$this->gets($ids);
            $ids[]=session('user.id');
            $where[]=['s_id','in',$ids];
        }else{
            $where[]=['s_id','eq',session('user.id')];
        }
        $data=User::where($where)->select();
        foreach($data as $v){
            $v['project']=Building::where('id',$v['project'])->column('building_name');
            if($v['project']){
                $v['project']=$v['project'][0];
            }else{
                $v['project']='';
            }
            $id=Building::where('building_name','eq',$v['project'])->column('building_people');
            if($id){
                $id=$id[0];
            }else{
                $id='';
            }
            $tt=Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('t_time');
            $v['s_id']=Staff::where('id',$v['s_id'])->column('name');
            if($v['s_id']){
                $v['s_id']=$v['s_id'][0];
            }else{
                $v['s_id']='';
            }
            if($tt){
                $v['t_time']=date('Y-m-d H:i',$tt[0]);
                $v['g_time']=date('Y-m-d H:i',Gen::where('u_id',$v['id'])->order('id','desc')->limit(1)->column('create_time')[0]);
                $v['building_people']=Staff::where('id',$id)->column(['name'])[0];
            }
        }
        $res=[
            'code'=>'200',
            'data'=>$data
        ];
        return json($res);
    }

    // 判断项目负责人动态跟新情况
    public function checkdong(){        
        $id=session('user')['id'];
                
        $job=Staff::where('id','eq',$id)->column('job')[0];
        if(!in_array($id,[86,87,84,83])){
            return json(['code'=>200,'msg'=>'正常']);
        }
	    // $id=85;
        
        $ids=Building::where('charge_id','eq',$id)->column('id');
        
        if($ids){
            $ids=implode(',',$ids);
            $data=Db::query("select * from (select * from erp_guide where bid in ($ids) and status = 0 order by update_time desc) a  GROUP BY a.bid");
        }else{
            $data=[];
        }
        
        
        $time=time();
        $name='';
        $name1='';
        $name2='';
        $num=0;
        $l=0;
        $ids=[];
        
        foreach($data as $v){
            if($time-$v['update_time']>3600*24*7){
                $n=Building::where('id','eq',$v['bid'])->column('building_name')[0];
                Building::where('id','eq',$v['bid'])->update(['old'=>3]);
                $name.=$n.'-';
                $num=1;
                $l=1;
                $ids[]=$v['bid'];
            }else if($time-$v['update_time']>3600*24*6){
                $n1=Building::where('id','eq',$v['bid'])->column('building_name')[0];
                Building::where('id','eq',$v['bid'])->update(['old'=>2]);
                $name1.=$n1.'-';
                $l=2;
                $ids[]=$v['bid'];
                $num=1;
            }else if($time-$v['update_time']>3600*24*5){
                $n2=Building::where('id','eq',$v['bid'])->column('building_name')[0];
                Building::where('id','eq',$v['bid'])->update(['old'=>1]);
                $name2.=$n2.'-';
                $l=2;
                $ids[]=$v['bid'];
                $num=1;
            }else{
                Staff::where('id','eq',$id)->update(['check'=>0]);
            }
            $old=Building::where('id','eq',$v['bid'])->column('old');
            if($old){
                if($old[0]==4 || $old[0]==6){
                    $ids[]=$v['bid'];
                }
            }
            if($job==28){
                if($old){
                    if($old[0]==4){
                        $ids[]=$v['bid'];
                    }
                }
            }
        }
        if($l==1){
            Staff::where('id','eq',$id)->update(['check'=>2]);
        }else if($l==2){
            Staff::where('id','eq',$id)->update(['check'=>1]);
        }else{
            Staff::where('id','eq',$id)->update(['check'=>0]);
        }
        $ls=[];
        if($job==28){
            $ds=Staff::where('pid','eq',session('user.id'))->select();
            $ds=$this->gets($ds);
            $ds[]=session('user.id');
            $data=Building::where('charge_id','in',$ds)->where('status','eq',1)->column('id');
            if($data){
                $num=1;
            }
            foreach($data as $v){
                $ids[]=$v;
            }
                        
        }

        if($num==0){
            return json(['code'=>200,'msg'=>'正常']);
        }else{
            // $job=28;
            //$job=Staff::where('id','eq',session('user.id'))->column('job')[0];
            Cache::set('check'.$id,$ids,7200);
            return json(['code'=>202,'name1'=>$name1,'name2'=>$name2,'name'=>$name,'l'=>$l,'ids'=>$ids,'job'=>$job]);
        }
    }

    
}
