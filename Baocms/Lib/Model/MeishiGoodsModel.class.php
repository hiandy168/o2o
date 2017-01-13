<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-22
 */

class MeishiGoodsModel extends CommonModel
{
    //删除标示
    public $flag = array(
    						'exist' => 0,
    						'delete' => 1,
    					);
    //根据商品id删除商品
    public function deleteById($id)
    {
    	return $this->where(array('goods_id' => $id))->save(array('closed' => $this->flag['delete']));
    }
    
    //根据店铺id删除商品
    public function deleteByStore($storeId)
    {
    	return $this->where(array('store_id' => $storeId))->save(array('closed' => $this->flag['delete']));
    }
}