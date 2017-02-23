<?php 

class ChaoshiAction extends CommonAction{
 const PAGE=10;

	public function index()
	{
		$_GET['p'] = I('post.p');
		$field = '*';
		$city_name = I('post.city_name');
		$map = array('closed' => 0, 'audit' => 1, 'status' => 0);
		$map['city_id'] = $this->get_city_id($city_name);
		$map['audit'] = 1;
		$map['closed'] = 0;
		$_GET['p'] = I('post.p');
		$lng = I('post.lng');
		$lat = I('post.lat');
		$order = I('post.order', '', 'trim');
		if ($lng && $lat) {
			import('ORG.Util.Page'); // 导入分页类
			$chaoshi_model = D('Chaoshi');
			$orderby = '';
			switch ($order) {
				case 'd':
					$orderby = array('since_money' => 'asc',
							'store_id' => 'desc'
					);
					break;
				case 'm':
					$orderby = array('distribution' => 'asc', 'store_id' => 'desc');
					break;
				case 's':
					$orderby = array('month_num' => 'desc', 'store_id' => 'desc');
					break;
				default:
					$orderby = array('month_num' => 'desc', 'store_id' => 'desc');
					break;
			}
			$full = I('post.full');
			if ($full == 1) {
				$map['discount_money'] = ['neq', 0];//减多少有优惠
			}
			if ($full == 2) {
				$map['is_pay'] = 1;//是否支持在线支付
			}
			if ($full == 3) {
				//print_r($is_new);
				$map['new_money'] = ['neq', 0];//是付支持新用户优惠
			}


			$field .= ',1000*ACOS(SIN((' . $lat . ' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS((' . $lat . ' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS((' . $lng . '* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 as mydistance';
			$map['distance'] = array('exp', '> `mydistance`');
			$count = $chaoshi_model->table("(select $field from " . $chaoshi_model->getTableName() . ") as temptable")->where($map)->order($orderby)->count();

			$Page = new Page($count, self::PAGE); // 实例化分页类 传入总记录数和每页显示的记录数
			//print_r($Page);

			$list = $chaoshi_model->table("(select $field from " . $chaoshi_model->getTableName() . ") as temptable")->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order($orderby)->select();

			foreach ($list as $key => $val) {
				$list[$key]['logo'] = get_remote_file_path($val['logo']);
			}
			if ($_GET['p'] < ceil($count / self::PAGE)) {
				$data['code'] = 200;

				$data['hasmore'] = true;
				$data['datas'] = $list;
				$data['page_total'] = ceil($count / self::PAGE);
				//print_r($data);
				$this->ajaxReturn($data);

			} else {

				$data['code'] = 200;
				$data['hasmore'] = false;
				$data['datas'] = $list;
				$data['page_total'] = ceil($count / self::PAGE);

				$this->ajaxReturn($data);
			}

		} else {

			$data['code'] = 400;
			$data['datas'] = '';
			$data['hasmore'] = false;
			$data['page_total'] = 0;
			$this->ajaxReturn($data);

		}
	}
     
    private function get_city_id($city_name) {
        $city_model = D('City');
        $map['name'] = array('like','%'.$city_name.'%');
        $city = $city_model->where($map)->find();
        return $city['city_id'];
    }
    
    public function city_id()
    {    
    	$city=I('post.city');
    	$lng=I('post.lng');
    	$lat=I('post.lat');
    	if(empty($city) || stristr($city, 'null') || empty($lng) || empty($lat)){
		    $this->ajaxReturn(array('code'=>400, 'datas'=>'', 'hasmore'=>false, 'page_total'=>0));
    	}
        $citys=M('city');
    	$dada=$citys->where('name="'.$city.'"')->find();
    
    	if($dada){    
	    	$data['code']=200;
	    	$data['datas']=$dada['city_id'];
	    	$data['hasmore']=false;
	    	$data['page_total']=0;
	    	$this->ajaxReturn($data);   
	    }else{
	    	$tap['lng']=$lng;
	    	$tap['lat']=$lat;
	    	$tap['pinyin']=get_pinyin($city);
	    	$tap['first_letter']=strtoupper(substr($tap['pinyin'],0,1));
	    	$tap['is_open']=1;
	    	$tap['name']=$city;
	    	if(!empty($city) && !stristr($city, 'null')&& !empty($lng) && !empty($lat)){		        
		    	$data['code']=1;
		    	$data['datas']=$citys->add($tap);
		    	$data['hasmore']=false;
		    	$data['page_total']=0;
		    	$this->ajaxReturn($data);	    	    
		    }else{	    
		    	$data['code']=400;
		    	$data['datas']='';
		    	$data['hasmore']=false;
		    	$data['page_total']=0;
		    	$this->ajaxReturn($data);   
		    }     
    	}          
   	}
}