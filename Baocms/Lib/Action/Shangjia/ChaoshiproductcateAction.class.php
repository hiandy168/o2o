<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiproductcateAction extends ChaoshiAction {

	private $create_fields = array('cate_name');
	private $edit_fields = array('cate_name');
	protected $chaoshi;

	public function _initialize() {
		parent::_initialize();

	}

	public function index() {
		$Chaoshicate_model = D('Chaoshiproductcate');
		import('ORG.Util.Page'); // 导入分页类
		$map = array('closed'=>'0');
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
			$map['cate_name'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
		}
		if ($store_id = $this->chaoshi['store_id']) {
			$map['store_id'] = $store_id;
			$chaoshi = D('Chaoshi')->find($store_id);
			$this->assign('store_name', $chaoshi['store_name']);
			$this->assign('store_id', $store_id);
		}
		//        var_dump($this->chaoshi['store_id']);

		$Page = new Page($Chaoshicate_model->where($map)->count(), 25); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出
		$list = $Chaoshicate_model
		->where($map)
		->order(array('cate_id' => 'desc'))
		->limit($Page->firstRow . ',' . $Page->listRows)
		->select();

		$this->assign('list', $list); // 赋值数据集
		$this->assign('page', $show); // 赋值分页输出
		$this->display(); // 输出模板
	}

	public function create() {
		if (IS_POST) {
			$data = $this->createCheck();
			
			$Sensitive_mod = D('SensitiveWords');
			$data['cate_name'] = $Sensitive_mod->filter($data['cate_name']);
			
			$obj = D('Chaoshiproductcate');
			if ($obj->add($data)) {
				$this->baoSuccess('添加成功', U('chaoshiproductcate/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->display();
		}
	}

	private function createCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['store_id'] = $this->chaoshi['store_id'];
		$data['cate_name'] = htmlspecialchars($data['cate_name']);
		if (empty($data['cate_name'])) {
			$this->baoError('分类名称不能为空');
		}
		return $data;
	}

	public function edit($cate_id = 0) {
		if ($cate_id == (int) $cate_id) {
			$obj = D('Chaoshiproductcate');
			if (!$detail = $obj->find($cate_id)) {
				$this->error('请选择要编辑的菜单分类');
			}
			if ($detail['store_id'] != $this->chaoshi['store_id']) {
				$this->error('请不要操作其他商家的菜单分类');
			}
			if ($this->isPost()) {
				$data = $this->editCheck();
				
				$Sensitive_mod = D('SensitiveWords');
				$data['cate_name'] = $Sensitive_mod->filter($data['cate_name']);
				
				$data['cate_id'] = $cate_id;
				if (false !== $obj->save($data)) {
					$this->baoSuccess('操作成功', U('chaoshiproductcate/index'));
				}
				$this->baoError('操作失败');
			} else {
				$this->assign('detail', $detail);
				$this->display();
			}
		} else {
			$this->error('请选择要编辑的菜单分类');
		}
	}

	private function editCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['cate_name'] = htmlspecialchars($data['cate_name']);
		if (empty($data['cate_name'])) {
			$this->baoError('分类名称不能为空');
		}
		return $data;
	}

	public function delete($cate_id = 0) {
		if (!(is_numeric($cate_id) && ($cate_id == (int) $cate_id))) {
			$this->baoError('请选择要删除的菜单分类');
		}
		$obj = D('Chaoshiproductcate');
		if (!$detail = $obj->where(array('shop_id' => $this->shop_id, 'cate_id' => $cate_id))->find()) {
			$this->baoError('请选择要删除的菜单分类');
		}

		// 验证该分类下是否有商品
		$store_id = $this->chaoshi['store_id'];
		if (!(is_numeric($store_id) && ($store_id == (int) $store_id))) {
			$this->baoError('错误的商家ID');
		}

		// 验证该分类下是否存在商品
		if(M('Chaoshi_product')->where(array('store_id' => $store_id, 'cate_id' => $cate_id, 'closed' => 0, 'audit' => array('in', array(0, 1))))->find()){
			$this->baoError('该分类下有可用商品，不能被删除');
		}

		$obj->save(array('cate_id' => $cate_id, 'closed' => 1));
		$this->baoSuccess('删除成功！', U('chaoshiproductcate/index'));

	}

}
