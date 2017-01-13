<?php
class ShopprimpModel extends CommonModel {
    protected $pk   = 'primp_id';
    protected $tableName =  'shop_primp';
    
    public function getPrimp($shop_id) {
        return $this->where(array('shop_id'=>$shop_id))->find();
    }
    
    public function setPrimp($shop_id,$data) {
        $primp = $this->where(array('shop_id'=>$shop_id))->find();
        if ($primp){
            $this->where(array('shop_id'=>$shop_id))->save($data);
            return true;
        }else {
            $this->add($data);
            return true;
        }
    }
}