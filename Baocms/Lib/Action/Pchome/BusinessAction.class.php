<?php

class BusinessAction extends CommonAction{
  
    public function get_bussiness($area_id=0){
        $callback = $_GET['callback'];
        if(!$area_id){
            echo $callback.'('.json_encode(array('status'=>0,'data'=>"","info"=>"暂无数据")).')';die;
        }
         
        $children = D('Business')->where(array('area_id'=>$area_id))->select();
        $newarray = array();
        foreach ($children as $k => $v) {
            $newarray[$v['first_letter']][]=$v;
        }
        if($children){
            
            echo $callback.'('.json_encode(array('status'=>1,'data'=>$newarray,"info"=>"获取成功")).')';die;
        }else{
           
            echo $callback.'('.json_encode(array('status'=>0,'data'=>"","info"=>"暂无数据")).')';die;
        }
    }

    public function children($area_id = 0,$curr_id=0){
        $datas = D('Business')->fetchAll();
        $str ='<option value="0">请选择</option>';
        foreach($datas as $val){
            if($val['area_id'] == $area_id){
                if($curr_id==$val['business_id']){
                    $str.='<option selected value="'.$val['business_id'].'">'.$val['business_name'].'</option>'; 
                }else{
                    $str.='<option value="'.$val['business_id'].'">'.$val['business_name'].'</option>'; 
                }
                               
            }            
        }
        echo $str;die;
    }














}