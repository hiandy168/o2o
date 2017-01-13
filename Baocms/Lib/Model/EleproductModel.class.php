<?php

/* 
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class EleproductModel extends CommonModel{
    protected $pk   = 'product_id';
    protected $tableName =  'ele_product';

    private static $eleproduct = null;
    private static $elecart = null;
    
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