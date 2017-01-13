<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 3:27
 */
class HotelModel extends CommonModel
{
    
    protected $pk = 'hotel_id';
        protected $tableName = 'hotel';
        protected $_validate=  array(
            array('store_name','require','店铺名必填'),
            array('store_name','','店铺名已存在',0,'unique',0),
            array('city_id','require','请选择所在城市'),
            array('lng','require','店铺经度不能为空'),
            array('lat','require','店铺纬度不能为空'),
         );
    public function HotelCate(){

        return array(
            '1' => '星级酒店',
            '2' => '主题酒店',
            '3' => '商务酒店',
            '4' => '经济酒店',
            '5' => '客栈民宿',
            '6' => '青年旅社',
            '7'=>'日租公寓',
            '8'=>'其它类型',
        );}
    public function HotelBrand(){
        return array(
            '1' => '如家',
            '2' => '7天',
            '3' => '汉庭',
            '4' => '速8',
            '5' => '锦江之星',
            '6' => '99联锁',
            '7'=>'莫泰',
            '8'=>'格林豪泰',
            '9'=>'其它品牌',
        );}

    //删除标示
    public $flag = array(
    					'exist' => 0,
    					'delete' => 1,
    				);
    				
  	private static $hotelroom = null;

    //根据店铺id删除店铺
    public function deleteById($storeId)
    {
    	return $this->where(array('hotel_id' => $storeId))->save(array('closed' => $this->flag['delete']));
    }
    
    //根据商家id获取店铺id
    public function getStoreIds($shopId)
    {
    	return $this->field('hotel_id as store_id')->where(array('shop_id' => $shopId , 'closed' => $this->flag['exist']))->select();
    }
    
    //根据店铺id删除所有商品
    public function deleteAll($storeId)
    {
    	$this->getHotelroomModel();
    	if (self::$hotelroom->deleteByStore($storeId) !== false) {
            if ($this->deleteById($storeId) !== false) {
            	return true;
            }   
            return false;        
        }
        return false;
    }
    
    public function getHotelroomModel()
    {
    	if (self::$hotelroom === null) {
    		return self::$hotelroom = D('HotelRoom');
    	}
    	return self::$hotelroom;
    }












}