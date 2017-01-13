<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiproductAction extends ChaoshiAction {

	private $create_fields = array('product_name','desc','cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'create_time', 'create_ip','inventory','product_num');
	private $edit_fields = array('product_name', 'desc','cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian','inventory','product_num');
	protected $map = array();

	public function _initialize() {
		parent::_initialize();


		$chaoshiproductcates = D('Chaoshiproductcate')->getProductCate($this->chaoshi['store_id']);
		//var_dump($chaoshiproductcates);
		$this->assign('chaoshiproductcates', $chaoshiproductcates);
	}

	public function index() {
		$this->map['audit'] = 0;
		$this->showdata();
		//所有商品分类
		$Chaoshicate_model = D('Chaoshiproductcate');

		$map = array('closed'=>'0');

		if ($store_id = $this->chaoshi['store_id']) {
			$map['store_id'] = $store_id;
			$chaoshi = D('Chaoshi')->find($store_id);
			$this->assign('store_name', $chaoshi['store_name']);
			$this->assign('store_id', $store_id);
		}
		$list = $Chaoshicate_model->where($map)->order(array('cate_id' => 'desc'))->select();
		$this->assign('listt', $list); // 赋值数据集
		$this->display('index'); // 输出模板

	}

	public function auditing() {
		$this->map['audit']=1;
		$this->map['is_out'] = 0;
		$Chaoshicate_model = D('Chaoshiproductcate');

		$map = array('closed'=>'0');
		if ($store_id = $this->chaoshi['store_id']) {
			$map['store_id'] = $store_id;
			$chaoshi = D('Chaoshi')->find($store_id);
			$this->assign('store_name', $chaoshi['store_name']);
			$this->assign('store_id', $store_id);
		}

		$list = $Chaoshicate_model->where($map)->order(array('cate_id' => 'desc'))->select();
		$this->assign('listt', $list); // 赋值数据集
		$this->showdata();

		$this->display('auditing'); // 输出模板
	}

	public function outed() {

		$this->map['is_out'] = 1;
		$this->map['audit'] =array('neq',0);
		$Chaoshicate_model = D('Chaoshiproductcate');

		$map = array('closed'=>'0');

		if ($store_id = $this->chaoshi['store_id']) {
			$map['store_id'] = $store_id;
			$chaoshi = D('Chaoshi')->find($store_id);
			$this->assign('store_name', $chaoshi['store_name']);
			$this->assign('store_id', $store_id);
		}




		$list = $Chaoshicate_model->where($map)->order(array('cate_id' => 'desc'))->select();

		$this->assign('listt', $list); // 赋值数据集
		$this->showdata();

		$this->display('outed'); // 输出模板








	}

	public function create() {

		if ($this->isPost()) {

			$data = $this->createCheck();
			$obj = D('Chaoshiproduct');
			/*敏感词过滤*/
			$Sensitive_mod = D('SensitiveWords');
			$data['product_name'] = $Sensitive_mod->filter($data['product_name']);
			$data['product_num'] = $Sensitive_mod->filter($data['product_num']);
			$data['desc'] = $Sensitive_mod->filter($data['desc']);

			if (!$obj->autoCheckToken($_POST)){
				$this->baoError("请勿重复提交！");
			}
			if ($obj->add($data)) {
				$this->baoSuccess('添加成功', U('chaoshiproduct/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->display();
		}
	}

	private function createCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['desc'] = htmlspecialchars($data['desc']);
		if (empty($data['product_name'])) {
			$this->baoError('商品名不能为空');
		}
		$data['product_name'] = htmlspecialchars($data['product_name']);
		$data['store_id'] = $this->chaoshi['store_id'];
		$data['cate_id'] = (int) $data['cate_id'];
		if (empty($data['cate_id'])) {
			$this->baoError('分类不能为空');
		}
		$data['photo'] = htmlspecialchars($data['photo']);
		if (empty($data['photo'])) {
			$this->baoError('请上传缩略图');
		}
		$data['price'] = $data['price'];
		if (empty($data['price'])) {
			$this->baoError('价格不能为空');
		}
		$data['inventory'] = (int) $data['inventory'];
		if (empty($data['inventory'])) {
			$this->baoError('请填写正确的库存');
		}
		$data['create_time'] = NOW_TIME;
		$data['create_ip'] = get_client_ip();
		$data['audit'] = 0;
		return $data;
	}
	public function edit($product_id = 0) {
		if ($product_id = (int) $product_id) {
			$obj = D('Chaoshiproduct');
			if (!$detail = $obj->find($product_id)) {
				$this->baoError('请选择要编辑的商品');
			}
			if($detail['store_id'] != $this->chaoshi['store_id']){
				$this->baoError('请不要操作其他商家的菜单管理');
			}
			if ($this->isPost()) {
				$data = $this->editCheck();
				
				/*敏感词过滤*/
				$Sensitive_mod = D('SensitiveWords');
				$data['product_name'] = $Sensitive_mod->filter($data['product_name']);
				$data['product_num'] = $Sensitive_mod->filter($data['product_num']);
				$data['desc'] = $Sensitive_mod->filter($data['desc']);
				
				$data['product_id'] = $product_id;
				$data['is_out']=1;
				$data['audit']=0;
				if (false !== $obj->save($data)) {
					D('ChaoshiCart')->clearByProductId($product_id);
					$this->baoSuccess('操作成功', U('chaoshiproduct/index'));
				}
				$this->baoError('操作失败');
			} else {
				$this->assign('detail', $detail);

				$this->display();
			}
		} else {
			$this->baoError('请选择要编辑的商品');
		}
	}

	private function editCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['product_name'] = htmlspecialchars($data['product_name']);
		if (empty($data['product_name'])) {
			$this->baoError('商品名不能为空');
		}
		$data['desc'] = htmlspecialchars($data['desc']);
		$data['cate_id'] = (int) $data['cate_id'];
		if (empty($data['cate_id'])) {
			$this->baoError('分类不能为空');
		} $data['photo'] = htmlspecialchars($data['photo']);
		if (empty($data['photo'])) {
			$this->baoError('请上传缩略图');
		}
		/* if (!isImage($data['photo'])) {
		 $this->baoError('缩略图格式不正确');
		 } */
		$data['price'] = $data['price'];
		if (empty($data['price'])) {
			$this->baoError('价格不能为空');
		}
		$data['inventory'] = (int) $data['inventory'];
		if (empty($data['inventory'])) {
			$this->baoError('请填写正确的库存');
		}
		return $data;
	}

	public function delete($product_id = 0) {
		if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
			$obj = D('Chaoshiproduct');
			if(!$detail = $obj->where(array('store_id'=>$this->chaoshi['store_id'],'product_id'=>$product_id))->find()){
				$this->baoError('请选择要删除的商品');
			}
			$obj->save(array('product_id' => $product_id, 'closed' => 1));
			D('ChaoshiCart')->clearByProductId($product_id);
			$this->baoSuccess('删除成功！', U('chaoshiproduct/index'));
		}
		$this->baoError('请选择要删除商品');
	}
	/**
	 * 下架
	 */
	public function out() {
		$product_id = I('product_id','0','intval');
		if ($product_id) {
			$obj = D('Chaoshiproduct');
			if (!$detail = $obj->where(array('store_id'=>$this->chaoshi['store_id'],'product_id'=>$product_id))->find()){
				$this->baoError('请选择要下架的商品');
			}
			if ($obj->save(array('product_id' => $product_id, 'is_out' => 1))){
				D('ChaoshiCart')->clearByProductId($product_id);
				$this->baoSuccess('下架成功！', U('chaoshiproduct/index'));
			}else {
				$this->baoError('下架失败！');
			}
		}
		$this->baoError('请选择要下架商品');
	}
	/**
	 * 上架
	 */
	public function putaway() {
		$product_id = I('product_id','0','intval');
		if ($product_id) {
			$obj = D('Chaoshiproduct');
			if (!$detail = $obj->where(array('store_id'=>$this->chaoshi['store_id'],'product_id'=>$product_id))->find()){
				$this->baoError('请选择要下架的商品');
			}
			if ($obj->save(array('product_id' => $product_id, 'is_out' => 0))){
				$this->baoSuccess('上架成功！', U('chaoshiproduct/outed'));
			}else {
				$this->baoError('下架失败！');
			}
		}
		$this->baoError('请选择要下架商品');
	}

	private function showdata() {
		$Chaoshiproduct = D('Chaoshiproduct');
		import('ORG.Util.Page'); // 导入分页类
		$this->map['closed'] = 0;
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
			$this->map['product_name'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
		}

		if ($tpl = I('audit')) {
			$this->map['audit'] = $tpl;
			$this->assign('examine', $tpl);
		}

		if ($caste = I('cate_id')) {
			$this->map['cate_id'] = $caste;
			$this->assign('cate_id', $caste);
		}

		if ($store_id = $this->chaoshi['store_id']) {
			$this->map['store_id'] = $store_id;
			$this->assign('store_id', $store_id);
		}
		$count = $Chaoshiproduct->where($this->map)->count(); // 查询满足要求的总记录数
		$Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出
		$list = $Chaoshiproduct->where($this->map)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$cate_ids= array();
		foreach ($list as $k => $val) {
			if($val['cate_id']){
				$cate_ids[] = $val['cate_id'];
			}
		}
		if($cate_ids){
			$this->assign('cates',D('Chaoshiproductcate')->itemsByIds($cate_ids));
		}
		// dump($list);
		$this->assign('list', $list); // 赋值数据集
		$this->assign('page', $show); // 赋值分页输出
	}

	public function shanchu(){
		$obj = D('Chaoshiproduct');
		$tpl=I('product_id');
		if($tpl)
		{
			$where['product_id']=array('in',$tpl);
			$obj->where($where)->save(array('closed'=>1));
			foreach($tpl as $v){
				D('ChaoshiCart')->clearByProductId($v);
			}
			$this->baoSuccess('删除成功！',U('chaoshiproduct/index'));
		}else{
			$this->baoError('请选择商品！');
		}


	}
	public function xiajia(){
		$tpl=I('product_id');
		if($tpl){
			$obj = D('Chaoshiproduct');
			$where['product_id']=array('in',$tpl);
			$where['is_out']=1;

			$dada= $obj->save($where);
			if($dada){
				foreach($tpl as $v){
					D('ChaoshiCart')->clearByProductId($v);
				}
				$this->baoSuccess('下架成功！', U('chaoshiproduct/index'));

			}
		}else{
			$this->baoError('请选择要下架商品');
		}
	}

	public function shangjia(){
		$tpl=I('product_id','intval');
		if($tpl){
			$obj = D('Chaoshiproduct');
			$where['product_id']=array('in',$tpl);
			$where['is_out']=0;
			$where['product_time']=time();

			if($where['product_id']){
				$dada['product_id']=$where['product_id'];
				$dada['audit']=3;
				if($obj->where($dada)->find()){
					$this->baoError('勾选的商品审核未通过，不能上架');
				}else{

					$dada= $obj->save($where);
					if($dada){
						$this->baoSuccess('上架成功！', U('chaoshiproduct/outed'));
					}else{
						$this->baoError('上架失败');
					}
				}
			}else{
				$this->baoError('请选择要上架的商品');
			}
		}else{
			$this->baoError('请选择要上架的商品');
		}

	}
	//修改价格和库存
	public function edit_price_inventory() {
		$chaoshi_product_model = D('ChaoshiProduct');
		$product_id = I('product_id','0','intval');
		$type = I('type','','trim');
		$val = I('val','0','floatval');
		$data = array();
		//因只能修改价格和库存，故作此判断。
		if (in_array($type, array('price','inventory'))){
			$data[$type] = $val;
		}
		if ($chaoshi_product_model->where(array('product_id'=>$product_id))->setField($data)) {
			$this->ajaxReturn(array('status'=>'success'));
		}else {
			$this->ajaxReturn(array('status'=>'error'));
		}
	}

}
