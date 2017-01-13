<?php
class EleproductAction extends CommonAction {

    private $create_fields = array('product_name','settlement_price' ,'shop_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num', 'create_time', 'create_ip');
    private $edit_fields = array('product_name','settlement_price', 'shop_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num');

    public function index() {
        $Eleproduct = D('Eleproduct');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件：未删除和审查通过的菜品
        $map = array('bao_ele_product.closed' => 0, 'bao_ele_product.audit' => 1, 'bao_ele.closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('EleProductSearchIndexMessage'))){
            $map = cookie(md5('EleProductSearchIndexMessage'));
            if(cookie(md5('EleProductSearchIndexMessageProduct'))){
                $map['bao_ele_product.product_name'] = cookie(md5('EleProductSearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_ele_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_ele_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Ele')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if(empty($keyword)){
                unset($map['bao_ele_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_ele_product.store_id']);
                unset($map['bao_ele_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_ele_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_ele_product.store_id'] == true){
                $store = M('Ele')->find($map['bao_ele_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_ele_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('EleProductSearchIndexMessage'), $map, 900);
        cookie(md5('EleProductSearchIndexMessageProduct'), $map['bao_ele_product.product_name'], 900);

        // 回显有写问题

        $count = $Eleproduct
            ->join('LEFT JOIN bao_ele ON bao_ele_product.store_id=bao_ele.store_id')
            ->where($map)
            ->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Eleproduct
            ->field('bao_ele_product.*, bao_ele.store_name, bao_ele_product_cate.cate_name')
            ->join('LEFT JOIN bao_ele ON bao_ele_product.store_id=bao_ele.store_id')
            ->join('LEFT JOIN bao_ele_product_cate ON bao_ele_product.cate_id=bao_ele_product_cate.cate_id')
            ->where($map)
            ->order(array('bao_ele_product.update_time' => 'desc'))   // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Eleproduct');
            if ($obj->add($data)) {
                 D('Elecate')->updateNum($data['cate_id']);
                $this->baoSuccess('添加成功', U('eleproduct/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $temp = $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($temp)) {
            $this->baoError('菜名不能为空');
        } 
        $temp = $data['shop_id'] = (int) $data['shop_id'];
        if (empty($temp)) {
            $this->baoError('商家不能为空');
        }
        $temp = $data['cate_id'] = (int) $data['cate_id'];
        if (empty($temp)) {
            $this->baoError('分类不能为空');
        }
        $temp = $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($temp)) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $temp = $data['price'];
        if (empty($temp)) {
            $this->baoError('价格不能为空');
        } 
       
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    /**peace
     * @param int $product_id
     * 显示商品详情
     */
    public function detail($product_id = 0) {
        if ($product_id = (int) $product_id) {
            $obj = D('Eleproduct');
            if (!$detail = $obj->find($product_id)) {
                $this->baoError('请选择要编辑的商品');
            }
            $eleproductcates = D('Eleproductcate')->getProductCate($detail['store_id']);
            $this->assign('eleproductcates', $eleproductcates);
//            var_dump($eleproductcates);

            $ele = D('Ele')->where(array('store_id'=>$detail['store_id']))->find();
            $this->assign('ele',$ele);
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->baoError('请选择要查看的商品');
        }
    }

    // update:remove begin

    /**peace  废弃的方法，大后台超市商品不编辑
     * @param int $product_id
     */
//    public function edit($product_id = 0) {
//        if ($product_id = (int) $product_id) {
//            $obj = D('Eleproduct');
//            if (!$detail = $obj->find($product_id)) {
//                $this->baoError('请选择要编辑的菜单管理');
//            }
//
//            $detail['store_name'] = D('ele')->where(array('store_id'=>$detail['store_id']))->getField('shop_name');
//
//            if ($this->isPost()) {
//                $data = $this->editCheck();
//                $data['product_id'] = $product_id;
//                if (false !== $obj->save($data)) {
//                       D('Elecate')->updateNum($data['cate_id']);
//                    $this->baoSuccess('操作成功', U('eleproduct/index'));
//                }
//                $this->baoError('操作失败');
//            } else {
//                $this->assign('detail', $detail);
//                $this->display();
//            }
//        } else {
//            $this->baoError('请选择要编辑的菜单管理');
//        }
//    }

//    private function editCheck() {
//        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
//        $data['product_name'] = htmlspecialchars($data['product_name']);
//        if (empty($data['product_name'])) {
//            $this->baoError('菜名不能为空');
//        }  $data['cate_id'] = (int) $data['cate_id'];
//        if (empty($data['cate_id'])) {
//            $this->baoError('分类不能为空');
//        } $data['photo'] = htmlspecialchars($data['photo']);
//        if (empty($data['photo'])) {
//            $this->baoError('请上传缩略图');
//        }
//
//        if (empty($data['price'])) {
//            $this->baoError('价格不能为空');
//        }
//
//
//        $data['is_new'] = (int) $data['is_new'];
//        $data['is_hot'] = (int) $data['is_hot'];
//        $data['is_tuijian'] = (int) $data['is_tuijian'];
//        $data['sold_num'] = (int) $data['sold_num'];
//        $data['month_num'] = (int) $data['month_num'];
//        return $data;
//    }

//    public function delete($product_id = 0) {
//        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
//            $obj = D('Eleproduct');
//            $obj->deleteById($product_id);
//            D('EleCart')->clearByProductId($product_id);//清空购物车
//            $this->baoSuccess('删除成功！', U('eleproduct/index'));
//        } else {
//            $product_id = $this->_post('product_id', false);
//            if (is_array($product_id)) {
//                $obj = D('Eleproduct');
//                $cart_model = D('EleCart');
//                foreach ($product_id as $id) {
//                    $obj->deleteById($id);
//                    $cart_model->clearByProductId($id);//清空购物车
//                }
//                $this->baoSuccess('删除成功！', U('eleproduct/index'));
//            }
//            $this->baoError('请选择要删除的菜单管理');
//        }
//    }

    // update:remove end

//    public function audit($product_id = 0) {
//        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
//            $obj = D('EleProduct');
//            $r = $obj -> where('product_id ='.$product_id) -> find();
//
//            $obj->save(array('product_id' => $product_id, 'audit' => 1));
//            $this->baoSuccess('审核成功！', U('eleproduct/index'));
//        } else {
//            $product_id = $this->_post('product_id', false);
//            if (is_array($product_id)) {
//                $obj = D('EleProduct');
//                foreach ($product_id as $id) {
//                    $r = $obj -> where('product_id ='.$id) -> find();
//
//                    $obj->save(array('product_id' => $id, 'audit' => 1));
//                }
//                $this->baoSuccess('审核成功！', U('eleproduct/index'));
//            }
//            $this->baoError('请选择要审核的商品');
//        }
//    }

    /**peace
     * @param int $product_id
     * 外卖（商家审核单个及批量）删除列表
     */
    public function shiftdelete($product_id = 0) {
        // 调用私有方法
        self::setDelete($product_id, 'eleproduct/apply', 'Eleproduct', 'product_id');
    }

    public function delete($product_id = 0) {
        // 调用私有方法
        self::setDelete($product_id, 'eleproduct/index', 'Eleproduct', 'product_id');
    }

    /**peace
     * @param int $product_id
     * 外卖 => 商品审核列表  待审查
     */
    public function apply() {
        $Eleproduct = D('Eleproduct');
        import('ORG.Util.Page'); // 导入分页类

        // 所有未删除且待审查的商品
        // 商品存在且待审查，对应店铺存在
        $map = array('bao_ele_product.closed' => 0, 'bao_ele_product.audit' => 0, 'bao_ele.closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('EleProductApplySearchIndexMessage'))){
            $map = cookie(md5('EleProductApplySearchIndexMessage'));
            if(cookie(md5('EleProductApplySearchIndexMessageProduct'))){
                $map['bao_ele_product.product_name'] = cookie(md5('EleProductApplySearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_ele_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_ele_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Ele')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if ($audit = (int) $this->_param('audit', 'intval')) {
                if($audit == 2){
                    $map['bao_ele_product.audit'] = $audit = 0;
                }else if($audit == 3){
                    $map['bao_ele_product.audit'] = $audit;
                }else{
                    $map['bao_ele_product.audit'] = $audit = 0;
                }
                $this->assign('audit', $audit);
            }

            if(empty($keyword)){
                unset($map['bao_ele_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_ele_product.store_id']);
                unset($map['bao_ele_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_ele_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_ele_product.store_id'] == true){
                $store = M('Ele')->find($map['bao_ele_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_ele_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('EleProductApplySearchIndexMessage'), $map, 900);
        cookie(md5('EleProductApplySearchIndexMessageProduct'), $map['bao_ele_product.product_name'], 900);
//        var_dump($map);

        $count = $Eleproduct
            ->join('LEFT JOIN bao_ele ON bao_ele_product.store_id=bao_ele.store_id')
            ->where($map)
            ->count();   // 查询满足要求的总记录数

        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        $list = $Eleproduct
            ->field('bao_ele_product.*, bao_ele.store_name, bao_ele.phone, bao_ele_product_cate.cate_name')
            ->join('LEFT JOIN bao_ele ON bao_ele_product.store_id=bao_ele.store_id')
            ->join('LEFT JOIN bao_ele_product_cate ON bao_ele_product.cate_id=bao_ele_product_cate.cate_id')
            ->where($map)
            ->order(array('bao_ele_product.audit' => 'ASC', 'bao_ele_product.update_time' => 'desc'))  // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('audit_2', $map['bao_ele_product.audit']);
//        var_dump($map['bao_ele_product.audit']);
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    /**peace
     * @param int $product_id
     * 外卖商品审查（单个及多个审查）
     * audit => 1      审查通过
     */
    public function audit($product_id = 0) {
        // 审查状态方法
        self::allAudit($product_id, 1, '该商品通过审查!');
    }

    /**peace
     * @param int $product_id
     * 外卖商品审查（单个及多个审查）
     * audit => 3      审查未通过
     */
    public function unAudit($product_id = 0) {
        // 审查状态方法
        self::allAudit($product_id, 3, '该商品未通过审查!');
    }

    /**
     * 商品审查公共方法
     */
    protected function allAudit($product_id, $audit, $pass){
        $obj = D('Eleproduct');
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            // 修改单个商品审查，
            // 是否存在该数据
            if(!($obj->where(array('product_id' => $product_id))->find())){
                return $this->baoError('请选择要审核的商品');
            }

            //
            $res = $obj->save(array('product_id' => $product_id, 'audit' => $audit));
            if ($res){
                return $this->baoSuccess($pass, U('eleproduct/apply'));
            }else {
                return $this->baoError($pass);
            }
        } else {

            // 修改多个商品审查，
            $product_id = $this->_post('product_id', false);
            if (is_array($product_id)) {
                foreach ($product_id as $id){
                    // 验证是否存在该条信息
                    $rst = $obj
                        ->where(array('product_id' => $id))
                        ->find();
                    if(!$rst){
                        return $this->baoError('请选择要审核的商品');
                    }
                    $obj->save(array('product_id' => $id, 'audit' => $audit));
                }
                return $this->baoSuccess($pass, U('eleproduct/apply'));
            }else {
                return $this->baoError('请选择要审核的商品');
            }
        }
    }

    /**peace
     * 为搜索选择商家
     */
    public function select() {
        $Ele = M('Ele');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件只有存在且以审核通过的店铺
        $map = array('bao_ele.closed' => 0, 'bao_ele.audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['bao_ele.store_name|bao_ele.phone'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if ($city_id = (int) $this->_param('city_id')) {
            $map['bao_ele.city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['bao_ele.area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }

        if ($business_id = (int) $this->_param('business_id')) {
            $map['bao_ele.business_id'] = $business_id;
            $this->assign('business_id', $business_id);
        }

        $Page = new Page($Ele->where($map)->count(), 12);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        // 查询说有的市区县名称及分类名
        $list = $Ele
            ->field('bao_ele.*, bao_city.name as city_name, bao_area.area_name, bao_business.business_name')
            ->join('LEFT JOIN bao_city ON bao_ele.city_id=bao_city.city_id')
            ->join('LEFT JOIN bao_area ON bao_ele.area_id=bao_area.area_id')
            ->join('LEFT JOIN bao_business ON bao_ele.business_id=bao_business.business_id')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('cities', M('City')->select());
        if($map['bao_ele.city_id']){
            $this->assign('areas',
                M('Area')->where(array('city_id' => $map['bao_ele.city_id']))->select()
            );
        }
        if($map['bao_ele.area_id']){
            $this->assign('businesses',
                M('Business')->where(array('area_id' => $map['bao_ele.area_id']))->select()
            );
        }
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    /**peace
     * 初始化搜索引擎，菜单列表
     */
    public function initialIndex(){
        cookie(md5('EleProductSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('eleproduct/index'), 1000);
    }

    /**peace
     * 初始化搜索引擎，审核列表
     */
    public function initialApply(){
        cookie(md5('EleProductApplySearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('eleproduct/apply'), 1000);
    }

}
