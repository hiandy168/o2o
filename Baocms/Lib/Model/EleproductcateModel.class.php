<?php
class EleproductcateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'ele_product_cate';
    
    public function getProductCate($store_id) {
        return $this->where(array('store_id'=>$store_id,'closed'=>0))->select();
    }
}