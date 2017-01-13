<?php

class PaymentModel extends CommonModel {

	protected $pk = 'payment_id';
	protected $tableName = 'payment';
	protected $token = 'payment';
	protected $types = array('goods' => '商城购物', 'gold' => '金块充值', 'tuan' => '生活购物', 'money' => '余额充值', 'ele' => '在线订餐', 'ding' => '在线订座', 'chaoshi'=>'超市购物','meishi'=>'美食预定','hotel'=>'酒店预订');
	protected $type = null;
	protected $log_id = null;


	public function getType() {
		return $this -> type;
	}

	public function getLogId() {
		return $this -> log_id;
	}

	public function getTypes() {
		return $this -> types;
	}

	public function getPayments($mobile = false) {
		$datas = $this -> fetchAll();
		$return = array();
		foreach ($datas as $val) {
			if ($val['is_open']) {
				if ($mobile == false) {
					if (!$val['is_mobile_only'])
						$return[$val['code']] = $val;
				} else {
					if ($val['code'] != 'tenpay') {
						$return[$val['code']] = $val;
					}
				}
			}
		}
// 		if (!is_weixin()) {
// 			unset($return['weixin']);
// 		}

// 		if (is_weixin()) {
// 			unset($return['alipay']);
// 		}

		return $return;
	}

	public function _format($data) {
		$data['setting'] = unserialize($data['setting']);
		return $data;
	}

	public function respond($code) {
		$payment = $this -> checkPayment($code);
		if (empty($payment))
			return false;
		if (defined('IN_MOBILE')) {
			require_cache(APP_PATH . 'Lib/Payment/' . $code . '.mobile.class.php');
		} else {
			require_cache(APP_PATH . 'Lib/Payment/' . $code . '.class.php');
		}
		$obj = new $code($payment['setting']);
		return $obj -> respond($payment['setting']);


	}

	public function getCode($logs) {
		$CONFIG = D('Setting') -> fetchAll();
		$datas = array('subject' => $CONFIG['site']['sitename'] . $this -> types[$logs['type']], 'logs_id' => think_encrypt($logs['log_id']), 'logs_amount' => $logs['money'], );
		$payment = $this -> getPayment($logs['code']);
		if (defined('IN_MOBILE')) {
			require_cache(APP_PATH . 'Lib/Payment/' . $logs['code'] . '.mobile.class.php');
		} else {
			require_cache(APP_PATH . 'Lib/Payment/' . $logs['code'] . '.class.php');
		}
		$obj = new $logs['code']();
		return $obj -> getCode($datas, $payment);
	}

	public function checkMoney($logs_id, $money) {
		$logs = D('Paymentlogs') -> find($logs_id);
		if ($logs['money'] == $money)
			return true;
		return false;
	}

	public function logsPaid($logs_id) {
		$this -> log_id = $logs_id;
		//用于外层回调
		$payment_logs_model = D('Paymentlogs');		
		$logs = $payment_logs_model -> find($logs_id);
		if (!empty($logs) && !$logs['is_paid']) {
			$data = array('log_id' => $logs_id, 'is_paid' => 1, );
			if ( $payment_logs_model -> save($data)) {//总之 先更新 然后再处理逻辑  这里保障并发是安全的		
			    $ip = get_client_ip();
			    $user_money_logs_model = D('Usermoneylogs');
				$payment_logs_model -> save(array('log_id' => $logs_id, 'pay_time' => NOW_TIME, 'pay_ip' => $ip));
				$this -> type = $logs['type'];
				if ($logs['type'] == 'ele') {//餐饮订餐
				    $product_model = D('Eleproduct');
				    $order_model = D('Eleorder');
				    $sms_model = D('Sms');
					switch ($logs['code']) {
						case 'alipay':
							$pay_type = 2;
							break;
						case 'weixin':
							$pay_type = 3;
							break;
						case 'money':
							$pay_type = 4;
							break;
					}					
					$order_res = $order_model -> save(array('order_id' => $logs['order_id'], 'status' => 1,'pay_status' => 1,'pay_type'=>$pay_type));
					$order = $order_model -> where('order_id =' . $logs['order_id']) -> find();
					$member = D('Users') -> find($logs['user_id']);
					$store = D('Ele') -> find($order['store_id']);
					
					$orderproduct = D('Eleorderproduct') -> where('order_id =' . $logs['order_id']) -> select();
			
					foreach ($orderproduct as $k => $v) {
					    $product_model -> where(array('product_id'=>$v['product_id']))->setInc('sold_num', $v['num']);
					    
					    $product_model -> where(array('product_id'=>$v['product_id']))->setDec('inventory', $v['num']);
					}

					if ($order_res){
					    $sms_model -> sendSms('sms_ele', $member['mobile'], array('nickname' => $member['nickname'], 'shopname' => $store['store_name'], ));
					    $sms_model -> eleTZshop($logs['order_id']);
					}else {
					    return false;
					}
				} elseif ($logs['type'] == 'chaoshi') {//超市购物
				    $chaoshi_product_model = D('Chaoshiproduct');
				    $chaoshi_order_model = D('Chaoshiorder');
				    $chaoshi_model = D('Chaoshi');

				    switch ($logs['code']) {
				        case 'alipay':
				            $pay_type = 2;
				            break;
				        case 'weixin':
				            $pay_type = 3;
				            break;
				        case 'money':
				            $pay_type = 4;
				            break;
				    }

					$order_res = $chaoshi_order_model -> save(array('order_id' => $logs['order_id'], 'status' => 1, 'pay_status' => 1, 'pay_type' => $pay_type));
					if (!$order_res){
					    return false;
					}
					$orderproduct = D('Chaoshiorderproduct') -> where('order_id =' . $logs['order_id']) -> select();
					foreach ($orderproduct as $k => $v) {
					    $chaoshi_product_model -> updateCount($v['product_id'], 'sold_num', $v['num']);
					    $chaoshi_product_model -> updateCount($v['product_id'], 'inventory', -$v['num']);
					}						
					$user_money_logs_model->add(array(
					    'user_id' => $logs['user_id'],
					    'type' => 2,
					    'order_type' => 1,
					    'money' => -$logs['money'],
					    'create_time' => NOW_TIME,
					    'create_ip' => get_client_ip(),
					    'intro' => '在线支付超市订单',
					));
					$order = $chaoshi_order_model->find($logs['order_id']);
					$chaoshi = $chaoshi_model->find($order['store_id']);					
					D('Sms')->sendSms('chaoshi_new_order', $chaoshi['phone']);
					return true;
				} elseif ($logs['type'] == 'meishi') {//美食预定
				    $meishi_order_model = D('MeishiOrder');
				    $meishi_goods_model = D('MeishiGoods');
					$meishi_order_model->save(array('order_id' => $logs['order_id'], 'status' => 1));
					$order_info = $meishi_order_model->relation(true)->find($logs['order_id']);
					$meishi_goods_model->where(array('goods_id'=>$order_info['goods_id']))->setInc('sale_num');
					D('Sms')->sendSms('meishi_sms', $order_info['phone'], array('code' => $order_info['code'], 'nickname' => $order_info['user_info']['nickname'], 'meishi' => $order_info['goods_info']['goods_name']));
					return true;
				}
			}
		}

		return true;
	}

	//更新商城销售接口
	public function mallSold($order_ids) {
		if (is_array($order_ids)) {
			$order_ids = join(',', $order_ids);
			$ordergoods = D('Ordergoods') -> where("order_id IN ({$order_ids})") -> select();
			foreach ($ordergoods as $k => $v) {
				D('Goods') -> updateCount($v['goods_id'], 'sold_num', $v['num']);
        		D('Goods') -> updateCount($v['goods_id'], 'num', -$v['num']);
			}
		} else {
			$order_ids = (int)$order_ids;
			$ordergoods = D('Ordergoods') -> where('order_id =' . $order_ids) -> select();
			foreach ($ordergoods as $k => $v) {
				D('Goods') -> updateCount($v['goods_id'], 'sold_num', $v['num']);
				D('Goods') -> updateCount($v['goods_id'], 'num', -$v['num']);				
			}
		}
		return TRUE;
	}
	
	//更新超市销售接口
	public function chaoshiSold($order_ids) {
	    if (is_array($order_ids)) {
	        $order_ids = join(',', $order_ids);
	        $orderproduct = D('Chaoshiorderproduct') -> where("order_id IN ({$order_ids})") -> select();
	        foreach ($orderproduct as $k => $v) {
	            D('Chaoshiproduct') -> updateCount($v['product_id'], 'sold_num', $v['num']);
	            D('Chaoshiproduct') -> updateCount($v['product_id'], 'inventory', -$v['num']);
	        }
	    } else {
	        $order_ids = (int)$order_ids;
	        $orderproduct = D('Chaoshiorderproduct') -> where('order_id =' . $order_ids) -> select();
	        foreach ($orderproduct as $k => $v) {
	            D('Chaoshiproduct') -> updateCount($v['product_id'], 'sold_num', $v['num']);
	            D('Chaoshiproduct') -> updateCount($v['product_id'], 'inventory', -$v['num']);
	
	        }
	    }
	    return TRUE;
	}

	//配送接口
	public function mallPeisong($order_ids) {

		foreach ($order_ids as $order_id) {

			$order = D('Order') -> where('order_id =' . $order_id) -> find();

			$shops = D('Shop') -> find($order['shop_id']);
			if ($shops['is_pei'] == 0) {
				$member = D('Users') -> find($order['user_id']);
				$ua = D('UserAddr');
				$dv = D('DeliveryOrder');
				$uaddr = $ua -> where('user_id =' . $order['user_id']) -> find();

				//在线支付成功以后，进入配送员判断

				$dv_data = array('type' => 0, 'type_order_id' => $order['order_id'], 'delivery_id' => 0, 'shop_id' => $order['shop_id'], 'user_id' => $order['user_id'], 'shop_name' => $shops['shop_name'], 'shop_addr' => $shops['addr'], 'shop_mobile' => $shops['tel'], 'user_name' => $member['nickname'], 'user_addr' => $uaddr['addr'], 'user_mobile' => $member['mobile'], 'create_time' => time(), 'update_time' => 0, 'status' => 1);

				$dv -> add($dv_data);
			}

			//通知商家
			D('Sms') -> mallTZshop($logs['order_id']);
		}
		D('Tongji') -> log(2, $logs['need_pay']);
		//统计

		return true;
	}

	public function checkPayment($code) {
		$datas = $this -> fetchAll();
		foreach ($datas as $val) {
			if ($val['code'] == $code)
				return $val;
		}
		return array();
	}

	public function getPayment($code) {
		$datas = $this -> fetchAll();
		foreach ($datas as $val) {
			if ($val['code'] == $code)
				return $val['setting'];
		}
		return array();
	}
/**
 * 不同的类型对应不同的订单表
 * @return array
 */    
	public function getOrderClass() {
	    return array(	        
	        'chaoshi' => 'ChaoshiOrder',
	        'ele' => 'EleOrder',
	    );
	}
/**
 *不同的支付类型在订单表是用数字表示的
 * @return array
 */	
	public function getOrderPayTypes() {
	    return array(
	        'wait' => 1,
	        'alipay' => 2,
	        'weixin' => 3,
	        'money' => 4,
	    );
	}
}
