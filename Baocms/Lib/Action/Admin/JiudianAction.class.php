<?php



class JiudianAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
        
    }

    public function index() {
        $hotel = D('Hotel');
        $home_store_model = D('HomeStore');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('status'=>1, 'closed' => $hotel->flag['exist'], 'audit'=>1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['store_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
       
        $count = $hotel->where($map)->count(); // 查询满足要求的总记录数
       // var_dump($count);
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $hotel->where($map)->order(array('hotel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $key=>$val){
            $list[$key]['hotel_cate']=$this->cate($val['hotel_cate']);
            $list[$key]['hotel_brand']=$this->cate($val['hotel_brand']);
            $list[$key]['is_home'] = $home_store_model->check_is_home($val['hotel_id'],'hotel');
        }
        
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->display(); // 输出模板
    }

    //商家审核
    public function exame() {
        $meishi = D('Hotel');
        $shop_mod = D('Shop');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('audit'=>array('neq',1));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['store_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        $count = $meishi->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $meishi->where($map)->order(array('addtime' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
         $ids = array();

        foreach ($list as $k => &$val) {
    
            if ($val['shop_id']) {
                $user_id = $shop_mod->where(array('shop_id'=>$val['shop_id']))->getField('user_id');
                $ids[$user_id] = $user_id;
                $val['user_id']=$user_id;
                
            }

        }
        
        $this->assign('users', $users = D('Users')->itemsByIds($ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('citys', D('City')->fetchAll());
        $this->display(); // 输出模板
    }
    public function edit($store_id = 0) {
        if ($store_id = (int) $store_id) {
            $obj = D('Ele');
            if (!$detail = $obj->find($store_id)) {
                $this->baoError('请选择要编辑的外卖商家');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['store_id'] = $store_id;
                $cate = $this->_post('cate', false);
                $cate = implode(',', $cate);
                $data['cate'] = $cate;

                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('ele/index'));
                }
                echo $obj->getLastSql();die;
                $this->baoError($obj->getError());
            } else {
                $cate = explode(',', $detail['cate']);
                $this->assign('cate', $cate);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的外卖商家');
        }
    }
    //审核编辑
      public function exame_see($hotel_id = 0) {
        if ($hotel_id = (int) $hotel_id) {
            $obj = D('Hotel');
            if (!$this->hotel_info = $obj->find($hotel_id)) {
                $this->baoError('请选择要审核的商家');
            }
            //var_dump($this->hotel_info);
            $this->shop_info = D('shop')->where(array('shop_id'=>$this->hotel_info['shop_id']))->find();
                $this->member_info = D('Users')->find($this->shop_info['user_id']);
               
                
                if($this->hotel_info['level']==1){
                    $this->presonl_exame();
                }else{
                    $this->com_exame();
                }
        } else {
            $this->baoError('请选择要编辑的美食商家');
        }
    }
        public function presonl_exame(){
        $auth_info = D('presonal_store_open_auth')->find($this->shop_info['user_id']);
        if(IS_POST){
            
            if(D('Hotel')->save($_POST['data'])){
                
                 if($_POST['data']['audit']=='2'){
                        D('Sms')->sendSms('hotel_open_err', $this->member_info['mobile'],array('username'=>$this->member_info['nickname']));
                    }elseif($_POST['data']['audit']=='1'){
                        //更新认证状态
                        D('presonal_store_open_auth')->where(array('uid'=>$this->shop_info['user_id']))->save(array('auth'=>1));
                        //更新商家状态
                       // D('shop')->where(array('shop_id'=>$this->shop_info['shop_id']))->save(array('audit'=>1));
                        //检测我佣有的店铺
                        $my_store = D('MyHaveStore')->where(array('uid'=>$this->shop_info['user_id'],'sc_id'=>4))->count();
                        if(!$my_store){
                            D('MyHaveStore')->add(array('uid'=>$this->shop_info['user_id'],'sc_id'=>4));
                        }
                        
                        D('Sms')->sendSms('hotel_open_ok', $this->member_info['mobile'],array('username'=>$this->member_info['nickname'],'shopname'=>$this->chaoshi_info['shop_name']));
                    }
                $this->baoSuccess('审核成功', U('jiudian/exame'));
            }else{
                $this->baoSuccess('修改成功', U('jiudian/exame'));
            }
        }
        
        //var_dump($auth_info);
        $this->detail = $auth_info;
        $this->display('presonl_exame');
    }
    
        public function com_exame(){
        
        $auth_info = D('ComStoreOpenAuth')->where(array('store_id'=>$this->hotel_info['hotel_id'],'store_class_id'=>4))->find();
        
       // var_dump($_POST);die;
        if(IS_POST){
            
            if(D('Hotel')->save($_POST['data'])){
                
                 if($_POST['data']['audit']=='2'){
                        D('Sms')->sendSms('hotel_open_err', $this->member_info['mobile'],array('username'=>$this->member_info['nickname']));
                    }elseif($_POST['data']['audit']=='1'){
                        //更新认证状态
                        D('ComStoreOpenAuth')->where(array('store_id'=>$this->hotel_info['store_id'],'store_class_id'=>4))->save(array('audit'=>1));
                        //更新商家状态
                        //D('shop')->where(array('shop_id'=>$this->shop_info['shop_id']))->save(array('audit'=>1));
                        //检测我佣有的店铺
                        $my_store = D('MyHaveStore')->where(array('uid'=>$this->shop_info['user_id'],'sc_id'=>4))->count();
                        if(!$my_store){
                            D('MyHaveStore')->add(array('uid'=>$this->shop_info['user_id'],'sc_id'=>4));
                        }
                        
                       D('Sms')->sendSms('hotel_open_ok', $this->member_info['mobile'],array('username'=>$this->member_info['nickname'],'shopname'=>$this->ele_info['shop_name']));
                    }
                $this->baoSuccess('审核成功', U('jiudian/exame'));
            }else{
               
               $this->baoError('无操作'); 
            }
            
        }
        
        
        $auth_info['other_pic']=explode(",",$auth_info['other_pic']);
        //var_dump($auth_info);
        $this->detail = $auth_info;

        $this->display('com_exame');
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        $data['is_open'] = (int) $data['is_open'];
        $data['is_pay'] = (int) $data['is_pay'];
        $data['is_fan'] = (int) $data['is_fan'];
        $data['is_new'] = (int) $data['is_new'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['distribution'] = (int) $data['distribution'];
        $data['audit'] = (int) $data['audit'];
        $data['intro'] = htmlspecialchars($data['intro']);
        $data['rate'] = (int) $data['rate'];
        if (empty($data['intro'])) {
            $this->baoError('说明不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($hotel_id = 0) {
    	if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            $obj = D('Hotel');
            if ($obj->deleteAll($hotel_id)) {
            	$this->baoSuccess('删除成功！', U('jiudian/index'));
            }
            $this->baoError('删除失败！', U('jiudian/index'));                  
        } else {
            $hotel_id = $this->_post('hotel_id', false);
            if (is_array($hotel_id)) {
                $obj = D('Hotel');
                foreach ($hotel_id as $id) {
	                if (!$obj->deleteAll($id)) {
	            		$this->baoError('删除失败！', U('jiudian/index')); 
	            	}            
	            }
                $this->baoSuccess('删除成功！', U('jiudian/index'));
            }
            $this->baoError('请选择要删除的酒店');
        }
    }

    public function opened($hotel_id = 0, $type = 'open') {
        if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {


            $obj = D('Hotel');
            $is_open = 0;
            if ($type == 'open') {
                $is_open = 1;
            }
            $obj->save(array('hotel_id' => $hotel_id, 'is_open' => $is_open));

            $this->baoSuccess('操作成功！', U('jiudian/index'));
        }
    }

     protected function cate($k){
     $data=D('Hotel')->HotelCate();
     return $data[$k];
     }
    
    protected function type ($k){
    $data=D('Hotel')->HotelBrand;
    return $data[$k];
    }
    /*
     * 设为首页推荐
     * 作者：刘弢
     */
    public function to_home() {
        $store_id =  I('store_id','0','intval');
        if(!$store_id) {
            $this->baoError("参数错误");
        }
        $hotel_model = D('Hotel');
        $home_store_model = D('HomeStore');
        $is_home = $home_store_model->check_is_home($store_id, 'hotel');
        if ($is_home) {
            $this->baoError('已是首页推荐');
        }
        $hotel = $hotel_model->find($store_id);
        $data = array(
            'store_id' => $hotel['hotel_id'],
            'type' => 'hotel',
            'city_id' => $hotel['city_id'],
            'logo' => $hotel['store_logo'],
            'store_name' => $hotel['store_name'],
        );
        if ($home_store_model->add($data)) {
            $this->baoSuccess('设置成功',U('jiudian/index'));
        }else {
            $this->error('设置失败');
        }
    }
    /*
     * 取消首页推荐
     * 作者：刘弢
     */
    public function cancel_home() {
        $store_id =  I('store_id','0','intval');
        if(!$store_id) {
            $this->baoError("参数错误");
        }
        $hotel_model = D('Hotel');
        $home_store_model = D('HomeStore');
        $res = $home_store_model->cancel_home($store_id, 'hotel');
        if ($res){
            $this->baoSuccess('取消成功',U('jiudian/index'));
        }else {
            $this->baoError('取消失败');
        }
    }
    
    //酒店查询
    public function select()
    {
    	$hotelModel = D('Hotel');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('status'=>1, 'closed' => $hotelModel->flag['exist']);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['store_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
    
        $count = $hotelModel->where($map)->count();
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $hotelModel->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
}