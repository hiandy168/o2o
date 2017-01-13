<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiAction extends CommonAction {

	private $create_fields = array('contacts','card','con_phone','email','seok','begin_time','shut_time','corporate','licence', 'other', 'scope','store_name','city_id','area_id','lng','lat','shop_id', 'distribution', 'is_open', 'start_time', 'end_time', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money','discount_money', 'logistics', 'since_money', 'intro','identity_pic','license_pic','distance','logo','phone','address');
	private $edit_fields = array('contacts','card','con_phone','email','seok','begin_time','shut_time','corporate','licence', 'other', 'scope','store_name','city_id','area_id','lng','lat', 'distribution', 'is_open', 'is_pay', 'start_time', 'end_time', 'is_fan', 'fan_money', 'is_new','license_pic', 'full_money', 'new_money','discount_money','logistics', 'since_money', 'intro','distance','logo','phone','address');
	protected $chaoshi;

	public function _initialize() {
		parent::_initialize();
        if(I('post.store_id',0)){
            $store_id = I('get.store_id',0);
            $where = array(
                'store_id' => $store_id,
                'closed' => 0,
                'audit' => 1
            );
            session('default_store_id',$store_id);
        }else if(I('get.store_id',0)){
            $store_id = I('get.store_id',0);
            $where = array(
                'store_id' => $store_id,
                'closed' => 0,
                'audit' => 1
            );
            session('default_store_id',$store_id);
        }else if(session('default_store_id')){
            $store_id = session('default_store_id');
            $where = array(
                'store_id' => $store_id,
                'closed' => 0,
                'audit' => 1
            );
        }else{
           $where = array('shop_id'=>$this->shop_id, 'is_default'=>1);
        }

        $this->chaoshi = D('Chaoshi')->where($where)->order('audit asc')->find();


		//        print_r(D('Chaoshi')->getLastSql());die;
		// $this->chaoshi = $this->chaoshilist;
		//var_dump($this->chaoshi);

		if (empty($this->chaoshi) && !in_array(ACTION_NAME,array('apply','setdefault'))) {
			$this->error('您还没有入驻超市频道');
		}
		if ($this->chaoshi['closed'] == 1 && !in_array(ACTION_NAME,array('setdefault'))) {
			$this->error('超市已被管理员删除！', U('index/main'));
		}
		if (!empty($this->chaoshi) && $this->chaoshi['audit'] == 0 && !in_array(ACTION_NAME,array('apply','xinxi','setdefault'))) {
			$this->error('亲，您的申请正在审核中！', U('chaoshi/xinxi'));
			//$this->redirect('chaoshi/xinxi');
		}
		if ($this->chaoshi['audit'] == 2 && !in_array(ACTION_NAME,array('apply','setdefault','xinxi'))) {
			$this->error('你的申请被拒绝，请修改申请内容！', U('chaoshi/xinxi'));
			//$this->redirect('chaoshi/xinxi');
		}
		//        if ($this->chaoshi['status']==1 && !in_array(ACTION_NAME,array('apply','setdefault','xinxi'))) {
		//            $this->error('您的超市整顿中请主动联系客服',U('index/main'));
		//        }
		$this->assign('chaoshi', $this->chaoshi);
	}

	//public function index() {
	// $this->display();
	// }

	public function open() {
		if(IS_POST){
			$is_open = (int)$_POST['data']['is_open'];

			// var_dump($this->chaoshi['store_id']);die;
			D('Chaoshi')->save(array(
            'store_id' => $this->chaoshi['store_id'],

            'is_open' => $is_open
			));
			//var_dump(D('Chaoshi')->getLastSql());die;
			$this->baoSuccess('操作成功！', U('chaoshi/edit'));

		}
		$data = D('Chaoshi');
		$store_id = $this->chaoshi['store_id'];
		$date = $data->where('store_id='.$store_id)->find();
		//print_r($date);
		$this->is_open = $data->is_open;
		$this->display();
	}



	public function apply() {
		if ($this->isPost()) {
			$obj = D('Chaoshi');
			$data = $this->applyCheck();
			$cate = $this->_post('cate', false);
			$cate = implode(',', $cate);
			$data['cate'] = $cate;
			if ($this->chaoshi['audit'] == 2){                                                    //判断是否为修改被拒绝的申请
				$data['store_id'] = $this->chaoshi['store_id'];
				$data['audit'] = 0;

				if ($obj->save($data)) {
					$this->baoSuccess('申请成功', U('chaoshi/edit'));
				}
			}else {
				$data['begin_time'] = time();   //超市注册的时间
				$data['shut_time'] = time()+365*86400;
				if ($obj->add($data)) {

					// print_r($obj->getLastSql());
					//die;
					$this->baoSuccess('申请成功', U('chaoshi/edit'));
				}
			}
			$this->baoError('操作失败！');
		} else {
			$this->assign("area", D("Area")->fetchAll());
			$this->assign("city", $citys = D("City")->fetchAll());

			if ($this->chaoshi){
				$this->assign('detail',$this->chaoshi);
			}
			$this->display();
		}
	}

	private function applyCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['shop_id'] = $this->shop_id;
		 
		return $data;
	}

	public function edit() {
		if ($this->isPost()) {
			$obj = D('Chaoshi');
			$data = $this->editCheck();

			$Sensitive_mod = D('SensitiveWords');
			$data['store_name'] = $Sensitive_mod->filter($data['store_name']);			
			$data['intro'] = $Sensitive_mod->filter($data['intro']);			
			
			$cate = $this->_post('cate', false);
			$cate = implode(',', $cate);
			$data['cate'] = $cate;
			$data['store_id'] = $this->chaoshi['store_id'];
			if(!$data['store_id']){

				if ($obj->add($data)) {
					$this->baoSuccess('修改成功', U('chaoshi/edit'));
				}
			}else{

				if ($obj->save($data)) {
					$this->baoSuccess('修改成功', U('chaoshi/edit'));
				}
			}
			$this->baoError('操作失败！');
		}else {
			$this->assign("area", D("Area")->fetchAll());
			$this->assign("city", D("City")->fetchAll());
			if ($this->chaoshi){
				$this->assign('detail',$this->chaoshi);
			}
			$this->display();
		}
	}

	public function xinxi(){
		$this->exame_status = array('0'=>'待审核','1'=>'审核通过','2'=>'审核未通过');
		$this->assign('audit', $this->chaoshi['audit']);
		$this->assign('exame_explain', $this->chaoshi['exame_explain']);
		if($this->chaoshi['level']==1){
			$this->presonl_audit_info();
		}else{
			$this->com_audit_info();
		}

	}
	public function presonl_audit_info(){
		$this->detail = D('PresonalStoreOpenAuth')->find($this->shop['user_id']);
		$this->display('presonl_audit_info');
	}
	public function com_audit_info(){
		$detail = D('ComStoreOpenAuth')->where(array('store_id'=>$this->chaoshi['store_id'],'store_class_id'=>1))->find();
		$detail['other_pic'] = explode(",",$detail['other_pic']);
		$this->detail= $detail;
		//var_dump($this->detail);
		$this->display('com_audit_info');
	}

	public function xuqian(){
		$this->display();
	}

	private function editCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['store_id'] = $this->chaoshi['store_id'];
		if (empty($data['store_id'])) {
			$this->baoError('超市不存在');
		}
		if (!$shop = D('Shop')->find($this->chaoshi['shop_id'])) {
			$this->baoError('商家不存在');
		}
		if (!(int) $data['distance']){
			$this->baoError('配送距离必须是整数');
		}
		$data['distribution'] = (int) $data['distribution'];
		$data['distance'] = (int) $data['distance'];
		if (empty($data['intro'])) {
			$this->baoError('说明不能为空');
		}
		return $data;
	}

	private function xinxiCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['store_id'] = $this->chaoshi['store_id'];
		if (empty($data['store_id'])) {
			$this->baoError('超市不存在');
		}
		if (!$shop = D('Shop')->find($this->chaoshi['shop_id'])) {
			$this->baoError('商家不存在');
		}

		$data['distribution'] = (int) $data['distribution'];
		$data['distance'] = (int) $data['distance'];
		return $data;
	}
	//设置默认店铺
	public function setdefault($store_id,$shop_id){
		if(D('Chaoshi')->setdefault($store_id,$shop_id)){
			$this->redirect('edit');
		}else{
			$this->error("切换失败");
		}

	}

}
