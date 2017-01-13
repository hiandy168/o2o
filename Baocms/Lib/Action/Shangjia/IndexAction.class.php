<?php



class IndexAction extends CommonAction {
	public function _initialize(){
		parent::_initialize();
	}

	public function index() {
		$this->my_store = D('MyHaveStore')->where(array('uid'=>$this->uid))->getField('sc_id',true);
		//获得我拥有的所有超市店铺
		$this->chaoshi_list = D('Chaoshi')->where(array('shop_id'=>$this->shop['shop_id'], 'closed'=>0))->field('store_id,store_name,shop_id,level,audit')->select();
		//获得我拥有的所有外卖店铺
		$this->waimai_list = D('Ele')->where(array('shop_id'=>$this->shop['shop_id'], 'closed'=>0))->field('store_id,store_name,shop_id,level,audit')->select();
		//var_dump($this->waimai_list);
		//获得我拥有的所有酒店铺
		$this->hotel_list = D('Hotel')->where(array('shop_id'=>$this->shop['shop_id'], 'closed'=>0))->field('store_id,store_name,shop_id,level,audit')->select();
		//获得我拥有的所有房产店铺
		$this->housestore_list = D('HouseStore')->where(array('shop_id'=>$this->shop['shop_id'], 'closed'=>0))->field('store_id,store_name,shop_id,level')->select();
		//获得我拥有的所有美食店铺
		$this->meishi_list = D('Meishi')->where(array('shop_id'=>$this->shop['shop_id'],'closed'=>0))->field('store_id,store_name,shop_id,shop_id,level,audit')->select();

		$this->display();
	}

	public function main() {
		$counts = array();
		$bg_time = strtotime(TODAY);
		$counts['totay_order'] = (int) D('Order')->where(array(
                    'shop_id' => $this->shop_id,
                    'create_time' => array(
		array('ELT', NOW_TIME),
		array('EGT', $bg_time),
		), 'status' => array(
                        'EGT', 0
		),
		))->count();
		$counts['order'] = (int) D('Order')->where(array(
                    'shop_id' => $this->shop_id,
                    'status' => array(
                        'EGT', 0
		),
		))->count();

		$counts['today_yuyue'] = (int) D('Shopyuyue')->where(array(
                    'shop_id' => $this->shop_id,
                    'create_time' => array(
		array('ELT', NOW_TIME),
		array('EGT', $bg_time),
		)))->count();
		$counts['yuyue'] = (int) D('Shopyuyue')->where(array(
                    'shop_id' => $this->shop_id,
                    'create_time' => array(
		array('ELT', NOW_TIME),
		array('EGT', $bg_time),
		)))->count();


		$counts['today_coupon'] = (int) D('Coupondownload')->where(array(
                    'shop_id' => $this->shop_id,
                    'create_time' => array(
		array('ELT', NOW_TIME),
		array('EGT', $bg_time),
		)))->count();
		$counts['coupon'] = (int) D('Coupondownload')->where(array(
                    'shop_id' => $this->shop_id,
		))->count();
		$counts['dianping'] = (int) D('Shopdianping')->where(array(
                    'shop_id' => $this->shop_id,
		))->count();

		$counts['orderby'] = (int) D('Shop')->where(array(
                    'ranking' => array(
                        'EGT', $this->shop['ranking']
		)
		))->count();

		$this->assign('counts', $counts);

		/* 统计抢购 */
		$bg_date = date('Y-m-d', NOW_TIME - 86400 * 6);
		$end_date = TODAY;
		$bg_time = strtotime($bg_date);
		$end_time = strtotime($end_date);
		$this->assign('bg_date', $bg_date);
		$this->assign('end_date', $end_date);
		$this->assign('money', D('Tuanorder')->money($bg_time, $end_time, $this->shop_id));
		$this->assign('ordermoney', D('Order')->money($bg_time, $end_time, $this->shop_id));
		$this->assign('shopmoney', D('Shopmoney')->money($bg_time, $end_time, $this->shop_id));
		$this->display();
	}

}
