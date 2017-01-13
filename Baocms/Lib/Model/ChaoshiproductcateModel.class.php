<?php
class ChaoshiproductcateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'chaoshi_product_cate';
    
    public function getProductCate($store_id) {
        return $this->where(array('store_id'=>$store_id,'closed'=>0))->select();
    }
}