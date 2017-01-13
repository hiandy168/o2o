<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-22
 */
class HotelproductAction extends CommonAction
{ 
    public function _initialize() {
        parent::_initialize();       
    }

    /**
     * 酒店房型列表
     */
    public function index() {
        $Hotelproduct = D('HotelProduct');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件：未删除和审查通过的菜品
        $map = array('bao_hotel_product.closed' => 0, 'bao_hotel_product.audit' => 1, 'bao_hotel.closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('HotelProductSearchIndexMessage'))){
            $map = cookie(md5('HotelProductSearchIndexMessage'));
            if(cookie(md5('HotelProductSearchIndexMessageProduct'))){
                $map['bao_hotel_product.product_name'] = cookie(md5('HotelProductSearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_hotel_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_hotel_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Hotel')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if(empty($keyword)){
                unset($map['bao_hotel_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_hotel_product.store_id']);
                unset($map['bao_hotel_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_hotel_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_hotel_product.store_id'] == true){
                $store = M('Hotel')->find($map['bao_hotel_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_hotel_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('HotelProductSearchIndexMessage'), $map, 900);
        cookie(md5('HotelProductSearchIndexMessageProduct'), $map['bao_hotel_product.product_name'], 900);

        // 回显有写问题

        $count = $Hotelproduct
            ->join('LEFT JOIN bao_hotel ON bao_hotel_product.store_id=bao_hotel.store_id')
            ->where($map)
            ->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $Hotelproduct
            ->field('bao_hotel_product.*, bao_hotel.store_name')
            ->join('LEFT JOIN bao_hotel ON bao_hotel_product.store_id=bao_hotel.store_id')
//            ->join('LEFT JOIN bao_hotel_product_cate ON bao_hotel_product.cate_id=bao_hotel_product_cate.cate_id')
            ->where($map)
            ->order(array('bao_hotel_product.update_time' => 'desc'))   // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list[0]);

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    // update:remove begin

//    public function delete($room_id = 0) {
//    	if (is_numeric($room_id) && ($room_id = (int) $room_id)) {
//            $obj = D('HotelRoom');
//            if($obj->deleteById($room_id)){
//                $this->baoSuccess('删除成功！', U('hotelroom/index'));
//            }
//            $this->baoError("删除失败");
//        } else {
//            $room_id = $this->_post('room_id', false);
//            if (is_array($room_id)) {
//                $obj = D('HotelRoom');
//                foreach ($room_id as $id) {
//                    $obj->deleteById($id);
//                }
//                $this->baoSuccess('删除成功！', U('hotelroom/index'));
//            }
//            $this->baoError('请选择要删除的房间');
//        }
//    }

    // update:remove end

    /**peace
     * @param int $product_id
     * 酒店（商家审核单个及批量）删除列表
     */
    public function shiftdelete($product_id = 0) {
        // 调用私有方法
        self::setDelete($product_id, 'hotelproduct/apply', 'HotelProduct', 'product_id');
    }

    public function delete($product_id = 0) {
        // 调用私有方法
        self::setDelete($product_id, 'hotelproduct/index', 'HotelProduct', 'product_id');
    }

    /**peace
     * @param int $product_id
     * 显示酒店房间类型详情
     */
    public function detail($product_id = 0) {
        if ($product_id = (int) $product_id) {
            $obj = D('HotelProduct');
            if (!$detail = $obj->find($product_id)) {
                $this->baoError('请选择要编辑的商品');
            }
//            $eleproductcates = D('Eleproductcate')->getProductCate($detail['store_id']);
//            $this->assign('eleproductcates', $eleproductcates);
//            var_dump($eleproductcates);

            $hotel = D('Hotel')
                ->where(array('store_id'=>$detail['store_id']))
                ->find();
            $this->assign('hotel',$hotel);
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->baoError('请选择要查看的商品');
        }
    }

    /**peace
     * @param int $product_id
     * 外卖 => 商品审核列表  待审查
     */
    public function apply() {
        $Hotelproduct = D('HotelProduct');
        import('ORG.Util.Page'); // 导入分页类

        // 所有未删除且待审查的商品
        // 商品存在且待审查，对应店铺存在
        $map = array('bao_hotel_product.closed' => 0, 'bao_hotel_product.audit' => 0, 'bao_hotel.closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('HotelProductApplySearchIndexMessage'))){
            $map = cookie(md5('HotelProductApplySearchIndexMessage'));
            if(cookie(md5('HotelProductApplySearchIndexMessageProduct'))){
                $map['bao_hotel_product.product_name'] = cookie(md5('HotelProductApplySearchIndexMessageProduct'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_hotel_product.product_name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['bao_hotel_product.store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Hotel')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            if ($audit = (int) $this->_param('audit', 'intval')) {
                if($audit == 2){
                    $map['bao_hotel_product.audit'] = $audit = 0;
                }else if($audit == 3){
                    $map['bao_hotel_product.audit'] = $audit;
                }else{
                    $map['bao_hotel_product.audit'] = $audit = 0;
                }
                $this->assign('audit', $audit);
            }

            if(empty($keyword)){
                unset($map['bao_hotel_product.product_name']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['bao_hotel_product.store_id']);
                unset($map['bao_hotel_product.store_name']);
            }
        }

        // 刷新回显搜索内容
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_hotel_product.product_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['bao_hotel_product.store_id'] == true){
                $store = M('Hotel')->find($map['bao_hotel_product.store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['bao_hotel_product.store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('HotelProductApplySearchIndexMessage'), $map, 900);
        cookie(md5('HotelProductApplySearchIndexMessageProduct'), $map['bao_hotel_product.product_name'], 900);
//        var_dump($map);

        $count = $Hotelproduct
            ->join('LEFT JOIN bao_hotel ON bao_hotel_product.store_id=bao_hotel.store_id')
            ->where($map)
            ->count();   // 查询满足要求的总记录数

        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        $list = $Hotelproduct
            ->field('bao_hotel_product.*, bao_hotel.store_name, bao_hotel.phone')
            ->join('LEFT JOIN bao_hotel ON bao_hotel_product.store_id=bao_hotel.store_id')
//            ->join('LEFT JOIN bao_ele_product_cate ON bao_ele_product.cate_id=bao_ele_product_cate.cate_id')
            ->where($map)
            ->order(array('bao_hotel_product.audit' => 'ASC', 'bao_hotel_product.update_time' => 'desc'))   // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list);

        $this->assign('audit_2', $map['bao_hotel_product.audit']);
//        var_dump($map['bao_ele_product.audit']);
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }
    
    //查看
    public function examine($room_id = 0){
    	if (is_numeric($room_id) && ($room_id = (int) $room_id)) {
    		$hotelRoom = D('HotelRoom');
    		$detail = D('HotelRoom r')->field('r.*,h.store_name')
    								  ->join('bao_hotel as h on h.hotel_id=r.hotel_id')
						    		  ->where(array('room_id' =>$room_id, 'r.closed' =>$hotelRoom->closed['exist']))
						    		  ->find();		    
    		if (!$detail) {
    			$this->error('你查看的房间不存在', U('hotelroom/index'));
    		}
    		$this->assign('audit', $hotelRoom->audit);
    		$this->assign('roomCate', $hotelRoom->roomCate);
    		$this->assign('roomType', $hotelRoom->roomType);
    		$this->assign('detail', $detail);
    		$this->display();
    	} else {
    		$this->error('操作错误', U('hotelroom/index'));
    	}
    }
    
    public function check(){
    	if (IS_POST) {
    		$post = I('post.');
    		if (empty($post['id'])) {
    			$this->error('操作错误！', U('hotelroom/index'));
    		}
    		$id = intval($post['id']);
    		if (empty($post['audit'])) {
    			$this->baoError('选择审核状态');
    		}
    		$data['audit'] = $post['audit'];
    		$data['examine_mark'] = htmlspecialchars($post['examine_mark']);
    		if (D('HotelRoom')->where(array('room_id' => $id))->save($data)) {
    			$this->baoSuccess('操作成功', U('hotelroom/index'));
    		}
    		$this->error('操作错误');
    	}
    }

    public function audit($product_id = 0) {
        // 审查状态方法
        self::allAudit($product_id, 1, '该房型通过审查!');
    }

    /**peace
     * @param int $product_id
     * 外卖商品审查（单个及多个审查）
     * audit => 3      审查未通过
     */
    public function unAudit($product_id = 0) {
        // 审查状态方法
        self::allAudit($product_id, 3, '该房型未通过审查!');
    }

    /**peace
     * 商品审查公共方法
     */
    protected function allAudit($product_id, $audit, $pass){
        $obj = D('HotelProduct');
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            // 修改单个商品审查，
            // 是否存在该数据
            if(!($obj->where(array('product_id' => $product_id))->find())){
                return $this->baoError('请选择要审核的商品');
            }

            //
            $res = $obj->save(array('product_id' => $product_id, 'audit' => $audit));
            if ($res){
                return $this->baoSuccess($pass, U('hotelproduct/apply'));
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
                return $this->baoSuccess($pass, U('hotelproduct/apply'));
            }else {
                return $this->baoError('请选择要审核的商品');
            }
        }
    }

    /**peace
     * 为搜索选择酒店
     */
    public function select() {
        $Ele = M('Hotel');
        import('ORG.Util.Page'); // 导入分页类

        // 初始化的条件只有存在且以审核通过的店铺
        $map = array('bao_hotel.closed' => 0, 'bao_hotel.audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['bao_hotel.store_name|bao_hotel.phone'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if ($city_id = (int) $this->_param('city_id')) {
            $map['bao_hotel.city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['bao_hotel.area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }

        if ($business_id = (int) $this->_param('business_id')) {
            $map['bao_hotel.business_id'] = $business_id;
            $this->assign('business_id', $business_id);
        }

        $Page = new Page($Ele->where($map)->count(), 12);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        // 查询说有的市区县名称及分类名
        $list = $Ele
            ->field('bao_hotel.*, bao_city.name as city_name, bao_area.area_name, bao_business.business_name')
            ->join('LEFT JOIN bao_city ON bao_hotel.city_id=bao_city.city_id')
            ->join('LEFT JOIN bao_area ON bao_hotel.area_id=bao_area.area_id')
            ->join('LEFT JOIN bao_business ON bao_hotel.business_id=bao_business.business_id')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('cities', M('City')->select());
        if($map['bao_hotel.city_id']){
            $this->assign('areas',
                M('Area')->where(array('city_id' => $map['bao_hotel.city_id']))->select()
            );
        }
        if($map['bao_hotel.area_id']){
            $this->assign('businesses',
                M('Business')->where(array('area_id' => $map['bao_hotel.area_id']))->select()
            );
        }
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    /**peace
     * 展示酒店每个房型图片
     */
    public function pictures($product_id = 0, $store_id = 0) {
        $hotelProductModel = M('HotelProduct');

        // 商品合法性验证
        $findProduct = $hotelProductModel
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
            $photos = explode(',', $findProduct['photo']);
            $set = 3;
            $this->assign('set', $set);
            $this->assign('photos', $photos);   // 赋值数据集
            $this->display();   // 输出模板
        }
    }

    /**
    * 初始化搜索引擎，菜单列表
    */
    public function initialIndex(){
        cookie(md5('HotelProductSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('hotelproduct/index'), 1000);
    }

    /**peace
     * 初始化搜索引擎，审核列表
     */
    public function initialApply(){
        cookie(md5('HotelProductApplySearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('hotelproduct/apply'), 1000);
    }
}