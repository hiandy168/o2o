<?php
/**
 * @author : Lucifer
 * @createTime 2016-10-10
 */
class MeishiCartModel extends CommonModel
{
   //商品删除时清空购物车对应的商品
    public function clearByProductId($productId)
    {
    	return $this->where(array('goods_id' => $productId))->delete();
    }
    
    //店铺删除时清空购物车对应的商品
    public function clearByStoreId($storeId)
    {
    	return $this->where(array('store_id' => $storeId))->delete();
    }
}