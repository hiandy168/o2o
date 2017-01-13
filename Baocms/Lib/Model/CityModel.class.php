<?php

/**
 * 描述：城市模型
 * 作者：王恒
 * 时间：2016-4-19
 * 联系方式：QQ337886915
 */

class CityModel extends CommonModel
{
    protected $pk   = 'city_id';
    protected $tableName =  'city';
    protected $token = 'city';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token)
    {
        $this->token = $token;
    }
    public function autoregister($ip="182.137.14.169")
    {
       $current = get_current_address_by_baidu($ip);      
       if(!$current->status) {   
    	   $name = $current->content->address_detail->city;  
    	   if (empty($name) || stristr($name, 'null')) {
    	   		$current = get_current_address_by_baidu("182.137.14.169");
	     		$name = $current->content->address_detail->city;
    	   } 		       
       } else {
       	   $current = get_current_address_by_baidu("182.137.14.169");
	       $name = $current->content->address_detail->city;
       }  
       $area = $this->where(array('name'=>$name))->find();
       if($area){
           return $area;
       }else{
           if (!empty($name) && !stristr($name, 'null')) {
            	$data['name'] = $name;
	            $data['pinyin'] = get_pinyin($current->content->address_detail->city);
	            $data['lng'] = $current->content->point->x/100000;
	            $data['lat'] = $current->content->point->y/100000;
	            $data['first_letter'] = strtoupper(substr($data['pinyin'],0,1));
	            $data['city_id'] = $this->add($data);
	            $this->cleanCache();
	            return $data; 
           }                     
       }           
   }
}
