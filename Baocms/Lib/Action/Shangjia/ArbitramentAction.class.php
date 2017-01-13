<?php

/**
 * Created by PhpStorm.
 * User: 曾和平
 * Date: 2016/10/18
 * Time: 19:52
 */
class ArbitramentAction extends CommonAction
{
	/**peace
	 * @var array
	 * 卖家中心申诉相关表单：bao_arbitrament, bao_arbitrament_pic,各板块的订单表的相关字段(`refund_status`, `pay_status`, `arbitrament_status`, `store_arbitrament_status`),
	 */

	// 订单类型  超市   外卖   美食   酒店   房产
	// array('1' => 'chaoshi', '2' => 'ele', '3' => 'meishi', '4' => 'hotel', '5' => 'house')
	// $order_type_detail与$order_type是一一对应关系，顺序不能改动，添加也要按顺序，
	// 订单的id均为
	// 若此处的信息更改，则对应的买家中心、移动端接口和大后台的信息也应更改
	private $order_type = array(1, 2, 3, 4, 5);
	private $order_type_detail = array(1 => 'Chaoshi_order', 2 => 'Ele_Order', 3 => 'Meishi_order', 4 => 'Hotel_order', 5 => 'House_order');

	/**peace
	 * 商家提交仲裁资料
	 * 获取对应的订单id，订单类型
	 * 使用一个测试的订单类型type 1
	 * 使用一个对应的测试的订单id 62
	 */
	public function index($order_type = 1, $order_id = 62){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$arbitramentModel = M('Arbitrament');
			// 检验id
			$id = $_POST['arbitrament_id'];
			$store_explain = $_POST['store_explain'];
			if(!(is_numeric($id) && $id == (int)$id)){
				$this->baoError('该仲裁ID有误');
			}

			$findArbitrament = $arbitramentModel
			->where(array('id' => $id))
			->find();
			if(!$findArbitrament){
				$this->baoError('未找到该仲裁信息');
			}

			// 仅保存商家陈述信息，开启事务
			M()->startTrans();
			$rst = $arbitramentModel
			->where(array('id' => $id))
			->save(array('store_explain' => $store_explain));
			if(!$rst){
				M()->rollback();
				$this->baoError('保存失败');
			}

			// 保存图片信息
			if(!isset($_POST['photos'])){
				$_POST['photos'] = array();
			}
			$photos = $_POST['photos'];
			if(!is_array($_POST['photos'])){
				M()->rollback();
				$this->baoError('图片数据错误');
			}
			if($photos){
				$arbitramentPicModel = M('Arbitrament_pic');
				foreach($photos as $photo){
					if(!($photo == (int)$photo)){
						M()->rollback();
						$this->baoError('保存失败');
					}
					$rst = $arbitramentPicModel
					->add(array('arbitrament_id' => $id, 'pic_id' => $photo, 'type' => 1));
					// 图片类型值卖家为1
					if(!$rst){
						M()->rollback();
						$this->baoError('仲裁图片保存失败');
					}
				}
			}

			// 更改订单状态，修改对应的订单表状态
			// `store_arbitrament_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商家响应仲裁状态0：未提交资料1已提交',
			$rst = M($this->order_type_detail[$findArbitrament['order_type']])
			->where(array('order_id' => $findArbitrament['order_id']))
			->save(array('store_arbitrament_status' => 1));
			if(!$rst){
				M()->rollback();
				$this->baoError('订单状态修改失败');
			}

			M()->commit();

			$this->redirect('chaoshiorder/index');

		}else{
			// 订单类型和订单验证
			$findOrder = $this->checkOrderInfo($order_type, $order_id);
			$findOrder['order_type'] = $order_type;

			// 查找买家昵称
			$findNickname = M('Arbitrament')
			->field('id,user_nickname')
			->where(array('user_id' => $findOrder['user_id']))
			->find();
			$findOrder['nickname'] = $findNickname['user_nickname'];
			//        var_dump($findOrder);

			// 订单id和订单类型，查找仲裁ID
			$findArbiId = M('Arbitrament')
			->where(array('order_type' => $order_type, 'order_id' => $order_id))
			->find();
			$findOrder['arbitrament_id'] = $findArbiId['id'];

			// 查找订单对应的商品详情
			$findOrderProducts = $this->orderProductDetail($order_type, $order_id);

			$this->assign('list', $findOrder);
			$this->assign('findOrderProducts', $findOrderProducts);
			$this->display();
		}

	}

	/**peace
	 * 验证订单类型和订单的合法性
	 * order_type, order_id
	 */
	protected function checkOrderInfo($order_type, $order_id){
		// 判定订单类型是否存在
		if(!($order_type == (int)$order_type)){
			return $this->baoError('订单类型错误');
		}
		if(!in_array($order_type, $this->order_type)){
			return $this->baoError('该订单类型不存在');
		}

		// 订单号合法性判断
		if(!($order_id == (int)$order_id)){
			return $this->baoError('订单错误');
		}
		// 查找改订单
		$findOrder = M($this->order_type_detail[$order_type])
		->where(array('order_id' => $order_id))
		->find();
		if(!$findOrder){
			return $this->baoError('未在对应版块找到订单');
		}
		return $findOrder;
	}

	/**peace
	 * 订单的商品详情
	 * @param $order_type
	 * @param $order_id
	 */
	protected function orderProductDetail($order_type, $order_id){
		if($order_type == 1){
			$orderTable = 'Chaoshi_order_product';
			$field = 'OP.*, bao_chaoshi_product.product_name';
			$join = 'LEFT JOIN bao_chaoshi_product ON bao_chaoshi_product.product_id=OP.product_id';
		}elseif ($order_type == 2){
			echo '2';
		}elseif ($order_type == 3){
			echo '3';
		}elseif ($order_type == 4){
			echo '4';
		}elseif ($order_type == 5){
			echo '5';
		}else{
			return $this->baoError('信息错误');
		}
		$findOrderProducts = M($orderTable)
		->alias('OP')
		->field($field)
		->join($join)
		->where(array('order_id' => $order_id))
		->select();
		if(!$findOrderProducts){
			return $this->baoError('查询错误');
		}

		return $findOrderProducts;
	}

	/**peace
	 * 仲裁结果详情显示
	 */
	public function detail(){
		$ArbitramentModel = M('Arbitrament');
		$order_type = $_GET['order_type'];
		$order_id = $_GET['order_id'];

		// // 订单类型和订单验证
		$findOrder = $this->checkOrderInfo($order_type, $order_id);

		// 判定存在性
		$findArbitrament = $ArbitramentModel
		->where(array('order_type' => $order_type, 'order_id' => $order_id))
		->find();
		if(!$findArbitrament){
			$this->baoError('未找到该仲裁ID');
		}

		// 买家昵称
		$findNickname = M('Users')
		->field('nickname')
		->where(array('user_id' => $findOrder['user_id']))
		->find();
		$findArbitrament['nickname'] = $findNickname['nickname'];

		// 查找订单对应的商品详情
		// 查找订单对应的商品详情
		$findOrderProducts = $this->orderProductDetail($findArbitrament['order_type'], $findArbitrament['order_id']);

		// 查找商家和买家上传的仲裁图片
		$findArbiPic = M('Arbitrament_pic')
		->where(array('arbitrament_id' => $findArbitrament['id']))
		->select();
		// 便利是否存在图片
		$storePicNum = 0;
		$userPicNum = 0;
		foreach($findArbiPic as $a){
			if($a['type'] == 1){
				++ $storePicNum;
			}elseif($a['type'] == 2){
				++ $userPicNum;
			}
		}

		// `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '处理状态1：未处理2：已处理',
		// 如果status是2，则显示详情；

		$this->assign('orderProducts', $findOrderProducts);
		$this->assign('list', $findArbitrament);
		$this->assign('order', $findOrder);
		$this->assign('arbiPic', $findArbiPic);
		$this->assign('storePicNum', $storePicNum);
		$this->assign('userPicNum', $userPicNum);
		$this->display();
	}

}