<?php


class IndexAction extends CommonAction {

      public function index() {
        $this->assign('lifecate', D('Lifecate')->fetchAll());
        $this->assign('channel', D('Lifecate')->getChannelMeans());
		
		//获取用户自定坐标
		$lat = cookie('lat_ok');
		$lng = cookie('lng_ok');
		if(empty($lat) || empty($lng)){
			$lat = cookie('lat');
			$lng = cookie('lng');
		}
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $shoplist = D('Shop')->where(array('closed' => 0, 'audit' => 1))->order($orderby)->limit(0, 6)->select();
        $couponlist = D('Coupon')->where(array('closed' => 0, 'audit' => 1, 'expire' => array('EGT', TODAY)))->order(array('downloads' => 'desc'))->limit(0, 6)->select();

		$this->assign('place', $place);
      
	  
		//p($huodong);
        $this->assign('shoplist', $shoplist);
        $this->assign('couponlist', $couponlist);
		
		
		//活动
        $huodong = D('Activity')->order('activity_id desc ')->where(array('city_id'=>$this->city_id, 'audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY), 'bg_date' => array('ELT', TODAY)))->limit(0, 6)->select();

        $this->assign('huodong', $huodong);

        $this->display();
    }
   

    public function search() {
        $keys = D('Keyword')->fetchAll();
        $keytype = D('Keyword')->getKeyType();
        $this->assign('keys',$keys);
        $this->display();
    }

public function more() {
        $this->display();
    }

    public function test(){
        $uid = I('uid');
        $name=I('name');
        $data[]=$uid;
        $data[]= $name;
        $this->success($data);
    }
    public function test1(){
        $this->display('test');
    }
}
