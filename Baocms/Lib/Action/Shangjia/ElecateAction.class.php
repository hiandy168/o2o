<?php



class ElecateAction extends CommonAction {

	private $create_fields = array('cate_name');
	private $edit_fields = array('cate_name');


	public function _initialize() {
		parent::_initialize();
		$getEleCate = D('Ele')->getEleCate();
		$this->assign('getEleCate', $getEleCate);
		$this->store_id = D('Ele')->where(array('shop_id'=>$this->shop_id,'is_default'=>1))->getField('store_id'); //获得当前店铺ID

		$this->ele = D('Ele')->find($this->store_id);
		if (!empty($this->ele) && $this->ele['audit'] == 0) {
			$this->error("亲，您的申请正在审核中！");
		}
		if (empty($this->ele) && ACTION_NAME != 'apply') {
			$this->error('您还没有入住外卖频道', U('ele/apply'));
		}
		$this->assign('ele', $this->ele);
	}

	public function index() {
		$Elecate = D('Elecate');
		import('ORG.Util.Page'); // 导入分页类
		$map = array('closed'=>'0');
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
			$map['cate_name'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
		}
		$map['store_id'] = $this->store_id;
		if ($store_id = $this->store_id) {


			$this->assign('store_name', $this->ele['store_name']);
			$this->assign('store_id', $store_id);
		}

		$count = $Elecate->where($map)->count(); // 查询满足要求的总记录数
		$Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出
		$list = $Elecate->where($map)->order(array('cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		 
		$store_ids = array();
		foreach ($list as $k => $val) {
			if ($val['store_id']) {
				$store_ids[$val['store_id']] = $val['store_id'];
			}
		}
		if ($store_ids) {
			$this->assign('shops', D('Shop')->itemsByIds($this->shop_id));
		}
		$this->assign('list', $list); // 赋值数据集
		$this->assign('page', $show); // 赋值分页输出
		$this->display(); // 输出模板
	}

	public function create() {
		if ($this->isPost()) {
			$data = $this->createCheck();
			$obj = D('Elecate');
			if ($obj->add($data)) {
				$this->baoSuccess('添加成功', U('elecate/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->display();
		}
	}

	private function createCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['store_id'] = $this->store_id;
		$data['cate_name'] = htmlspecialchars($data['cate_name']);
		if (empty($data['cate_name'])) {
			$this->baoError('分类名称不能为空');
		}
		return $data;
	}

	public function edit($cate_id = 0) {
		if ($cate_id = (int) $cate_id) {
			$obj = D('Elecate');
			if (!$detail = $obj->find($cate_id)) {
				$this->error('请选择要编辑的菜单分类');
			}
			if ($detail['store_id'] != $this->store_id) {
				$this->error('请不要操作其他商家的菜单分类');
			}
			if ($this->isPost()) {
				$data = $this->editCheck();
				$data['cate_id'] = $cate_id;
				if (false !== $obj->save($data)) {
					$this->baoSuccess('操作成功', U('elecate/index'));
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
		if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
			$obj = D('Elecate');
			if (!$detail = $obj->where(array('store_id' => $this->store_id, 'cate_id' => $cate_id))->find()) {
				$this->baoError('请选择要删除的菜单分类');
			}
			$obj->save(array('cate_id' => $cate_id, 'closed' => 1));
			$this->baoSuccess('删除成功！', U('elecate/index'));
		}
		$this->baoError('请选择要删除的菜单分类');
	}

}
