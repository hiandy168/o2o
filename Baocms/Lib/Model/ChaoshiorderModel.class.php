<?php
class ChaoshiorderModel extends RelationModel {
    protected $pk = 'order_id';
    protected $tableName = 'chaoshi_order';
    protected $cfg = array(
        0 => '等待付款',
        1 => '等待审核',
        2 => '正在配送',
        5 => '申请取消',
        6 => '已取消',
        8 => '已完成',
        9 => '已评价',
    );
    protected $_link = array(
        'order_products'=>array(
            'mapping_type'=>HAS_MANY,
            'class_name'=>'chaoshi_order_product',
            'foreign_key'=>'order_id',
        ),
        'chaoshi_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'chaoshi',
            'foreign_key'=>'store_id',
        ),
        'user_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'users',
            'foreign_key'=>'user_id',
        ),
        'product_list'=>array(
            'mapping_type'=>MANY_TO_MANY,
            'class_name'=>'chaoshi_product',
            'relation_foreign_key'=>'product_id',
            'foreign_key'=>'order_id',
            'relation_table'=>'bao_chaoshi_order_product',
        )
         
    );
    protected $_validate = array(
        array('phone','require','电话号码不能为空！',1),
        array('name','require','收货人姓名不能为空！',1),
        array('receipt_addr','require','收货人地址不能为空！',1),
        array('send_time','is_time','送达时间不能为空！',1,'callback',1),
    );
    protected $_auto = array(
        array('create_time','time',1,'function'),
        array('create_ip','get_client_ip',1,'function'),
        array('month','getMonth',1,'callback'),
    );
    protected function is_time($date){
        $time = strtotime($date);
        if (!$time){
            return false;
        }else {
            return true;
        }
    }
    public function overOrder($order_id) {      
        $detail = $this->find($order_id);
        $chaoshi = D('Chaoshi')->where(array('store_id'=>$detail['store_id']))->find();
        if (empty($detail)){
            return false;            
        }
        if ($detail['status'] != 2){
            return false;                
        }
        $shop_model = D('Shop');
        $shop = $shop_model->find($chaoshi['shop_id']);   
        $this->startTrans();
        $order_res = $this->save(array('order_id' => $order_id, 'status' => 5));          
        $money = $detail['pay_price'];
        if ($money > 0) {
            D('ShopMoney')->add(array(
                'shop_id' => $chaoshi['shop_id'],
                'type' => 'chaoshi',
                'money' => '+'.$money,
                'create_ip' => get_client_ip(),
                'create_time' => NOW_TIME,
                'order_id' => $order_id,
                'intro' => '超市订单:' . $order_id.'收款',
            ));
        
            $money_res = $shop_model->updateMoney($shop['shop_id'], $money, 1);
        }
        $sold_num_res = D('Chaoshi')->updateCount($detail['store_id'], 'sold_num'); //这里是订单数
        $sold_month_res = D('Chaoshi')->updateMonth($detail['store_id']);
        
        if ($order_res && $money_res && $sold_num_res && $sold_month_res){
            $this->commit();
            return true;
        }else {
            $this->rollback();
            return false;
        }        
    }
    
    public function getCfg() {
        return $this->cfg;
    }
    
    public function checkIsNew($uid, $store_id) {
        $uid = (int) $uid;
        $store_id = (int) $store_id;
        $res = $this->where(array('user_id' => $uid, 'store_id' => $store_id, 'closed' => 0))->count();
        if (!$res) {
            return true;
        }else {
            return false;
        }
    }
    public function getMonth(){
        return date('Ym', NOW_TIME);
    }
}