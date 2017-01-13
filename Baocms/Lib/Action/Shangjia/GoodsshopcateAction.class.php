<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class GoodsshopcateAction extends CommonAction {

	private $create_fields = array( 'cate_name', 'orderby', 'shop_id','parent_id');
	private $edit_fields = array( 'cate_name', 'orderby', 'shop_id');

	public function index() {
		$this->check_weidian();
		$Goodsshopcate = D('Goodsshopcate');
		$Goodsshopcate->orderby = array('orderby' => 'asc');
		$list = $Goodsshopcate->fetchAll($field = '*',array('shop_id' => $this->shop_id));
		$this->assign('list', $list); // 赋值数据集
		$this->display(); // 输出模板
	}

	private function check_weidian(){

		$wd = D('WeidianDetails');
		$wd_res = $wd->where('shop_id ='.($this->shop_id)) -> find();
		if(!$wd_res){
			$this->error('请先完善微店资料！',U('goods/weidian'));
		}elseif($wd_res['audit'] == 0){
			$this->error('您的微店正在审核中，请耐心等待！',U('index/index'));
		}elseif($wd_res['audit'] == 2){
			$this->error('您的微店未通过审核！',U('index/index'));
		}

	}

	public function create($parent_id=0) {
		if ($this->isPost()) {
			$data = $this->createCheck();
			$obj = D('Goodsshopcate');
			$data['parent_id'] = $parent_id;
			$data['shop_id'] = $this->shop_id;
			if ($obj->add($data)) {
				$obj->cleanCache();
				$this->baoSuccess('添加成功', U('goodsshopcate/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->assign('parent_id',$parent_id);
			$this->display();
		}
	}

	private function createCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['cate_name'] = htmlspecialchars($data['cate_name']);
		if (empty($data['cate_name'])) {
			$this->baoError('分类不能为空');
		}
		$detail = D('Goodsshopcate')->where(array('shop_id'=>$this->shop_id,'cate_name'=>$data['cate_name']))->select();
		if(!empty($detail)){
			$this->baoError('分类名称已存在');
		}
		$data['orderby'] = (int) $data['orderby'];
		return $data;
	}

	public function createone($parent_id=0) {
		if ($this->isPost()) {
			$data = $this->createCheck();
			$obj = D('Goodsshopcate');
			$data['parent_id'] = $parent_id;
			$data['shop_id'] = $this->shop_id;
			if ($obj->add($data)) {
				$obj->cleanCache();
				$this->baoSuccess('添加成功', U('goodsshopcate/index'));
			}
			$this->baoError('操作失败！');
		} else {
			$this->assign('parent_id',$parent_id);
			$this->display();
		}
	}

	public function edit($cate_id=0) {
		if ($cate_id = (int) $cate_id) {
			$obj = D('Goodsshopcate');
			if (!$detail = $obj->find($cate_id)) {
				$this->baoError('请选择要编辑的商家分类');
			}
			if($detail['shop_id'] != $this->shop_id){
				$this->baoError('不可以修改别人的内容');
			}

			if ($this->isPost()) {
				$data = $this->editCheck();
				$data['cate_id'] = $cate_id;
				$data['shop_id'] = $this->shop_id;
				if (false !== $obj->save($data)) {
					$obj->cleanCache();
					$this->baoSuccess('操作成功', U('goodsshopcate/index'));
				}
				$this->baoError('操作失败');
			} else {
				$this->assign('detail', $detail);
				$this->display();
			}
		} else {
			$this->baoError('请选择要编辑的商家分类');
		}
	}

	private function editCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['cate_name'] = htmlspecialchars($data['cate_name']);
		if (empty($data['cate_name'])) {
			$this->baoError('分类不能为空');
		}
		$detail = D('Goodsshopcate')->where(array('shop_id'=>$this->shop_id,'cate_name'=>$data['cate_name'],'cate_id'=>array('neq',$_GET['cate_id'])))->find();
		if(!empty($detail)){
			$this->baoError('分类名称已存在');
		}
		$data['orderby'] = (int) $data['orderby'];
		if (empty($data['orderby'])) {
			$data['orderby'] = 100;
		}
		return $data;
	}

	public function delete($cate_id = 0) {
		if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
			$obj = D('Goodsshopcate');
			$obj->delcate($cate_id);
			$obj->cleanCache();
			$this->baoSuccess('删除成功！', U('Goodsshopcate/index'));
		} else {
			$cate_id = $this->_post('cate_id', false);
			if (is_array($cate_id)) {
				$obj = D('Goodscate');
				foreach ($cate_id as $id) {
					$obj->delete($id);
				}
				$obj->cleanCache();
				$this->baoSuccess('删除成功！', U('Goodsshopcate/index'));
			}
			$this->baoError('请选择要删除的商家分类');
		}
	}

	public function update() {
		$orderby = $this->_post('orderby', false);
		$obj = D('Goodsshopcate');
		foreach ($orderby as $key => $val) {
			$data = array(
                'cate_id' => (int) $key,
                'orderby' => (int) $val
			);
			$obj->save($data);
		}
		$obj->cleanCache();
		$this->baoSuccess('更新成功', U('Goodsshopcate/index'));
	}

	public function child($parent_id=0){
		$datas = D('Goodsshopcate')->fetchAll();
		$str = '';

		foreach($datas as $var){
			if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
				 
				foreach($datas as $var2){

					if($var2['parent_id'] == $var['cate_id']){
						if ($var2['cate_id'] == (int)$this->_get('shopcate_id')){
							$str.='<option value="'.$var2['cate_id'].'" selected="selected">'.$var2['cate_name'].'</option>'."\n\r";
						}else {
							$str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
						}
						foreach($datas as $var3){
							if($var3['parent_id'] == $var2['cate_id']){
								if ($var3['cate_id'] == (int)$this->_get('shopcate_id')){
									$str.='<option value="'.$var3['cate_id'].'" selected="selected">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r";
								}else {
									$str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r";
								}
							}

						}
					}
				}
			}
		}
		echo $str;die;
	}
}
