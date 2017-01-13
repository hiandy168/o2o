<?php

class AreaAction extends CommonAction{
  
    public function get_area($city_id=0){
        $city_id || $this->ajaxReturn(array('status'=>0,'data'=>"","info"=>"暂无数据"));
        $children = D('Area')->where(array('city_id'=>$city_id))->select();
        $callback = $_GET['callback'];
        if($children){
             echo $callback.'('.json_encode(array('status'=>1,'data'=>$children,"info"=>"获取成功")).')';die;
        }else{
            echo $callback.'('.json_encode(array('status'=>0,'data'=>$children,"info"=>"暂无数据")).')';die;
        }
    }















}