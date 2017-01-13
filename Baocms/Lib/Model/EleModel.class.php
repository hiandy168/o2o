<?php



class EleModel extends CommonModel {

    protected $pk = 'store_id';
    protected $tableName = 'ele';
    protected $_validate=  array(
        array('store_name','require','店铺名必填'),
        array('store_name','','店铺名已存在',0,'unique',0),
         array('city_id','require','请选择所在城市'),
        array('lng','require','店铺经度不能为空'),
        array('lat','require','店铺纬度不能为空'),
     );

     private static $eleproduct = null;
     private static $elecart = null;
     
    //删除标示
    public $flag = array(
    					'exist' => 0,
    					'delete' => 1,
    				);
     
    public function updateMonth($shop_id) {
        $shop_id = (int) $shop_id;
        $month = date('Ym', NOW_TIME);
        $num = (int) (D('Eleorder')->where(array('shop_id' => $shop_id, 'month' => $month))->count());
        return $this->execute("update " . $this->getTableName() . " set  month_num={$num} where shop_id={$shop_id}");
    }

    public function getEleCate() {
        return array(
            '1' => '快餐简餐',
            '2' => '正餐',
            '3' => '馋嘴小吃',
            '4' => '甜点饮料',
            '5' => '生活超市',
            '6' => '水果蔬菜',
        );
    }
    
    
    public function CallDataForMat($items) { //专门针对CALLDATA 标签处理的
        if (empty($items))
            return array();
        $obj = D('Shop');
        $shop_ids = array();
        foreach ($items as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $shops = $obj->itemsByIds($shop_ids);
        foreach ($items as $k => $val) {
            $val['shop'] = $shops[$val['shop_id']];
            $items[$k] = $val;
        }
        return $items;
    }

    //根据店铺id删除店铺
    public function deleteById($storeId)
    {
    	return $this->where(array('store_id' => $storeId))->save(array('closed' => $this->flag['delete']));
    }
    
    //根据商家id获取店铺id
    public function getStoreIds($shopId)
    {
    	return $this->field('store_id')->where(array('shop_id' => $shopId , 'closed' => $this->flag['exist']))->select();
    }
    
    //根据店铺id删除所有商品
    public function deleteAll($storeId)
    {
    	$this->getEleproductModel();
    	$this->getElecartModel();
    	if (self::$eleproduct->deleteByStore($storeId) !== false) {
    		self::$elecart->clearByStoreId($storeId);   //清空购物车
            if ($this->deleteById($storeId) !== false) {
            	return true;
            }   
            return false;        
        }
        return false;
    }
    
    public function getEleproductModel()
    {
    	if (self::$eleproduct === null) {
    		return self::$eleproduct = D('Eleproduct');
    	}
    	return self::$eleproduct;
    }
    
	public function getElecartModel()
    {
    	if (self::$elecart === null) {
    		return self::$elecart = D('EleCart');
    	}
    	return self::$elecart;
    }
}
