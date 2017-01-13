<?php



class EleAction extends CommonAction {

	private $create_fields = array('store_id','shop_id','store_name','area_id','city_id','business_id','lng','lat', 'distribution', 'is_open', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'orderby');
	protected $ele;

	public function _initialize() {
		parent::_initialize();
		$this->store_id = D('Ele')->where(array('shop_id'=>$this->shop_id,'is_default'=>1))->getField('store_id'); //获得当前店铺ID
		//var_dump($this->store_id);
		$getEleCate = D('Ele')->getEleCate();
		$this->assign('getEleCate', $getEleCate);
		$this->ele = D('Ele')->find($this->store_id);
		//print_r($this->ele);die;
		if (empty($this->ele) && ACTION_NAME != 'apply') {
			$this->error('您还没有入驻外卖频道', U('ele/apply'));
		}
		if (!empty($this->ele) && $this->ele['audit'] == 0) {
			$this->error("亲，您的申请正在审核中！");
		}
		 
		$this->assign('ele', $this->ele);
	}

	public function index() {
		$this->display();
	}

	public function open() {
		$is_open = (int) $_POST['is_open'];
		//$is_open = $is_open ? 1 : 0;
		D('Ele')->save(array(
            'store_id' => $this->store_id,
            'is_open' => $is_open
		));
		//dump(D('Ele')->getLastSql());
		$this->baoSuccess('操作成功！', U('ele/index'));
	}

	public function apply() {
		$this->assign("area", D("Area")->fetchAll());
		$this->assign("city", D("City")->fetchAll());

		if ($this->isPost()) {
			$data = $this->applyCheck();
			$obj = D('Ele');
			$cate = $this->_post('cate', false);
			$cate = implode(',', $cate);
			$data['cate'] = $cate;
			if ($obj->add($data)) {
				$this->baoSuccess('添加成功', U('ele/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->display();
		}
	}

	private function applyCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['is_open'] = (int) $data['is_open'];
		$data['is_pay'] = (int) $data['is_pay'];
		$data['is_fan'] = (int) $data['is_fan'];
		$data['is_new'] = (int) $data['is_new'];
		$data['distribution'] = (int) $data['distribution'];
		$data['intro'] = htmlspecialchars($data['intro']);
		$data['shop_id'] =$this->shop_id;
		if (empty($data['intro'])) {
			$this->baoError('说明不能为空');
		}
		return $data;
	}

	// update:remove begin
	//    /**peace
	//     * 外卖编辑信息
	//     */
	//    public function edit() {
	//        if ($this->isPost()) {
	//            $obj = D('Ele');
	//            $data = $this->editCheck();
	//
	//            $cate = $this->_post('cate', false);
	//            $cate = implode(',', $cate);
	//            $data['cate'] = $cate;
	//            $data['store_id'] = $this->chaoshi['store_id'];
	//            if(!$data['store_id']){
	//
	//                if ($obj->add($data)) {
	//                    $this->baoSuccess('修改成功', U('ele/edit'));
	//                }
	//            }else{
	//
	//                if ($obj->save($data)) {
	//                    $this->baoSuccess('修改成功', U('ele/edit'));
	//                }
	//            }
	//            $this->baoError('操作失败！');
	//        }else {
	//            $this->assign("area", D("Area")->fetchAll());
	//            $this->assign("city", D("City")->fetchAll());
	//            if ($this->ele){
	//                $this->assign('detail',$this->ele);
	//            }
	//            $this->display();
	//        }
	//    }
	// update:remove end

}
