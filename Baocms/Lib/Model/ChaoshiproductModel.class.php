<?php

/* 
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class ChaoshiproductModel extends CommonModel{
    protected $pk   = 'product_id';
    protected $tableName =  'chaoshi_product';

    private static $chaoshiproduct = null;
    private static $chaoshicart = null;
    
    //删除标示
    public $flag = array(
    						'exist' => 0,
    						'delete' => 1,
    					);
    //根据商品id删除商品
    public function deleteById($id)
    {
    	return $this->where(array('product_id' => $id))->save(array('closed' => $this->flag['delete']));
    }
    
    //根据店铺id删除商品
    public function deleteByStore($storeId)
    {
    	return $this->where(array('store_id' => $storeId))->save(array('closed' => $this->flag['delete']));
    }

    // 根据店铺id删除所有商品
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
}