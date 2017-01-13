<?php



class PaymentAction extends CommonAction {

    protected function ele_success($message, $detail) {
        $order_id = $detail['order_id'];
        $eleorder = D('Eleorder')->find($order_id);
        $detail['single_time'] = $eleorder['create_time'];
        $detail['settlement_price'] = $eleorder['settlement_price'];
        $detail['new_money'] = $eleorder['new_money'];
        $detail['fan_money'] = $eleorder['fan_money'];
        $addr_id = $eleorder['addr_id'];
        $product_ids = array();
        $ele_goods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        foreach ($ele_goods as $k => $val) {
            if (!empty($val['product_id'])) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
        }
        $addr = D('Useraddr')->find($addr_id);
        $this->assign('addr', $addr);
        $this->assign('ele_goods', $ele_goods);
        $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('ele');
    }

    protected function goods_success($message, $detail) {
        $order_ids = array();
        if (!empty($detail['order_id'])) {
            $order_ids[] = $detail['order_id'];
        } else {
            $order_ids = explode(',', $detail['order_ids']);
        }
        $goods = $good_ids = $addrs = array();
        foreach ($order_ids as $k => $val) {
            if (!empty($val)) {
                $order = D('Order')->find($val);
                $addr = D('Useraddr')->find($order['addr_id']);
                $ordergoods = D('Ordergoods')->where(array('order_id' => $val))->select();
                foreach ($ordergoods as $a => $v) {
                    $good_ids[$v['goods_id']] = $v['goods_id'];
                }
            }
            $goods[$k] = $ordergoods;
            $addrs[$k] = $addr;
        }
        $this->assign('addr', $addrs[0]);
        $this->assign('goods', $goods);
        $this->assign('good', D('Goods')->itemsByIds($good_ids));
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('goods');
    }

    public function detail($order_id) {
        $dingorder = D('Shopdingorder');
        $dingyuyue = D('Shopdingyuyue');
        $dingmenu = D('Shopdingmenu');
        if (!$order = $dingorder->where('order_id = ' . $order_id)->find()) {
            $this->baoError('该订单不存在');
        } else if (!$yuyue = $dingyuyue->where('ding_id = ' . $order['ding_id'])->find()) {
            $this->baoError('该订单不存在');
        } else if ($yuyue['user_id'] != $this->uid) {
            $this->error('非法操作');
        } else {
            $arr = $dingorder->get_detail($this->shop_id, $order, $yuyue);
            $menu = $dingmenu->shop_menu($this->shop_id);
            $this->assign('yuyue', $yuyue);
            $this->assign('order', $order);
            $this->assign('order_id', $order_id);
            $this->assign('arr', $arr);
            $this->assign('menu', $menu);
            $this->display();
        }
    }

    protected function ding_success($message, $detail) {
        $dingorder = D('Shopdingorder');
        $dingyuyue = D('Shopdingyuyue');
        $dingmenu = D('Shopdingmenu');

        if (!$order = $dingorder->where('order_id = ' . $detail['order_id'])->find()) {
            $this->error('该订单不存在');
        } else if (!$yuyue = $dingyuyue->where('ding_id = ' . $order['ding_id'])->find()) {
            $this->error('该订单不存在');
        } else if ($yuyue['user_id'] != $this->shop_id) {
            $this->error('非法操作');
        } else {
            $arr = $dingorder->get_detail($yuyue['shop_id'], $order, $yuyue);
            $menu = $dingmenu->shop_menu($yuyue['shop_id']);
            $this->assign('yuyue', $yuyue);
            $this->assign('order', $order);
            $this->assign('order_id', $detail['order_id']);
            $this->assign('arr', $arr);
            $this->assign('menu', $menu);
            $this->assign('message', $message);
            $this->assign('paytype', D('Payment')->getPayments());
            $this->display('ding');
        }
    }

    protected function chaoshi_success($message, $detail) {
        $order_id = $detail['order_id'];
        $chaoshiorder = D('Chaoshiorder')->find($order_id);
        $detail['single_time'] = $chaoshiorder['create_time'];
        $detail['settlement_price'] = $chaoshiorder['settlement_price'];
        $addr_id = $chaoshiorder['addr_id'];
        $product_ids = array();
        $chaoshi_products = D('Chaoshiorderproduct')->where(array('order_id'=>$order_id))->select();
        foreach ($chaoshi_products as $k=>$val){
            if(!empty($val['product_id'])){
                $product_ids[$val['product_id']] = $val['product_id'];
            }
        }
        $addr = D('Useraddr')->find($addr_id);
        $this->assign('addr',$addr);
        $this->assign('chaoshi_products',$chaoshi_products);
        $this->assign('products',D('Chaoshiproduct')->itemsByIds($product_ids));
        $this->assign('message',$message);
        $this->assign('detail',$detail);
        $this->assign('chaoshiorder',$chaoshiorder);
        $this->assign('paytype', D('Payment')->getPayments());
        
        $this->display('chaoshi');
    }
    
    protected function meishi_success($message, $detail) {
        $order_id = $detail['order_id'];
        $meishi_order_model = D('MeishiOrder');
        $meishiorder = $meishi_order_model->relation(true)->find($order_id);
        $detail['single_time'] = $meishiorder['create_time'];

        $this->assign('message',$message);
        $this->assign('detail',$detail);
        $this->assign('meishiorder',$meishiorder);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('meishi');
    }
    
    protected function hotel_success($message, $detail) {
        $order_id = $detail['order_id'];
        $hotel_order_model = D('HotelOrder');
        $hotelorder = $hotel_order_model->relation(true)->find($order_id);
        $detail['single_time'] = $hotelorder['create_time'];
    
        $this->assign('message',$message);
        $this->assign('detail',$detail);
        $this->assign('hotelorder',$hotelorder);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('hotel');
    }
    
    protected function other_success($message, $detail) {
        //dump($detail);
        $tuanorder = D('Tuanorder')->find($detail['order_id']);
        if (!empty($tuanorder['branch_id'])) {
            $branch = D('Shopbranch')->find($tuanorder['branch_id']);
            $addr = $branch['addr'];
        } else {
            $shop = D('Shop')->find($tuanorder['shop_id']);
            $addr = $shop['addr'];
        }

        $this->assign('addr', $addr);
        $tuans = D('Tuan')->find($tuanorder['tuan_id']);
        $this->assign('tuans', $tuans);
        $this->assign('tuanorder', $tuanorder);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('other');
    }

    public function respond() {
        if (I('subject')){
            $code = 'alipay';
        }else {
            $code = 'weixin';
        }
        if (empty($code)) {
            $this->error('没有该支付方式！');
            die;
        }
        if($code == 'weixin'){
            define('IN_MOBILE', 1);
        }
        $ret = D('Payment')->respond($code);
        if ($ret == false) {
            $this->error('支付验证失败！');
            die;
        }
        if ($this->isPost()) {
            echo 'SUCESS';
            die;
        }              
        $log_id = D('Payment')->getLogId();        
        $detail = D('Paymentlogs')->find($log_id);
        $type = $detail['type'];
        if(!empty($detail)){
            if ($type == 'ele') {
                $this->ele_success('恭喜您支付成功啦！', $detail);
            } elseif ($type == 'ding') {
                $this->ding_success('恭喜您支付成功啦！', $detail);
            } elseif ($type == 'chaoshi') {
                $this->chaoshi_success('恭喜您支付成功啦！', $detail);
            } elseif ($type == 'meishi') {
                $this->meishi_success('恭喜您支付成功啦！', $detail);
            } elseif ($type == 'hotel') {
                $this->hotel_success('恭喜您支付成功啦！', $detail);
            } elseif ($type == 'goods') {
                
                if(empty($detail['order_id'])){
                    $this->success('合并付款成功', U('member/order/index'));
                }else{
                    $this->goods_success('恭喜您支付成功啦！', $detail);
                }
               
             
            } elseif ($type == 'gold' || $detail['type'] == 'money') {
                $this->success('恭喜您充值成功', U('member/index/index'));

            } else {
                $this->other_success('恭喜您支付成功啦！', $detail);
            }
        }else{
             $this->success('支付成功', U('member/index/index'));
        }
        //if(empty($type) || empty($log_id)){
        //$this->success('支付成功！', U('index/index'));
        //  }
    }

    public function payment($log_id) {
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $log_id = think_decrypt($log_id);
        $logs = D('Paymentlogs')->find($log_id);
        if (empty($logs) || $logs['user_id'] != $this->uid || $logs['is_paid'] == 1) {
            $this->error('没有有效的支付记录！');
            die;
        }
        $url = "";
        if ($logs['type'] == "tuan") {
            $url = U('tuan/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "ele") {
            $url = U('ele/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "goods") {
            $url = U('mall/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "ding") {
            $url = U('ding/pay2', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "chaoshi") {
            $url = U('chaoshi/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "meishi") {
            $url = U('Hmeishi/order/pay', array('order_id' => $logs['order_id']),'html',false,C('BASE_SITE'));
        } elseif ($logs['type'] == "hotel") {
            $url = U('jiudian/order/payment', array('order_id' => $logs['order_id']),'html',false,C('BASE_SITE'));
        }
   //var_dump(D('Payment')->getCode($logs));exit();   
        $this->assign('url', $url);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('types', D('Payment')->getTypes());
        $this->assign('logs', $logs);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->assign('remain_time', time() - session('pay_use_remain_time'));
        $this->assign('remain_time_help', 60-(time() - session('pay_use_remain_time')));
        // 帮助导航
        $this->assign('helpCates', $helpCates = $this->helpCates());
        $this->display('payment');
    }
/**
 * 更改支付方式
 * @author 刘弢
 */    
    public function change_payment_type(){        
        $payment_model = D('Payment');
        $payment_logs_model =  D('PaymentLogs');        
        $order_classes = $payment_model->getOrderClass();
        
        $log_id = I('log_id','0','intval');
        $log = $payment_logs_model->find($log_id);
        if ($log['is_paid'] == 1){
            $this->baoError('订单已支付');
        }
        $order_model = D($order_classes[$log['type']]);
        
        $order_pay_types = $payment_model->getOrderPayTypes();
        $code = I('code');
        $pay_type = $order_pay_types[$code];
        $order_model->startTrans();
        $order_res = $order_model->where(array('order_id'=>$log['order_id']))->setField('pay_type',$pay_type);
        $log_res = $payment_logs_model->where(array('log_id'=>$log_id))->setField('code',$code);
        if ($order_res && $log_res){
            $order_model->commit();
            $this->baoSuccess('修改成功，请稍候......',U('payment/payment', array('log_id' => think_encrypt($log_id))));
        }else {
            $order_model->rollback();
            $this->baoError('修改失败');
        }
    }
}
