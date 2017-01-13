<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiproductAction extends CommonAction {

    private $create_fields = array('product_name','settlement_price' ,'store_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num', 'create_time', 'create_ip','inventory');
    private $edit_fields = array('product_name','settlement_price', 'store_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num','inventory');

    public function _initialize() {
        $chaoshiproductcates = D('Chaoshiproductcate')->getProductCate($this->shop_id);
        $this->assign('chaoshiproductcates', $chaoshiproductcates);
    }
    
    public function index() {
        $Chaoshiproduct = D('Chaoshiproduct');
        import('ORG.Util.Page'); // 导入分页类

        // 所有未删除的商品，此处别名
        $map = array('bao_chaoshi_product.closed'=> 0, 'bao_chaoshi_product.audit' => 1, 'bao_chaoshi.closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('ChaoshiProductSearchIndexMessage'))){
            $map = cookie(md5('ChaoshiProductSearchIndexMessage'));
            if(cookie(md5('ChaoshiProductSearchIndexMessageProduct'))){
                $map['bao_chaoshi_product.product_name'] = cookie(md5('ChaoshiProductSearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_chaoshi_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_chaoshi_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Chaoshi')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if(empty($keyword)){
                unset($map['bao_chaoshi_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_chaoshi_product.store_id']);
                unset($map['bao_chaoshi_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_chaoshi_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_chaoshi_product.store_id'] == true){
                $store = M('Chaoshi')->find($map['bao_chaoshi_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_chaoshi_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('ChaoshiProductSearchIndexMessage'), $map, 900);
        cookie(md5('ChaoshiProductSearchIndexMessageProduct'), $map['bao_chaoshi_product.product_name'], 900);

        $count = $Chaoshiproduct
            ->join('LEFT JOIN bao_chaoshi ON bao_chaoshi_product.store_id=bao_chaoshi.store_id')
            ->where($map)
            ->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Chaoshiproduct
            ->field('bao_chaoshi_product.*, bao_chaoshi.store_name, bao_chaoshi_product_cate.cate_name')
            ->join('LEFT JOIN bao_chaoshi ON bao_chaoshi_product.store_id=bao_chaoshi.store_id')
            ->join('LEFT JOIN bao_chaoshi_product_cate ON bao_chaoshi_product.cate_id=bao_chaoshi_product_cate.cate_id')
            ->where($map)
            ->order(array('bao_chaoshi_product.update_time' => 'desc'))   // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list[0]);

        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Chaoshiproduct');
            if ($obj->add($data)) {
                 D('Elecate')->updateNum($data['cate_id']);
                $this->baoSuccess('添加成功', U('chaoshiproduct/index'));
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
            $this->baoError('商品名不能为空');
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
        $temp = $data['settlement_price'];
        if (empty($temp)) {
            $this->baoError('结算价格不能为空');
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

    // update:remove begin

    /**peace  废弃的方法，大后台外卖商品不编辑
     * @param int $product_id
     */
//    public function edit($product_id = 0) {
//        if ($product_id = (int) $product_id) {
//            $obj = D('Chaoshiproduct');
//            if (!$detail = $obj->find($product_id)) {
//                $this->baoError('请选择要编辑的商品');
//            }
//            $chaoshiproductcates = D('Chaoshiproductcate')->getProductCate($detail['store_id']);
//            $this->assign('chaoshiproductcates', $chaoshiproductcates);
//            $chaoshi = D('Chaoshi')->where(array('store_id'=>$detail['store_id']))->find();
//            $this->assign('chaoshi',$chaoshi);
//            if ($this->isPost()) {
//                $data = $this->editCheck();
//                $data['product_id'] = $product_id;
//                if (false !== $obj->save($data)) {
//                    $this->baoSuccess('操作成功', U('chaoshiproduct/index'));
//                }
//                $this->baoError('操作失败');
//            } else {
//                $this->assign('detail', $detail);
//                $this->display();
//            }
//        } else {
//            $this->baoError('请选择要编辑的商品');
//        }
//    }

    // update:remove end

    /**peace  代替上面的编辑方法
     * @param int $product_id
     */
    public function detail($product_id = 0) {
        if ($product_id = (int) $product_id) {
            $obj = D('Chaoshiproduct');
            if (!$detail = $obj->find($product_id)) {
                $this->baoError('请选择要编辑的商品');
            }
            $chaoshiproductcates = D('Chaoshiproductcate')->getProductCate($detail['store_id']);
            $this->assign('chaoshiproductcates', $chaoshiproductcates);

            $chaoshi = D('Chaoshi')->where(array('store_id'=>$detail['store_id']))->find();
            $this->assign('chaoshi',$chaoshi);
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->baoError('请选择要查看的商品');
        }
    }

    // update:remove begin

//    private function editCheck() {
//        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
//        $data['product_name'] = htmlspecialchars($data['product_name']);
//        if (empty($data['product_name'])) {
//            $this->baoError('菜名不能为空');
//        }
//        $data['store_id'] = (int) $data['store_id'];
//        if (empty($data['store_id'])) {
//            $this->baoError('商家不能为空');
//        } $data['cate_id'] = (int) $data['cate_id'];
//        if (empty($data['cate_id'])) {
//            $this->baoError('分类不能为空');
//        } $data['photo'] = htmlspecialchars($data['photo']);
//        if (empty($data['photo'])) {
//            $this->baoError('请上传缩略图');
//        }
//        if (!isImage($data['photo'])) {
//            $this->baoError('缩略图格式不正确');
//        }
//        if (empty($data['price'])) {
//            $this->baoError('价格不能为空');
//        }
//        $data['sold_num'] = (int) $data['sold_num'];
//        $data['month_num'] = (int) $data['month_num'];
//        return $data;
//    }

    // update:remove end
    
    public function delete($product_id = 0) {

        // 调用私有方法
        self::setDelete($product_id, 'chaoshiproduct/index', 'Chaoshiproduct', 'product_id');

        // update:remove begin
//    	if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
//            $obj = D('Chaoshiproduct');
//            $obj->deleteById($product_id);
//            D('ChaoshiCart')->clearByProductId($product_id);//清空购物车
//            $this->baoSuccess('删除成功！', U('chaoshiproduct/index'));
//        } else {
//            $product_id = $this->_post('product_id', false);
//            if (is_array($product_id)) {
//                $obj = D('Chaoshiproduct');
//                $chaoshi_cart = D('ChaoshiCart');
//                foreach ($product_id as $id) {
//                    $obj->deleteById($id);
//                    $chaoshi_cart->clearByProductId($id);//清空购物车
//                }
//                $this->baoSuccess('删除成功！', U('chaoshiproduct/index'));
//            }
//            $this->baoError('请选择要删除的商品');
//        }
        // update:remove end
    }

    /**peace
     * @param int $chaoshiProduct_id
     * 社区超市（商家审核单个及批量）删除列表
     */
    public function shiftdelete($product_id = 0) {

        // 调用私有方法
        self::setDelete($product_id, 'chaoshiproduct/apply', 'Chaoshiproduct', 'product_id');
    }

    /**peace
     * @param int $product_id
     * 超市商品审查（单个及多个审查）
     * audit => 1      审查通过
     */
    public function audit($product_id = 0) {
        // 审查状态方法
        self::allAudit($product_id, 1, '该商品通过审查!');
    }

    /**peace
     * @param int $product_id
     * 超市商品审查（单个及多个审查）
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
        $obj = D('Chaoshiproduct');
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            // 修改单个商品审查，
            // 是否存在该数据
            if(!($obj->where(array('product_id' => $product_id))->find())){
               return $this->baoError('请选择要审核的商品');
            }

            //
            $res = $obj->save(array('product_id' => $product_id, 'audit' => $audit));
            if ($res){
                return $this->baoSuccess($pass, U('chaoshiproduct/apply'));
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
                return $this->baoSuccess($pass, U('chaoshiproduct/apply'));
            }else {
                return $this->baoError('请选择要审核的商品');
            }
        }
    }

    /**peace
     * @param int $product_id
     * 社区超市 => 商品审核列表  待审查
     */
    public function apply() {
        $Chaoshiproduct = D('Chaoshiproduct');
        import('ORG.Util.Page'); // 导入分页类

        // 所有未删除且待审查的商品
        // 商品存在且待审查，对应店铺存在
        $map = array('bao_chaoshi_product.closed' => 0, 'bao_chaoshi_product.audit' => 0, 'bao_chaoshi.closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('ChaoshiProductSearchApplyMessage'))){
            $map = cookie(md5('ChaoshiProductSearchApplyMessage'));
            if(cookie(md5('ChaoshiProductSearchApplyMessageProduct'))){
                $map['bao_chaoshi_product.product_name'] = cookie(md5('ChaoshiProductSearchApplyMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_chaoshi_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_chaoshi_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Chaoshi')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if ($audit = (int) $this->_param('audit', 'intval')) {
                if($audit == 2){
                    $map['bao_chaoshi_product.audit'] = $audit = 0;
                }else if($audit == 3){
                    $map['bao_chaoshi_product.audit'] = $audit;
                }else{
                    $map['bao_chaoshi_product.audit'] = $audit = 0;
                }
                $this->assign('audit', $audit);
            }

            if(empty($keyword)){
                unset($map['bao_chaoshi_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_chaoshi_product.store_id']);
                unset($map['bao_chaoshi_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_chaoshi_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_chaoshi_product.store_id'] == true){
                $store = M('Chaoshi')->find($map['bao_chaoshi_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_chaoshi_product.store_id']);
            }
        }
//        var_dump($map);
        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('ChaoshiProductSearchApplyMessage'), $map, 900);
        cookie(md5('ChaoshiProductSearchApplyMessageProduct'), $map['bao_chaoshi_product.product_name'], 900);

        $count = $Chaoshiproduct
            ->join('LEFT JOIN bao_chaoshi ON bao_chaoshi_product.store_id=bao_chaoshi.store_id')
            ->where($map)
            ->count();  // 查询满足要求的总记录数

        $Page = new Page($count, 25);  // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();  // 分页显示输出
//        var_dump($count);
        $list = $Chaoshiproduct
            ->field('bao_chaoshi_product.*, bao_chaoshi.store_name, bao_chaoshi.phone, bao_chaoshi_product_cate.cate_name')
            ->join('LEFT JOIN bao_chaoshi ON bao_chaoshi_product.store_id=bao_chaoshi.store_id')
            ->join('LEFT JOIN bao_chaoshi_product_cate ON bao_chaoshi_product.cate_id=bao_chaoshi_product_cate.cate_id')
            ->where($map)
            ->order(array('bao_chaoshi_product.audit' => 'ASC', 'bao_chaoshi_product.update_time' => 'desc'))  // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('audit_2', $map['bao_chaoshi_product.audit']);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    // update:remove begin
    /**peace
     * @param int $store_id
     * @param $url   // 页面跳转的方向
     * 共本类的 delete及shiftdelete方法调用
     */
//    protected function setDelete($product_id, $url) {
//        $obj = D('Chaoshiproduct');
//        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
//            // deleteAll自封装方法
//            if ($obj->deleteAll($product_id)) {
//                $this->baoSuccess('删除成功！', U($url));
//            }
//            $this->baoError('删除失败！', U($url));
//        } else {
//            // _post的product_id是html页面对应的name属性
//            $product_id = $this->_post('product_id', false);
//            if (is_array($product_id)) {
//                foreach ($product_id as $id) {
//                    if (!$obj->deleteAll($id)) {
//                        $this->baoError('删除失败！', U($url));
//                    }
//                }
//                $this->baoSuccess('删除成功！', U($url));
//            }
//            $this->baoError('请选择要删除的商品');
//        }
//    }

    // update:remove end

    /**
     * 修改商品的价格或者数量
     */
    public function changePN($product_id){
        $supermarketModel = D('Chaoshiproduct');
        if($this->isPost()){
            // 对传递的参数判断
            $product_id = I('post.product_id','','htmlspecialchars');
            $findProduct = $supermarketModel->find($product_id);
            if(!$findProduct){
                $this->baoError('该商品不存在');
            }
            $price = I('post.price','','htmlspecialchars');
            $product_num = I('post.product_num','','htmlspecialchars');
            if($product_num != (int)$product_num){
                $this->baoError('该商品信息有误');
            }

            // 判定数据是否被修改
            if($findProduct['price'] == $price && $findProduct['product_num'] == $product_num){
                $this->baoSuccess('该商品价格和库存未改变', 'index');
            }

            $rst = $supermarketModel->save(array('product_id'=>$product_id, 'price'=>$price, 'product_num'=>$product_num));

            if(!$rst){
                $this->baoError('该商品信息保存失败');
            }
            $this->baoSuccess('该商品信息保存成功', 'index');
        }else{
            if(is_numeric($product_id) && $product_id = (int)$product_id){
                $findProduct = $supermarketModel->find($product_id);
                if(!$findProduct){
                    $this->baoError('未发现该商品');
                }
                $this->assign('list', $findProduct);
                $this->display();
            }
            $this->baoError('商品ID参数错误');
        }

    }

    /**peace
     * 为搜索选择商家
     */
    public function select() {
        $Ele = M('Chaoshi');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件只有存在且以审核通过的店铺
        $map = array('bao_chaoshi.closed' => 0, 'bao_chaoshi.audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['bao_chaoshi.store_name|bao_chaoshi.phone'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if ($city_id = (int) $this->_param('city_id')) {
            $map['bao_chaoshi.city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['bao_chaoshi.area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }

        if ($business_id = (int) $this->_param('business_id')) {
            $map['bao_chaoshi.business_id'] = $business_id;
            $this->assign('business_id', $business_id);
        }

        $Page = new Page($Ele->where($map)->count(), 12);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        // 查询说有的市区县名称及分类名
        $list = $Ele
            ->field('bao_chaoshi.*, bao_city.name as city_name, bao_area.area_name, bao_business.business_name')
            ->join('LEFT JOIN bao_city ON bao_chaoshi.city_id=bao_city.city_id')
            ->join('LEFT JOIN bao_area ON bao_chaoshi.area_id=bao_area.area_id')
            ->join('LEFT JOIN bao_business ON bao_chaoshi.business_id=bao_business.business_id')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list[0]);
        // update:remove begin

//        $ids = array();
//        foreach ($list as $k => $val) {
//            if ($val['user_id']) {
//                $ids[$val['user_id']] = $val['user_id'];
//            }
//        }
//        $this->assign('users', D('Users')->itemsByIds($ids));

        // update:remove end
        $this->assign('cities', M('City')->select());
        if($map['bao_chaoshi.city_id']){
            $this->assign('areas',
                M('Area')->where(array('city_id' => $map['bao_chaoshi.city_id']))->select()
            );
        }
        if($map['bao_chaoshi.area_id']){
            $this->assign('businesses',
                M('Business')->where(array('area_id' => $map['bao_chaoshi.area_id']))->select()
            );
        }
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    /**peace
     * 初始化搜索引擎，商品列表
     */
    public function initialIndex(){
        cookie(md5('ChaoshiProductSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('chaoshiproduct/index'), 1000);
    }

    /**peace
     * 初始化搜索引擎，审核列表
     */
    public function initialApply(){
        cookie(md5('ChaoshiProductSearchApplyMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('chaoshiproduct/apply'), 1000);
    }

}
