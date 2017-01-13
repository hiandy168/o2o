<?php

class PrimpAction extends CommonAction {

	public function index() {
		define('THEME_PATH', BASE_PATH . '/themes/default/');
		define('APP_TMPL_PATH', __ROOT__ . '/themes/default/');
		$mod = A('Pchome/Shop');

		$_GET['fetch'] = true;
		$_GET['shop_id'] = $this->shop_id;
		$content = $mod->detail();

		$primp_model = D('Shopprimp');
		$primp_data = $primp_model->getPrimp($this->shop_id);
		//获得推荐1中的商品
		$primp_data['recommend1_list'] = $primp_data['recommened1']?D('Goods')->where(array('goods_id'=>array('in',$primp_data['recommened1'])))->select():null;
		//获得推荐2中的商品
		$primp_data['recommend2_list'] = $primp_data['recommened2']?D('Goods')->where(array('goods_id'=>array('in',$primp_data['recommened2'])))->select():null;
		//var_dump($primp_data);  die;
		$this->assign('primp_data',$primp_data);

		$this->assign('content',$content);
		$this->display();
	}

	public function tmpEdit() {
		$primp_model = D('Shopprimp');
		$data = $this->_post('data');
		if ($data['head_bgground_pic']){
			$data['head_pic_hide'] ? $data['head_pic_hide']=$data['head_pic_hide'] : $data['head_pic_hide']=0;
		}
		if ($data['body_bgground_pic']){
			$data['body_pic_hide'] ? $data['body_pic_hide']=$data['body_pic_hide'] : $data['body_pic_hide']=0;
		}
		if(isset($_POST['recommened1'])){
			$data['recommened1'] = implode(",",$_POST['recommened1']);
		}
		if(isset($_POST['recommened2'])){
			$data['recommened2'] = implode(",",$_POST['recommened2']);
		}
		 


		$data['shop_id'] = $this->shop_id;
		$primp_model->setPrimp($this->shop_id,$data);
		$this->success('装修店铺成功');
	}

	public function bannerEdit() {
		$banner_model = D('Shopbanner');
		$banner_model->where(array('shop_id'=>$this->shop_id))->delete();
		$data_array = array(array('photo'=>$this->_post('photo_file1'),'link'=> $this->_post('link1'),'orderby'=>1),
		array('photo'=>$this->_post('photo_file2'),'link'=> $this->_post('link2'),'orderby'=>2),
		array('photo'=>$this->_post('photo_file3'),'link'=> $this->_post('link3'),'orderby'=>3)
		);
		$data   = $this->_post('data');
		$data['shop_id'] = $this->shop_id;
		foreach ($data_array as $value){
			if (!empty($value['photo'])){
				$datalist[] = array_merge($data,$value);
			}
		}

		//var_dump($datalist);die;
		foreach ($datalist as $k => $v) {
			$banner_model->add($v);
		}

		//echo $banner_model->getLastSql();die;
		$this->success('设置成功');
	}
}
