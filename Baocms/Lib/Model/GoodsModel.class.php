<?php



class GoodsModel extends CommonModel{
    protected $pk   = 'goods_id';
    protected $tableName =  'goods';
	 protected $_validate = array(
        array( ),
        array( ),
        array( )
    ); 
    
    public function _format($data){
        $data['save'] =  $data['price'] - $data['mall_price'];
        $data['price'] = $data['price']; 
        $data['mall_price'] = $data['mall_price']; 
        $data['settlement_price'] = $data['settlement_price']; 
        $data['commission'] = $data['commission']; 
        $data['discount'] = $data['mall_price'] * 10 / $data['price'];
        return $data;
    }
    public function goods_status($goods_id){
        //查看商品是否正在交易中
        
        $sql = "select count(a.id) as count from ".C('DB_PREFIX')."order_goods as a inner join ".C('DB_PREFIX')."order as b on a.order_id=b.order_id where a.goods_id={$goods_id} and b.status=3";
        
        $info = $this->query($sql);
       
        if($info[0][count]>0){
            return array('status'=>0,'info'=>'商品正在交易中，请结束所有与该商品相关的交易后在删除');
        }
        return array('status'=>1,'info'=>'可以删除');
    }
    //删除商品
    public function ajax_delete($goods_id){
        $status = $this->goods_status($goods_id); 
        if($status['status']==0){
            //var_dump(111);die;
            return $status;
        }
         
        $this->delete($goods_id);
        M('goods_photos')->where(array('goods_id'=>$goods_id))->delete();
        M('goods_dianping_pics')->where(array('goods_id'=>$goods_id))->delete();
        M('goods_dianping')->where(array('goods_id'=>$goods_id))->delete();
        return array('status'=>1,'info'=>"删除成功");
        
          
    }
    
}