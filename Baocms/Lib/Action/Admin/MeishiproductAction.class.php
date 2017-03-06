<?php



class MeishiproductAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
        
    }

    public function index() {
        $Meishiproduct = D('MeishiProduct');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件：未删除和审查通过的菜品
        $map = array('bao_meishi_product.closed' => 0, 'bao_meishi_product.audit' => 1, 'bao_meishi.closed' => 0, 'bao_meishi.audit' => 1);

        // 保存搜索信息到cookie中
        if(cookie(md5('MeishiProductSearchIndexMessage'))){
            $map = cookie(md5('MeishiProductSearchIndexMessage'));
            if(cookie(md5('MeishiProductSearchIndexMessageProduct'))){
                $map['bao_meishi_product.product_name'] = cookie(md5('MeishiProductSearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_meishi_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_meishi_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Meishi')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if(empty($keyword)){
                unset($map['bao_meishi_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_meishi_product.store_id']);
                unset($map['bao_meishi_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_meishi_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_meishi_product.store_id'] == true){
                $store = M('Meishi')->find($map['bao_meishi_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_ele_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('MeishiProductSearchIndexMessage'), $map, 900);
        cookie(md5('MeishiProductSearchIndexMessageProduct'), $map['bao_meishi_product.product_name'], 900);

        // 回显有写问题

        $count = $Meishiproduct
            ->join('LEFT JOIN bao_meishi ON bao_meishi_product.store_id=bao_meishi.store_id')
            ->where($map)
            ->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        $list = $Meishiproduct
            ->field('bao_meishi_product.*, bao_meishi.store_name, bao_meishi.logo')
            ->join('LEFT JOIN bao_meishi ON bao_meishi_product.store_id=bao_meishi.store_id')
            ->where($map)
            ->order(array('bao_meishi_product.update_time' => 'desc'))   // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list', $list); // 赋值数据集
//        var_dump($list[0]);
        $this->assign('page', $show); // 赋值分页输出
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

    /**peace
     * @param int $product_id
     * 显示商品详情
     */
    public function detail($product_id = 0) {
        if ($product_id = (int) $product_id) {
            $obj = D('MeishiProduct');
            if (!$detail = $obj->find($product_id)) {
                $this->baoError('请选择要编辑的商品');
            }

            $meishi = D('Meishi')->where(array('store_id'=>$detail['store_id']))->find();
            $photos = explode(',', $detail['photo']);
            $this->assign('photos', $photos);
//            var_dump($photos);
            $this->assign('meishi',$meishi);
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->baoError('请选择要查看的商品');
        }
    }

    /**peace
     * @param int $product_id
     * 美食（商家审核单个及批量）删除列表
     */
    public function shiftdelete($product_id = 0) {
        // 调用私有方法
        self::setDelete($product_id, 'meishiproduct/apply', 'MeishiProduct', 'product_id');
    }

    public function delete($product_id = 0) {
        // 调用私有方法
        self::setDelete($product_id, 'meishiproduct/index', 'MeishiProduct', 'product_id');
    }

    /**peace
     * @param int $product_id
     * 美食 => 商品审核列表  待审查
     */
    public function apply() {
        $Meishiproduct = D('Meishi_product');
        import('ORG.Util.Page'); // 导入分页类

        // 所有未删除且待审查的商品
        // 商品存在且待审查，对应店铺存在
        $map = array('bao_meishi_product.closed' => 0, 'bao_meishi_product.audit' => 0, 'bao_meishi.closed' => 0, 'bao_meishi.audit' => 1);
//        var_dump($map);

        // 保存搜索信息到cookie中
        if(cookie(md5('MeishiProductApplySearchIndexMessage'))){
            $map = cookie(md5('MeishiProductApplySearchIndexMessage'));
            if(cookie(md5('MeishiProductApplySearchIndexMessageProduct'))){
                $map['bao_meishi_product.product_name'] = cookie(md5('MeishiProductApplySearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_meishi_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_meishi_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Meishi')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if ($audit = (int) $this->_param('audit', 'intval')) {
                if($audit == 2){
                    $map['bao_meishi_product.audit'] = $audit = 0;
                }else if($audit == 3){
                    $map['bao_meishi_product.audit'] = $audit;
                }else{
                    $map['bao_meishi_product.audit'] = $audit = 0;
                }
                $this->assign('audit', $audit);
            }

            if ($product_type = (int) $this->_param('product_type', 'intval')) {
                if($product_type == 100){
                    unset($map['bao_meishi_product.product_type']);
                }else if(in_array($product_type, [1,2,3])){
                    $map['bao_meishi_product.product_type'] = $product_type;
                }
                $this->assign('product_type', $product_type);
            }

            if(empty($keyword)){
                unset($map['bao_meishi_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_meishi_product.store_id']);
                unset($map['bao_meishi_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_meishi_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_meishi_product.store_id'] == true){
                $store = M('Meishi')->find($map['bao_meishi_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_meishi_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('MeishiProductApplySearchIndexMessage'), $map, 900);
        cookie(md5('MeishiProductApplySearchIndexMessageProduct'), $map['bao_meishi_product.product_name'], 900);
//        var_dump($map);

        $count = $Meishiproduct
            ->join('LEFT JOIN bao_meishi ON bao_meishi_product.store_id=bao_meishi.store_id')
            ->where($map)
            ->count();   // 查询满足要求的总记录数

        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        $list = $Meishiproduct
            ->field('bao_meishi_product.*, bao_meishi.store_name, bao_meishi.phone, bao_meishi.logo, bao_meishi_meal.meal_use_num')
            ->join('LEFT JOIN bao_meishi ON bao_meishi_product.store_id=bao_meishi.store_id')
            ->join('LEFT JOIN bao_meishi_meal ON bao_meishi_product.product_id=bao_meishi_meal.meal_id')
            ->where($map)
            ->order(array('bao_meishi_product.audit' => 'ASC', 'bao_meishi_product.update_time' => 'desc'))  // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list[0]);

        $this->assign('audit_2', $map['bao_meishi_product.audit']);
        $this->assign('product_type_2', $map['bao_meishi_product.product_type']);
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    /**peace
     * @param int $product_id
     * 美食商品审查（单个及多个审查）
     * audit => 1      审查通过
     */
    public function audit($product_id = 0) {
        // 审查状态方法
        self::allAudit($product_id, 1, '该商品通过审查!');
    }

    /**peace
     * @param int $product_id
     * 美食商品审查（单个及多个审查）
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
        $obj = D('MeishiProduct');
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            // 修改单个商品审查，
            // 是否存在该数据
            if(!($obj->where(array('product_id' => $product_id))->find())){
                return $this->baoError('请选择要审核的商品12');
            }

            //
            $res = $obj->save(array('product_id' => $product_id, 'audit' => $audit));
            if ($res){
                return $this->baoSuccess($pass, U('meishiproduct/apply'));
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
                return $this->baoSuccess($pass, U('meishiproduct/apply'));
            }else {
                return $this->baoError('请选择要审核的商品');
            }
        }
    }


    // update:remove begin

//    public function delete($goods_id = 0)
//    {
//    	if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
//            $obj = D('MeishiGoods');
//            $obj->deleteById($goods_id);
//            D('MeishiCart')->clearByProductId($goods_id);//清空购物车
//            $this->baoSuccess('删除成功！', U('Meishigoods/index'));
//        } else {
//            $goods_id = $this->_post('goods_id', false);
//            if (is_array($goods_id)) {
//                $obj = D('MeishiGoods');
//                $meishi_cart = D('MeishiCart');
//                foreach ($goods_id as $id) {
//                    $obj->deleteById($id);
//                    $meishi_cart->clearByProductId($id);//清空购物车
//                }
//                $this->baoSuccess('删除成功！', U('Meishigoods/index'));
//            }
//            $this->baoError('请选择要删除的美食');
//        }
//    }

//    public function opened($store_id = 0, $type = 'open') {
//        if (is_numeric($store_id) && ($store_id = (int) $store_id)) {
//            $obj = D('Ele');
//            $is_open = 0;
//            if ($type == 'open') {
//                $is_open = 1;
//             }
//            $obj->save(array('store_id' => $store_id, 'is_open' => $is_open));
//            die;
//            $this->baoSuccess('操作成功！', U('meishi/index'));
//        }
//    }

    // update:remove end

    /**peace
     * 为搜索选择商家
     */
    public function select() {
        $meishi = M('Meishi');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件只有存在且以审核通过的店铺
        $map = array('bao_meishi.closed' => 0, 'bao_meishi.audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['bao_meishi.store_name|bao_meishi.phone'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if ($city_id = (int) $this->_param('city_id')) {
            $map['bao_meishi.city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['bao_meishi.area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }

        if ($business_id = (int) $this->_param('business_id')) {
            $map['bao_meishi.business_id'] = $business_id;
            $this->assign('business_id', $business_id);
        }

        $Page = new Page($meishi->where($map)->count(), 12);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        // 查询说有的市区县名称及分类名
        $list = $meishi
            ->field('bao_meishi.*, bao_city.name as city_name, bao_area.area_name, bao_business.business_name')
            ->join('LEFT JOIN bao_city ON bao_meishi.city_id=bao_city.city_id')
            ->join('LEFT JOIN bao_area ON bao_meishi.area_id=bao_area.area_id')
            ->join('LEFT JOIN bao_business ON bao_meishi.business_id=bao_business.business_id')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('cities', M('City')->select());
        if($map['bao_meishi.city_id']){
            $this->assign('areas',
                M('Area')->where(array('city_id' => $map['bao_meishi.city_id']))->select()
            );
        }
        if($map['bao_meishi.area_id']){
            $this->assign('businesses',
                M('Business')->where(array('area_id' => $map['bao_meishi.area_id']))->select()
            );
        }
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    /**peace
     * 展示美食全部图片
     */
    public function pictures($product_id = 0, $store_id = 0) {
        $meishiProductModel = M('MeishiProduct');

        // 商品合法性验证
        $findProduct = $meishiProductModel
            ->where(array('product_id' => $product_id))
            ->find();
        if(!$findProduct){
            $set = 1;
            $this->assign('set', $set);
            $this->display();   // 输出模板
        } elseif($store_id != $findProduct['store_id']){
            $set = 2;
            $this->assign('set', $set);
            $this->display();   // 输出模板
        }else{
            // 解析图片
//            $photos = explode(',', substr($findProduct['photo'], 1, -1));
            $photos = explode(',', $findProduct['photo']);
            if(!$photos[0]){
                array_shift($photos);
            }
            if(!$photos[count($photos)-1]){
                array_pop($photos);
            }

            $set = 3;
            $this->assign('set', $set);
            $this->assign('photos', $photos);   // 赋值数据集
            $this->display();   // 输出模板
        }
    }

    /**peace
     * 初始化搜索引擎，菜单列表
     */
    public function initialIndex(){
        cookie(md5('MeishiProductSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('meishiproduct/index'), 1000);
    }

    /**peace
     * 初始化搜索引擎，审核列表
     */
    public function initialApply(){
        cookie(md5('MeishiProductApplySearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('meishiproduct/apply'), 1000);
    }

}
