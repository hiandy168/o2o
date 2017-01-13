<?php



class PayAction extends CommonAction {

    protected function ele_success($message, $detail) {
        $order_id = $detail['order_id'];
        $eleorder = D('Eleorder')->find($order_id);
        $detail['single_time'] = $eleorder['create_time'];
        $detail['settlement_price'] = $eleorder['settlement_price'];
        $detail['new_money'] = $eleorder['new_money'];
        $detail['discount_money'] = $eleorder['discount_money'];
        $detail['store_id'] = $eleorder['store_id'];
        $addr_id = $eleorder['addr_id'];
        $product_ids = array();
        $ele_goods = D('Eleorderproduct')->where(array('order_id'=>$order_id))->select();
        foreach ($ele_goods as $k=>$val){
            if(!empty($val['product_id'])){
                $product_ids[$val['product_id']] = $val['product_id'];
            }
        }	 	
        $addr = D('Useraddr')->find($addr_id);
        $this->assign('addr',$addr);
        $this->assign('ele_goods',$ele_goods);
        $this->assign('products',D('Eleproduct')->itemsByIds($product_ids));
        $this->assign('message',$message);
        $this->assign('detail',$detail);
        $this->assign('paytype', D('Payment')->getPayments());
			 //====================微信支付通知===========================
// 			$map          = array('product_id'=>array('in',$product_ids));
//             $product_name = D('Eleproduct')->where($map)->getField('product_name',true);
//             $product_name = implode(',', $product_name);
//             include_once "Baocms/Lib/Net/Wxmesg.class.php";
//             $_data_pay = array(
//                 'url'       =>  "http://".$_SERVER['HTTP_HOST']."mcenter/eleorder/index.html",
//                 'topcolor'  =>  '#F55555',
//                 'first'     =>  '亲,您的订单已支付完成,我们马上发货！',
//                 'remark'    =>  '更多信息,请登录http://'.$_SERVER['HTTP_HOST'].'再次感谢您的惠顾！',
//                 'money'     =>  $eleorder['need_pay'].'元',
//                 'orderInfo' =>  $product_name,
//                 'addr'      =>  $addr['addr'],
//                 'orderNum'  =>  '1-'.$order_id
//             );
//             $pay_data = Wxmesg::pay($_data_pay);
//             $return   = Wxmesg::net($this->uid, 'OPENTM202243342', $pay_data);

            //====================微信支付通知==============================
        $this->display('ele');
    }
    
    protected function goods_success($message, $detail) {
        $order_ids = array();
        if(!empty($detail['order_id'])){
            $order_ids[] = $detail['order_id'];
        }else{
            $order_ids = explode(',',$detail['order_ids']);
        }
        $goods = $good_ids = $addrs = array();
        $use_integral = 0;
        foreach($order_ids as $k=>$val){
            if(!empty($val)){
                $order = D('Order')->find($val);
                $addr = D('Useraddr')->find($order['addr_id']);
                $ordergoods = D('Ordergoods')->where(array('order_id'=>$val))->select();
                foreach($ordergoods as $a=>$v){
                    $good_ids[$v['goods_id']] = $v['goods_id'];
                    $use_integral += $v['use_integral'];
                }
            }
            $goods[$k] = $ordergoods;
            $addrs[$k] = $addr;
        }
		 	
        $this->assign('use_integral',$use_integral);
        $this->assign('addr',$addrs[0]);
        $this->assign('goods',$goods);
        $this->assign('good',D('Goods')->itemsByIds($good_ids));
        $this->assign('detail',$detail);
        $this->assign('message',$message);
        $this->assign('paytype', D('Payment')->getPayments());
		 //====================微信支付通知===========================

           
            $map         = array('order_ids'=>array('in',$goods_ids));
            $goods_name  = D('Goods')->where($map)->getField('title',true);
            $goods_name  = implode(',', $goods_name);
             include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $_data_pay = array(
                'url'       =>  "http://".$_SERVER['HTTP_HOST']."mcenter/goods/index.html",
                'topcolor'  =>  '#F55555',
                'first'     =>  '亲,您的订单已支付完成,我们马上发货！',
                'remark'    =>  '更多信息,请登录http://'.$_SERVER['HTTP_HOST'].'再次感谢您的惠顾！',
                'money'     =>  $order['total_price'].'元',
                'orderInfo' =>  $goods_name,
                'addr'      =>  $addr['addr'],
                'orderNum'  =>  '1-'.$order['order_id'],
            );
            $pay_data = Wxmesg::pay($_data_pay);
            $return   = Wxmesg::net($this->uid, 'OPENTM202243342', $pay_data);


            //====================微信支付通知==============================

        $this->display('goods');
    }

	public function detail($order_id)
	{
		$dingorder = D('Shopdingorder');
		$dingyuyue = D('Shopdingyuyue');
		$dingmenu = D('Shopdingmenu');
		if(!$order = $dingorder->where('order_id = '.$order_id)->find()){
			$this->baoError('该订单不存在');
		}else if(!$yuyue = $dingyuyue->where('ding_id = '.$order['ding_id'])->find()){
			$this->baoError('该订单不存在');
		}else if($yuyue['user_id'] != $this->uid){
			$this->error('非法操作');
		}else{
			$arr = $dingorder->get_detail($this->shop_id,$order,$yuyue);
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

        if(!$order = $dingorder->where('order_id = '.$detail['order_id'])->find()){
			$this->error('该订单不存在');
		}else if(!$yuyue = $dingyuyue->where('ding_id = '.$order['ding_id'])->find()){
			$this->error('该订单不存在');
		}else if($yuyue['user_id'] != $this->uid){
			$this->error('非法操作');
		}else{
			$arr = $dingorder->get_detail($yuyue['shop_id'],$order,$yuyue);
			$menu = $dingmenu->shop_menu($yuyue['shop_id']);
			$this->assign('yuyue', $yuyue);
			$this->assign('order', $order);
			$this->assign('order_id', $detail['order_id']);
			$this->assign('arr', $arr);
			$this->assign('menu', $menu);
			$this->assign('message',$message);
			$this->assign('paytype', D('Payment')->getPayments());
			 //====================微信支付通知===========================
			 $shop_ids   = D('Shop')->getFieldByShop_id($yuyue['shop_id']);
			 $map         = array('shop_id'=>array('in',$shop_ids));
           	 $shop_name  = D('Shop')->where($map)->getField('shop_name',true);
           	 $shop_name  = implode(',', $shop_name);
			 $addr  = D('Shop')->where($map)->getField('addr',true);
           	 $addr  = implode(',', $addr);

			
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $_data_pay = array(
                'url'       =>  "http://".$_SERVER['HTTP_HOST']."mcenter/ding/index.html",
                'topcolor'  =>  '#F55555',
                'first'     =>  '恭喜您，订单付款成功！',
                'remark'    =>  '更多信息,请登录http://'.$_SERVER['HTTP_HOST'].'再次感谢您的惠顾！',
                'money'     =>  $order['need_price'].'元',
                'orderInfo' =>  '在'.$shop_name.'预定了座位',
                'addr'      =>  '商家地址：'.$addr,
                'orderNum'  =>  '1-'.$order['order_id']
            );
            $pay_data = Wxmesg::pay($_data_pay);
            $return   = Wxmesg::net($this->uid, 'OPENTM202243342', $pay_data);

            //====================微信支付通知==============================
			$this->display('ding');
		
		}
    }

    protected function other_success($message, $detail) {
        //dump($detail);
        $tuanorder = D('Tuanorder')->find($detail['order_id']);
        if(!empty($tuanorder['branch_id'])){
            $branch = D('Shopbranch')->find($tuanorder['branch_id']);
            $addr = $branch['addr'];
        }else{
            $shop = D('Shop')->find($tuanorder['shop_id']);
            $addr = $shop['addr'];
        }
        $addr = D('Useraddr')->find($addr_id);//增加
        $this->assign('addr',$addr);
        $tuans = D('Tuan')->find($tuanorder['tuan_id']);
        $this->assign('tuans',$tuans);
        $this->assign('tuanorder',$tuanorder);
        $this->assign('message',$message);
        $this->assign('detail',$detail);
        $this->assign('paytype', D('Payment')->getPayments());
		 //====================微信支付通知===========================
			$code_ids  = D('Tuancode')->where("order_id=".$tuanorder['order_id'])->getField('code_id',true);
            $code_ids  = implode(',', $code_ids);
            $map          = array('code_id'=>array('in',$code_ids));
            $code = D('Tuancode')->where($map)->getField('code',true);
            $code = implode(',', $code);
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $_data_pay = array(
                'url'       =>  "http://".$_SERVER['HTTP_HOST']."mcenter/tuancode/index.html",
                'topcolor'  =>  '#F55555',
                'first'     =>  '亲,您的订单已支付完成,您的团购卷：'.$code.'！',
                'remark'    =>  '更多信息,请登录http://'.$_SERVER['HTTP_HOST'].'再次感谢您的惠顾！',
                'money'     =>  $tuanorder['total_price'].'元',
                'orderInfo' =>  $tuans['title'],
                'addr'      =>  '商家地址：'.$addr['addr'],
                'orderNum'  =>  '1-'.$tuanorder['order_id']
            );
            $pay_data = Wxmesg::pay($_data_pay);
            $return   = Wxmesg::net($this->uid, 'OPENTM202243342', $pay_data);

            //====================微信支付通知==============================
        $this->display('other');
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
    
    public function pay() {
        $member = D('Users')->find($this->uid);
       // var_dump(I('password'));die;
        $logs_id = think_decrypt(I('logs_id'));
        if (empty($logs_id)) {
            $this->error('没有有效的支付');
        }
      // if (!D('Lock')->lock($this->uid)) { //上锁
            //$this->error('服务器繁忙，1分钟后再试');
       // }
        if (!$detail = D('Paymentlogs')->find($logs_id)) {
          //  D('Lock')->unlock();
            $this->error('没有有效的支付');
        }
        if ($detail['code'] != 'money') {
          //  D('Lock')->unlock();
            $this->error('没有有效的支付');
        }       
        if ($detail['is_paid']) {
          //  D('Lock')->unlock();
            $this->error('没有有效的支付');
        }
        $verify = I('verify');
        if (session('pay_use_remain_code') != $verify){            
            $this->error('验证码错误');
        }
        $pin = I('pin','','md5');
        if (empty($member['pin'])) {
            $this->error('请设置支付密码',U('Pcucenter/info/zf','','',false,C('base_site')));
        }
        if ($member['pin'] != $pin) {
            $this->error('支付密码错误');
        }
            
        if ($member['money'] < $detail['money']) {
           // D('Lock')->unlock();
            $this->error('很抱歉您的账户余额不足', U('Pcucenter/payment/index','','html',false,C('BASE_SITE')));
        }
        if ($detail['type'] == 'chaoshi'){
            $chaoshi_product_model = D('Chaoshiproduct');
            $orderproduct = D('Chaoshiorderproduct')->where(array('order_id'=>$detail['order_id']))->select();
            foreach ($orderproduct as $k => $v){
                $product = $chaoshi_product_model->find($v['product_id']);
                if ($product['inventory'] < $v['num']){
                    $this->error('有商品库存不足，请重新购买', U('chaoshi/index'));
                    exit();
                }
                if ($product['is_out'] == 1){
                    $this->error('有商品已下架，请重新购买', U('chaoshi/index'));
                    exit();
                }
            }
        }
        $model = M();
        $model->startTrans();
        $money_res = D('Users')->where(array('user_id' => $this->uid))->setDec('money',$detail['money']);
        $pay_res = D('Payment')->logsPaid($logs_id);
        if($money_res && $pay_res){
            $model->commit();
            session('pay_use_remain_code' , null);//清除验证码
           // D('Lock')->unlock();
            if ($detail['type'] == 'ele') {
                $this->ele_success('恭喜您支付成功啦！', $detail);
            }elseif ($detail['type'] == 'ding') {
                $this->ding_success('恭喜您支付成功啦！', $detail);
            } elseif ($detail['type'] == 'goods') {
                $this->goods_success('恭喜您支付成功啦！', $detail);
            } elseif ($detail['type'] == 'chaoshi') {                   
                $order_info = D('Chaoshiorder')->relation('chaoshi_info')->find($detail['order_id']);
                //var_dump($order_info);die;
                D('Sms')->sendSms('chaoshi_order_pay',$order_info['chaoshi_info']['con_phone'],array('chaoshi_name'=>$order_info['chaoshi_info']['store_name']));
                $this->chaoshi_success('恭喜您支付成功啦！', $detail);
            } elseif ($detail['type'] == 'hotel') {
                $this->hotel_success('恭喜您支付成功啦！', $detail);
            } elseif ($detail['type'] == 'meishi') {
                $this->meishi_success('恭喜您支付成功啦！', $detail);
            } elseif($detail['type'] == 'gold' ||$detail['type'] == 'money'){
                $this->success('恭喜您充值成功',U('Pcucenter/payment/index','','html',false,C('BASE_SITE')));die();
            } else {
                $this->other_success('恭喜您支付成功啦！', $detail);
            }
        }else {
            $model->rollback();
            $this->error('支付失败！');
        }  
        
    }


}
