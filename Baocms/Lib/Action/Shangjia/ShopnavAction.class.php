<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class ShopnavAction extends CommonAction {
	private $create_fields = array('title', 'url', 'orderby');
	private $edit_fields = array('title', 'url', 'orderby');

	public function index() {
		$Shopnav_model = D('Shopnav');
		import('ORG.Util.Page'); // 导入分页类
		$map = array('shop_id' => $this->shop_id);

		$count = $Shopnav_model->where($map)->count(); // 查询满足要求的总记录数
		$Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出
		$list = $Shopnav_model->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list); // 赋值数据集
		$this->assign('page', $show); // 赋值分页输出
		$this->display(); // 输出模板
	}
	public function create() {
		if ($this->isPost()) {
			$data = $this->createCheck();
			$obj = D('Shopnav');
			if ($obj->add($data)) {
				$this->baoSuccess('添加成功', U('shopnav/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->display();
		}
	}

	private function createCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['title'] = htmlspecialchars($data['title']);
		if (empty($data['title'])) {
			$this->baoError('标题不能为空');
		}
		if ((int)$data['orderby']<0) {
			$this->baoError('排序不是正整数');
		}
		$data['shop_id'] = $this->shop_id;
		return $data;
	}

	public function edit($nav_id = 0) {
		if ($nav_id = (int) $nav_id) {
			$obj = D('Shopnav');
			if (!$detail = $obj->find($nav_id)) {
				$this->baoError('请选择要编辑的店铺导航');
			}
			if($detail['shop_id'] != $this->shop_id){
				$this->error('不可操作其他人的！');
			}

			if ($this->isPost()) {
				$data = $this->editCheck();
				$data['nav_id'] = $nav_id;
				if (false !== $obj->save($data)) {
					$this->baoSuccess('操作成功', U('shopnav/index'));
				}
				$this->baoError('操作失败');
			} else {
				$this->assign('detail', $detail);
				$this->display();
			}
		} else {
			$this->baoError('请选择要编辑的店铺导航');
		}
	}

	private function editCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['title'] = htmlspecialchars($data['title']);
		if (empty($data['title'])) {
			$this->baoError('标题不能为空');
		}
		if ((int)$data['orderby']<0) {
			$this->baoError('排序不是正整数');
		}
		return $data;
	}

	public function del($nav_id = 0) {
		if ($nav_id = (int) $nav_id) {
			$obj = D('Shopnav');
			if (!$detail = $obj->find($nav_id)) {
				$this->baoError('请选择要删除的店铺导航');
			}
			elseif($detail['shop_id'] != $this->shop_id){
				$this->error('不可操作其他人的！');
			}
			else{
				if (false !== $obj->delete($nav_id)) {
					$this->baoSuccess('删除成功', U('shopnav/index'));
				}
				$this->baoError('删除失败');
			}
		} else {
			$this->baoError('请选择要删除的店铺导航');
		}
	}
}