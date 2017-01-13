<?php
class ShopnavModel extends Model{
    protected $tableName =  'shop_nav';
 
    public function getNav($field='*',$where=array()) {
        return $this->where($where)->field($field)->order('orderby')->select();
    }
}