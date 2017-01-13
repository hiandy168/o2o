<?php



class ShopAction extends CommonAction {

	public function index() {
		$this->display();
	}

	public function logo() {
		if ($this->isPost()) {
			$logo = $this->_post('logo', 'htmlspecialchars');
			if (empty($logo)) {
				$this->baoError('请上传商铺LOGO');
			}

			$data = array('shop_id' => $this->shop_id, 'logo' => $logo);
			if (D('Shop')->save($data)) {
				$this->baoSuccess('上传LOGO成功！', U('shop/logo'));
			}
			$this->baoError('更新LOGO失败');
		} else {
			$this->display();
		}
	}

	public function image() {
		if ($this->isPost()) {
			$photo = $this->_post('photo', 'htmlspecialchars');
			if (empty($photo)) {
				$this->baoError('请上传商铺形象照');
			}
			if (!isImage($photo)) {
				$this->baoError('商铺形象照格式不正确');
			}
			$data = array('shop_id' => $this->shop_id, 'photo' => $photo);
			if (false !== D('Shop')->save($data)) {
				$this->baoSuccess('上传形象照成功！', U('shop/image'));
			}
			$this->baoError('更新形象照失败');
		} else {
			$this->display();
		}
	}




	 

	public function about() {
		if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), array('addr', 'contact', 'near', 'business_time','delivery_time'));
			$data['addr'] = htmlspecialchars($data['addr']);
			if (empty($data['addr'])) {
				$this->baoError('店铺地址不能为空');
			}
			$data['contact'] = htmlspecialchars($data['contact']);
			$data['near'] = htmlspecialchars($data['near']);
			$data['business_time'] = htmlspecialchars($data['business_time']);
			$data['shop_id'] = $this->shop_id;
			$data['delivery_time'] = (int)$data['delivery_time'];
			$details = $this->_post('details', 'SecurityEditorHtml');
			if ($words = D('Sensitive')->checkWords($details)) {
				$this->baoError('商家介绍含有敏感词：' . $words);
			}
			$ex = array(
                'details'        => $details,
                'near'           => $data['near'],
                'business_time'  => $data['business_time'],
                'delivery_time'  => $data['delivery_time'],
			);
			unset($data['business_time'],$data['near'],$data['delivery_time']);
			if (false !== D('Shop')->save($data)) {
				D('Shopdetails')->upDetails($this->shop_id,$ex);
				$this->baoSuccess('操作成功', U('shop/about'));
			}
			$this->baoError('操作失败');
		} else {

			$this->assign('ex', D('Shopdetails')->find($this->shop_id));
			$this->display();
		}
	}
	public function info(){
		$shop_mod = D('Shop');
		if ($this->isPost()) {
			 
			if($data = $shop_mod->token(false)->create($_POST['data'])){
				// if (!$shop_mod->token(true)->autoCheckToken($_POST)){
				//                        $this->baoError("不能重复修改");
				//                    }
				if($shop_mod->save($data)!==false){

					$this->baoSuccess("修改成功");
				}else{

					$this->baoError("修改失败");
				}
			}else{
				$this->baoError($shop_mod->getError());
			}
			 
		}else{
			 
			$this->display();
		}

	}


}
