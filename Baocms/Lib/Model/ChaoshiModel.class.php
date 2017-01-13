<?php

/*
 * 好吉来电子商务
 * 作者：王恒
 * 时间：2016-4-13
 * 邮件: 337886915@qq.com  QQ 337886915
 */

class ChaoshiModel extends CommonModel {

    protected $pk = 'store_id';
    protected $tableName = 'chaoshi';
    protected $_validate=  array(
        array('store_name','require','店铺名必填'),
        array('logo','require','LOGO必需'),
        array('store_name','','店铺名已存在',0,'unique',0),
        array('city_id','require','请选择所在城市'),
        array('lng','require','店铺经度不能为空'),
        array('lat','require','店铺纬度不能为空'),
     );
     
    private static $chaoshiproduct = null;
    private static $chaoshicart = null;
     
    //删除标示
    public $flag = array(
    					'exist' => 0,
    					'delete' => 1,
    				);
	//店铺状态
	public $status = array(
						'normal' => 0,
						'reorganize' => 1,
					);
    				
    public function updateMonth($store_id) {
        $store_id = (int) $store_id;
        $month = date('Ym', NOW_TIME);
        $num = (int) (D('Chaoshiorder')->where(array('store_id' => $store_id, 'month' => $month))->count());        
        return $this->execute("update " . $this->getTableName() . " set  month_num={$num} where store_id={$store_id}");
    }

    public function getChaoshiCate() {
        $chaoshicate_mod = D('ChaoshiCate');
        $catelist = $chaoshicate_mod->fetchAll();
        $returndata = array();
        foreach($catelist as $k=>$v){
            $returndata[$v['chaoshi_cate_id']] = $v['cate_name'];
        }
        return $returndata;
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
    
    public function setdefault($store_id,$shop_id){
        $this->where(array('shop_id'=>$shop_id))->save(array('is_default'=>0));
         return $this->where(array('store_id'=>$store_id))->save(array('is_default'=>1));
        
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
    
    // 根据店铺id删除超市
    public function deleteAll($storeId)
    {
    	$this->getChaoshiproductModel();
    	$this->getChaoshicartModel();
    	if (self::$chaoshiproduct->deleteByStore($storeId) !== false) {
    		self::$chaoshicart->clearByStoreId($storeId);
            if ($this->deleteById($storeId) !== false) {
            	return true;
            }   
            return false;        
        }
        return false;
    }
    
    public function getChaoshiproductModel()
    {
    	if (self::$chaoshiproduct === null) {
    		return self::$chaoshiproduct = D('Chaoshiproduct');
    	}
    	return self::$chaoshiproduct;
    }
    
	public function getChaoshicartModel()
    {
    	if (self::$chaoshicart === null) {
    		return self::$chaoshicart = D('ChaoshiCart');
    	}
    	return self::$chaoshicart;
    }
    
    //修改店铺的整顿状态
    public function updateStatus($storeId, $status)
    {
    	return $this->where(array('store_id' => $storeId))->save(array('status' => $status));
    }
    
    //整顿店铺
 	public function reorganize($storeId)
    {
    	$this->getChaoshicartModel();
    	if ($this->updateStatus($storeId, $this->status['reorganize']) !== false) {
    		self::$chaoshicart->clearByStoreId($storeId);
            return true;          
        }
        return false;
    }
}
