<?php



class MeishigoodsAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
        
    }

    public function index() {
        $meishi = D('MeishiGoods');
        $meishi_cate_mod = D('MeishiCate');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => $meishi->flag['exist']);
        $count = $meishi->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $meishi->where($map)->order(array('store_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //var_dump($list);
        $this->assign('meishi_cate',$meishi_cate = $meishi_cate_mod->fetchAll());
        //var_dump($meishi_cate);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('areas', D('Area')->fetchAll());
        $exame_status=array('1'=>'待审核','2'=>'审核通过','3'=>'审核不通过');
                //var_dump($exame_status);
                $this->assign('exame_status',$exame_status);
        $this->assign('business', D('Business')->fetchAll());
        $this->display(); // 输出模板
    }

        public function see($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('MeishiGoods');
            if (!$detail = $obj->find($goods_id)) {
                $this->baoError('要查看的商品不正确');
            }
            if ($this->isPost()) {
                
                
                
                $data['status'] = $_POST['status'];
                $data['exame_explain'] = $_POST['exame_explain'];
                $data['goods_id'] = $_POST['goods_id'];
                $obj->save($data);
                $this->baoSuccess('操作成功', U('meishigoods/index'));
               
            } else {
                
                $meishi_cate_mod = D('MeishiCate');
                $this->assign('meishi_cate',$meishi_cate = $meishi_cate_mod->fetchAll());
                
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的美食商家');
        }
    }
    ///----------------------------

    //商家审核
    public function exame() {
        $meishi = D('Meishi');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('status'=>2, 'closed' => $meishi->flag['exist']);
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
        //var_dump($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
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
    
      public function exame_see($store_id = 0) {
        if ($store_id = (int) $store_id) {
            $obj = D('Meishi');
            if (!$detail = $obj->find($store_id)) {
                $this->baoError('请选择要审核的商家');
            }
            if ($this->isPost()) {
                $shop_mod = D('Shop');
                $users_mod = D('Users');
                $data['store_id'] = $_GET['store_id'];
                $data['status'] = $_POST['status'];
                $data['exame_explain'] = $_POST['exame_explain'];
               // var_dump($detail);die;
                $uid = $shop_mod->where(array('shop_id'=>$detail['shop_id']))->getField('user_id');
                $username = $users_mod->where(array('user_id'=>$uid))->getField('nickname');
                if (false !== $obj->save($data)) {
                    if($data['status']=='3'){
                        D('Sms')->sendSms('meishi_open_err', I('boss_tel'),array('username'=>$username));
                    }elseif($data['status']=='1'){
                        D('Sms')->sendSms('meishi_open_ok', I('boss_tel'),array('username'=>$username));
                    }
                    $this->baoSuccess('操作成功', U('ele/exame'));
                }
                echo $obj->getLastSql();die;
                $this->baoError($obj->getError());
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的美食商家');
        }
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
    
    public function delete($goods_id = 0) 
    {
    	if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('MeishiGoods');
            $obj->deleteById($goods_id);
            D('MeishiCart')->clearByProductId($goods_id);//清空购物车
            $this->baoSuccess('删除成功！', U('Meishigoods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('MeishiGoods');
                $meishi_cart = D('MeishiCart');
                foreach ($goods_id as $id) {
                    $obj->deleteById($id);
                    $meishi_cart->clearByProductId($id);//清空购物车
                }
                $this->baoSuccess('删除成功！', U('Meishigoods/index'));
            }
            $this->baoError('请选择要删除的美食');
        }
    }

/*    public function opened($store_id = 0, $type = 'open') {
        if (is_numeric($store_id) && ($store_id = (int) $store_id)) {
            $obj = D('Ele');
            $is_open = 0;
            if ($type == 'open') {
                $is_open = 1;
             }
            $obj->save(array('store_id' => $store_id, 'is_open' => $is_open));
            die;
            $this->baoSuccess('操作成功！', U('meishi/index'));
        }
    }*/

}
