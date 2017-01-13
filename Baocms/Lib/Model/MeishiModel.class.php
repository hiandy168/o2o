<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-22
 */

class MeishiModel extends CommonModel
{
      protected $_validate=  array(
        array('store_name','require','店铺名必填'),
        array('store_name','','店铺名已存在',0,'unique',0),
        array('city_id','require','请选择所在城市'),
        array('lng','require','店铺经度不能为空'),
        array('lat','require','店铺纬度不能为空'),
     );
	private static $meishiProduct = null;
    private static $meishicart = null;
	
    //删除标示
    public $flag = array(
    					'exist' => 0,
    					'delete' => 1,
    				);
   //根据店铺id删除店铺
    public function deleteById($storeId)
    {
    	return $this->where(array('store_id' => $storeId))->save(array('closed' => $this->flag['delete']));
    }
    
    //根据商家id获取店铺id
    public function getStoreIds($shopId){
    	return $this->field('store_id')->where(array('shop_id' => $shopId , 'closed' => $this->flag['exist']))->select();
    }
    
    //根据店铺id删除所有商品
    public function deleteAll($storeId){
    	$this->getMeishiProductModel();
    	$this->getMeishiCartModel();
    	if (self::$meishiProduct->deleteByStore($storeId) !== false) {
    		self::$meishicart->clearByStoreId($storeId);
            if ($this->deleteById($storeId) !== false) {
            	return true;
            }   
            return false;        
        }
        return false;
    }
    
    public function getMeishiProductModel(){
    	if (self::$meishiProduct === null) {
    		return self::$meishiProduct = D('Meishiproduct');
    	}
    	return self::$meishiProduct;
    }
    
 	public function getMeishiCartModel()
    {
    	if (self::$meishicart === null) {
    		return self::$meishicart = D('MeishiCart');
    	}
    	return self::$meishicart;
    }
}