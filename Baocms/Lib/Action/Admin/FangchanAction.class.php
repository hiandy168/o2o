<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/12 0012
 * Time: 上午 10:10
 */
//房产
class FangchanAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();

    }


    public function index()
    {
        $HouseModel = D('HouseStore');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('bao_house_store.closed' => 0, 'bao_house_store.level' => 2, 'bao_house_store.audit' => 1);


        // 根据提交方式判断，是否清除cookie相应信息
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_house_store.store_name'] = array('LIKE', '%' . $keyword . '%');
                // 在获取以后再赋值s
                $this->assign('keyword', $keyword);
            }
            if (!$keyword) {
                unset($map['bao_house_store.store_name']);
            }

            // 店铺类型，1 个人    2 企业
            if ($level = (int)$this->_param('level')) {
                $map['bao_house_store.level'] = $level;
                $this->assign('level', $level);
            }

            // 酒店分类判断
            if ($cate_id = (int)$this->_param('cate_id')) {
                $map['bao_house_store.cate_id'] = $cate_id;
                $this->assign('cate_id', $cate_id);
            }
            if ($cate_id == 0) {
                unset($map['bao_house_store.cate_id']);
            }

            // 酒店品牌判断
            if ($brand_id = (int)$this->_param('brand_id')) {
                $map['bao_house_store.brand_id'] = $brand_id;
                $this->assign('brand_id', $brand_id);
            }
            if ($brand_id == 0) {
                unset($map['bao_house_store.brand_id']);
            }

            if ($city_id = (int)$this->_param('city_id')) {
                $map['bao_house_store.city_id'] = $city_id;
                $this->assign('city_id', $city_id);
            }
            if ($city_id === 0) {
                unset($map['bao_house_store.city_id']);
                unset($map['bao_house_store.area_id']);
                unset($map['bao_house_store.business_id']);
            }

            if ($area_id = (int)$this->_param('area_id')) {
                $map['bao_house_store.area_id'] = $area_id;
                $this->assign('area_id', $area_id);
            }
            if ($area_id === 0) {
                unset($map['bao_house_store.area_id']);
                unset($map['bao_house_store.business_id']);
            }

            if ($business_id = (int)$this->_param('business_id')) {
                $map['bao_house_store.business_id'] = $business_id;
                $this->assign('business_id', $business_id);
            }
            if ($business_id === 0) {
                unset($map['bao_house_store.business_id']);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_house_store.store_name'][1]), 1, -1);
            if ($keyword != '') {
                $this->assign('keyword', $keyword);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('HotelSearchIndexMessage'), $map, 900);
        cookie(md5('HotelSearchIndexMessageName'), $map['bao_house_store.store_name'], 900);
        //        var_dump($map);*/

        $count = $HouseModel->where($map)->count();   // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        $list = $HouseModel
            ->field('bao_house_store.*, bao_city.name as city_name, bao_area.area_name')
            ->join('LEFT JOIN bao_city ON bao_city.city_id = bao_house_store.city_id')
            ->join('LEFT JOIN bao_area ON bao_area.area_id = bao_house_store.area_id')
            ->where($map)
            ->order(array('bao_house_store.update_time' => 'desc'))
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        // 是否选中搜索栏名
        $this->assign('city_id_2', $map['bao_house_store.city_id']);
        $this->assign('area_id_2', $map['bao_house_store.area_id']);
        $this->assign('business_id_2', $map['bao_house_store.business_id']);
        $this->assign('cate_id_2', $map['bao_house_store.cate_id']);
        $this->assign('brand_id_2', $map['bao_house_store.brand_id']);
        $this->assign('level_2', $map['bao_house_store.level']);
        //
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        // 传递市区县数据
        if (!$map['bao_house_store.city_id']) {
            $map['city_id'] = 0;
        } else {
            $map['city_id'] = $map['bao_house_store.city_id'];
        }
        if (!$map['bao_house_store.area_id']) {
            $map['area_id'] = 0;
        } else {
            $map['area_id'] = $map['bao_house_store.area_id'];
        }
        $this->assign('map', $map);
        $this->display(); // 输出模板
    }

    public function exame()
    {
        $HouseModel = D('HouseStore');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('bao_house_store.closed' => 0, 'bao_house_store.level' => 2, 'bao_house_store.audit' => 0);

        /* // 保存搜索信息到cookie中
         if (cookie(md5('HotelSearchIndexMessage'))) {
             $map = cookie(md5('HotelSearchIndexMessage'));
             if (cookie(md5('HotelSearchIndexMessageName'))) {
                 $map['bao_house_store.store_name'] = cookie(md5('HotelSearchIndexMessageName'));
             }
         }*/

        // 根据提交方式判断，是否清除cookie相应信息
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_house_store.store_name'] = array('LIKE', '%' . $keyword . '%');
                // 在获取以后再赋值s
                $this->assign('keyword', $keyword);
            }
            if (!$keyword) {
                unset($map['bao_house_store.store_name']);
            }

            // 店铺类型，1 个人    2 企业
            if ($level = (int)$this->_param('level')) {
                $map['bao_house_store.level'] = $level;
                $this->assign('level', $level);
            }
            if ($audit = (int)$this->_param('audit')) {
                $map['bao_house_store.audit'] = $audit;
                $this->assign('audit', $audit);
            }

            // 酒店分类判断
            if ($cate_id = (int)$this->_param('cate_id')) {
                $map['bao_house_store.cate_id'] = $cate_id;
                $this->assign('cate_id', $cate_id);
            }
            if ($cate_id == 0) {
                unset($map['bao_house_store.cate_id']);
            }


            if ($city_id = (int)$this->_param('city_id')) {
                $map['bao_house_store.city_id'] = $city_id;
                $this->assign('city_id', $city_id);
            }
            if ($city_id === 0) {
                unset($map['bao_house_store.city_id']);
                unset($map['bao_house_store.area_id']);
                unset($map['bao_house_store.business_id']);
            }

            if ($area_id = (int)$this->_param('area_id')) {
                $map['bao_house_store.area_id'] = $area_id;
                $this->assign('area_id', $area_id);
            }
            if ($area_id === 0) {
                unset($map['bao_house_store.area_id']);
                unset($map['bao_house_store.business_id']);
            }

            if ($business_id = (int)$this->_param('business_id')) {
                $map['bao_house_store.business_id'] = $business_id;
                $this->assign('business_id', $business_id);
            }
            if ($business_id === 0) {
                unset($map['bao_house_store.business_id']);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_house_store.store_name'][1]), 1, -1);
            if ($keyword != '') {
                $this->assign('keyword', $keyword);
            }
        }

        $count = $HouseModel->where($map)->count();   // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        $list = $HouseModel
            ->field('bao_house_store.*, bao_city.name as city_name, bao_area.area_name')
            ->join('LEFT JOIN bao_city ON bao_city.city_id = bao_house_store.city_id')
            ->join('LEFT JOIN bao_area ON bao_area.area_id = bao_house_store.area_id')
            ->where($map)
            ->order(array('bao_house_store.update_time' => 'desc'))
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        // 是否选中搜索栏名
        $this->assign('city_id_2', $map['bao_house_store.city_id']);
        $this->assign('area_id_2', $map['bao_house_store.area_id']);
        $this->assign('business_id_2', $map['bao_house_store.business_id']);
        $this->assign('cate_id_2', $map['bao_house_store.cate_id']);
        $this->assign('brand_id_2', $map['bao_house_store.brand_id']);
        $this->assign('level_2', $map['bao_house_store.level']);

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        // 传递市区县数据
        if (!$map['bao_house_store.city_id']) {
            $map['city_id'] = 0;
        } else {
            $map['city_id'] = $map['bao_house_store.city_id'];
        }
        if (!$map['bao_house_store.area_id']) {
            $map['area_id'] = 0;
        } else {
            $map['area_id'] = $map['bao_house_store.area_id'];
        }
        $this->assign('map', $map);
        $this->display(); // 输出模板
    }

    /**peace
     * 酒店店铺审核
     */
    public function apply()
    {
        $HotelModel = D('Hotel');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('bao_house_store.closed' => 0, 'bao_house_store.audit' => 0, 'bao_house_store.level' => 2);

        // 保存搜索信息到cookie中
        if (cookie(md5('HotelSearchApplyMessage'))) {
            $map = cookie(md5('HotelSearchApplyMessage'));
            if (cookie(md5('HotelSearchApplyMessageName'))) {
                $map['bao_house_store.store_name'] = cookie(md5('HotelSearchApplyMessageName'));
            }
        }

        // 根据提交方式判断，是否清除cookie相应信息
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['bao_house_store.store_name'] = array('LIKE', '%' . $keyword . '%');
                // 在获取以后再赋值
                $this->assign('keyword', $keyword);
            }
            if (!$keyword) {
                unset($map['bao_house_store.store_name']);
            }

            // 店铺类型，1 个人    2 企业
            if ($level = (int)$this->_param('level')) {
                $map['bao_house_store.level'] = $level;
                $this->assign('level', $level);
            }

            // 店铺类型，0未审查    2 未通过审查
            if ($audit = (int)$this->_param('audit')) {
                if ($audit == 1) {
                    $audit = 0;
                }
                $map['bao_house_store.audit'] = $audit;
                $this->assign('audit', $audit);
            }

            // 酒店分类判断
            if ($cate_id = (int)$this->_param('cate_id')) {
                $map['bao_house_store.cate_id'] = $cate_id;
                $this->assign('cate_id', $cate_id);
            }
            if ($cate_id == 0) {
                unset($map['bao_house_store.cate_id']);
            }

            // 酒店品牌判断
            if ($brand_id = (int)$this->_param('brand_id')) {
                $map['bao_house_store.brand_id'] = $brand_id;
                $this->assign('brand_id', $brand_id);
            }
            if ($brand_id == 0) {
                unset($map['bao_house_store.brand_id']);
            }

            if ($city_id = (int)$this->_param('city_id')) {
                $map['bao_house_store.city_id'] = $city_id;
                $this->assign('city_id', $city_id);
            }
            if ($city_id === 0) {
                unset($map['bao_house_store.city_id']);
                unset($map['bao_house_store.area_id']);
                unset($map['bao_house_store.business_id']);
            }

            if ($area_id = (int)$this->_param('area_id')) {
                $map['bao_house_store.area_id'] = $area_id;
                $this->assign('area_id', $area_id);
            }
            if ($area_id === 0) {
                unset($map['bao_house_store.area_id']);
                unset($map['bao_house_store.business_id']);
            }

            if ($business_id = (int)$this->_param('business_id')) {
                $map['bao_house_store.business_id'] = $business_id;
                $this->assign('business_id', $business_id);
            }
            if ($business_id === 0) {
                unset($map['bao_house_store.business_id']);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_house_store.store_name'][1]), 1, -1);
            if ($keyword != '') {
                $this->assign('keyword', $keyword);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('HotelSearchApplyMessage'), $map, 900);
        cookie(md5('HotelSearchApplyMessageName'), $map['bao_house_store.store_name'], 900);
//        var_dump($map);

        $count = $HotelModel->where($map)->count();   // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出
        // 根据更新时间升序
        $list = $HotelModel
            ->field('bao_house_store.*, bao_users.nickname, bao_users.mobile, bao_area.area_name, bao_city.name as city_name, bao_hotel_cate.cate_name, bao_hotel_brand.brand_name')
            ->join('LEFT JOIN bao_shop ON bao_shop.shop_id = bao_house_store.shop_id')
            ->join('LEFT JOIN bao_users ON bao_users.user_id = bao_shop.user_id')
            ->join('LEFT JOIN bao_city ON bao_city.city_id = bao_house_store.city_id')
            ->join('LEFT JOIN bao_area ON bao_area.area_id = bao_house_store.area_id')
            ->join('LEFT JOIN bao_hotel_cate ON bao_hotel_cate.cate_id = bao_house_store.cate_id')
            ->join('LEFT JOIN bao_hotel_brand ON bao_hotel_brand.brand_id = bao_house_store.brand_id')
//            ->join('LEFT JOIN bao_hotel_cate ON bao_hotel_cate.cate_id = bao_house_store.cate_id')
            ->order(array('bao_house_store.audit' => 'ASC', 'bao_house_store.update_time' => 'ASC'))
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        // 是否选中搜索栏名
        $this->assign('audit_2', $map['bao_house_store.audit']);
        $this->assign('city_id_2', $map['bao_house_store.city_id']);
        $this->assign('area_id_2', $map['bao_house_store.area_id']);
        $this->assign('business_id_2', $map['bao_house_store.business_id']);
        $this->assign('cate_id_2', $map['bao_house_store.cate_id']);
        $this->assign('brand_id_2', $map['bao_house_store.brand_id']);
        $this->assign('level_2', $map['bao_house_store.level']);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        // 传递市区县数据
        // 传递市区县数据
        if (!$map['bao_house_store.city_id']) {
            $map['city_id'] = 0;
        } else {
            $map['city_id'] = $map['bao_house_store.city_id'];
        }
        if (!$map['bao_house_store.area_id']) {
            $map['area_id'] = 0;
        } else {
            $map['area_id'] = $map['bao_house_store.area_id'];
        }
        $this->assign('map', $map);
        $this->display(); // 输出模板
    }

    /**
     * @param int $store_id
     * @param int $level
     * 查看商家详情
     */
    public function detail($store_id = 0, $level = 0)
    {
        if ($store_id = (int)$store_id) {
            $obj = D('Hotel');
            if (!$detail = $obj->find($store_id)) {
                $this->baoError('请选择要查看的商家');
            }
            if ($level == 1) {
                // 个人用户申请的店铺
                // 查询个人店铺的身份证照片
                $pics = M('Hotel')
                    ->join('LEFT JOIN bao_shop ON bao_house_store.shop_id=bao_shop.shop_id')
                    ->join('LEFT JOIN bao_users ON bao_shop.user_id=bao_users.user_id')
                    ->join('LEFT JOIN bao_presonal_store_open_auth ON bao_presonal_store_open_auth.uid=bao_users.user_id')
                    ->field('bao_presonal_store_open_auth.id_face, bao_presonal_store_open_auth.id_back')
                    ->where(array('store_id' => $store_id))
                    ->find();
                $detail['id_face'] = $pics['id_face'];
                $detail['id_back'] = $pics['id_back'];
                $shufflings = explode('","', substr($detail['store_imgs'], 2, -2));
//                var_dump($detail['store_imgs']);

                $detail['intro'] = htmlspecialchars_decode($detail['intro']);
                $detail['bulletin'] = htmlspecialchars_decode($detail['bulletin']);
                $detail['exame_explain'] = htmlspecialchars_decode($detail['exame_explain']);
                $this->assign('detail', $detail);
                $this->assign('shufflings', $shufflings);
                $this->display();

            } elseif ($level == 2) {
                // 企业用户申请的店铺
                // 查询企业店铺营业执照
                $pics = M('Com_store_open_auth')
                    ->field('business_license, other_pic')
                    ->where(array('store_id' => $store_id, 'store_class_id' => 4))
                    ->find();
                $detail['business_license'] = $pics['business_license'];
                if (!$pics['other_pic']) {
                    $detail['other_pic'] = 0;
                }
                $pics = array_filter(explode(',', $pics['other_pic']));
//                var_dump($detail['store_imgs']);

                $this->assign('detail', $detail);
                $this->assign('pics', $pics);
                $this->display();

            } else {

                $this->baoError('请选择要查看的商家');
            }
        } else {
            $this->baoError('请选择要查看的商家');
        }
    }

    // update:remove begin

    //商家审核
//    public function exame() {
//        $meishi = D('Hotel');
//        $shop_mod = D('Shop');
//        import('ORG.Util.Page'); // 导入分页类
//        $map = array('audit'=>array('neq',1));
//        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
//            $map['store_name'] = array('LIKE', '%' . $keyword . '%');
//            $this->assign('keyword', $keyword);
//        }
//
//        if ($area_id = (int) $this->_param('area_id')) {
//            $map['area_id'] = $area_id;
//            $this->assign('area_id', $area_id);
//        }
//        $count = $meishi->where($map)->count(); // 查询满足要求的总记录数
//        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
//        $show = $Page->show(); // 分页显示输出
//        $list = $meishi->where($map)->order(array('addtime' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
//         $ids = array();
//
//        foreach ($list as $k => &$val) {
//
//            if ($val['shop_id']) {
//                $user_id = $shop_mod->where(array('shop_id'=>$val['shop_id']))->getField('user_id');
//                $ids[$user_id] = $user_id;
//                $val['user_id']=$user_id;
//
//            }
//
//        }
//
//        $this->assign('users', $users = D('Users')->itemsByIds($ids));
//        $this->assign('list', $list); // 赋值数据集
//        $this->assign('page', $show); // 赋值分页输出
//        $this->assign('areas', D('Area')->fetchAll());
//        $this->assign('business', D('Business')->fetchAll());
//        $this->assign('citys', D('City')->fetchAll());
//        $this->display(); // 输出模板
//    }

    // update:remove end

    public function exame2($store_id = 0)
    {
        if ($store_id = (int)$store_id) {
            $obj = D('Hotel');
            if (!$this->house_info = $obj->find($store_id)) {
                $this->baoError('请选择要审核的商家');
            }
            $this->shop_info = D('Shop')->where(array('shop_id' => $this->house_info['shop_id']))->find();
            $this->member_info = D('Users')->find($this->shop_info['user_id']);
            if ($this->house_info['level'] == 1) {
                $this->presonl_exame();
            } else {
                $this->com_exame();
            }
        } else {
            $this->baoError('请选择要编辑的商家');
        }
    }

    public function edit($store_id = 0)
    {
        if ($store_id = (int)$store_id) {
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
    public function exame_see($store_id = 0)
    {
        if ($store_id = (int)$store_id) {
            $obj = D('HouseStore');
            if (!$this->house_info = $obj->find($store_id)) {
                $this->baoError('请选择要审核的商家');
            }
            $this->shop_info = D('shop')->where(array('shop_id' => $this->house_info['shop_id']))->find();
            $this->member_info = D('Users')->find($this->house_info['user_id']);
            if ($this->house_info['level'] == 1) {
                $this->presonl_exame();
            } else {
                $this->com_exame();
            }
        } else {
            $this->baoError('请选择要编辑的美食商家');
        }
    }

    public function presonl_exame()
    {
        $auth_info = D('presonal_store_open_auth')->find($this->shop_info['user_id']);
        if (IS_POST) {
            if (D('HouseStore')->save($_POST['data'])) {
                if ($_POST['data']['audit'] == '2') {
                    D('Sms')->sendSms('hotel_open_err', $this->member_info['mobile'], array('username' => $this->member_info['nickname']));
                } elseif ($_POST['data']['audit'] == '1') {
                    //更新认证状态
                    D('presonal_store_open_auth')->where(array('uid' => $this->shop_info['user_id']))->save(array('auth' => 1));
                    //更新商家状态
                    // D('shop')->where(array('shop_id'=>$this->shop_info['shop_id']))->save(array('audit'=>1));
                    //检测我佣有的店铺
                    $my_store = D('MyHaveStore')->where(array('uid' => $this->shop_info['user_id'], 'sc_id' => 4))->count();
                    if (!$my_store) {
                        D('MyHaveStore')->add(array('uid' => $this->shop_info['user_id'], 'sc_id' => 4));
                    }
                    D('Sms')->sendSms('hotel_open_ok', $this->member_info['mobile'], array('username' => $this->member_info['nickname'], 'shopname' => $this->chaoshi_info['shop_name']));
                }
                $this->baoSuccess('审核成功', U('fangchan/exame'));
            } else {
                $this->baoSuccess('修改成功', U('fangchan/exame'));
            }
        }
        $this->detail = $auth_info;
        $this->display('presonl_exame');
    }

    public function com_exame()
    {
        $auth_info = D('ComStoreOpenAuth')->where(array('store_id' => $this->house_info['store_id'], 'store_class_id' => 5))->find();
        // var_dump($_POST);die;
        if (IS_POST) {
            if (D('HouseStore')->save($_POST['data'])) {
                if ($_POST['data']['audit'] == '2') {
                    D('Sms')->sendSms('house_open_err', $this->member_info['mobile'], array('username' => $this->member_info['nickname']));
                } elseif ($_POST['data']['audit'] == '1') {
                    //更新认证状态
                    D('ComStoreOpenAuth')->where(array('store_id' => $this->house_info['store_id'], 'store_class_id' => 5))->save(array('audit' => 1));
                    //更新商家状态
                    //D('shop')->where(array('shop_id'=>$this->shop_info['shop_id']))->save(array('audit'=>1));
                    //检测我佣有的店铺
                    $my_store = D('MyHaveStore')->where(array('uid' => $this->shop_info['user_id'], 'sc_id' => 5))->count();
                    if (!$my_store) {
                        D('MyHaveStore')->add(array('uid' => $this->shop_info['user_id'], 'sc_id' => 5));
                    }
                    D('Sms')->sendSms('house_open_ok', $this->member_info['mobile'], array('username' => $this->member_info['nickname'], 'shopname' => $this->ele_info['shop_name']));
                }

                $this->baoSuccess('审核成功', U('Fangchan/exame'));
            } else {
                $this->baoError('无操作');
            }
        }
        $auth_info['other_pic'] = explode(",", $auth_info['other_pic']);
        $this->detail = $auth_info;
        $this->display('com_exame');
    }

    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        $data['is_open'] = (int)$data['is_open'];
        $data['is_pay'] = (int)$data['is_pay'];
        $data['is_fan'] = (int)$data['is_fan'];
        $data['is_new'] = (int)$data['is_new'];
        $data['sold_num'] = (int)$data['sold_num'];
        $data['month_num'] = (int)$data['month_num'];
        $data['distribution'] = (int)$data['distribution'];
        $data['audit'] = (int)$data['audit'];
        $data['intro'] = htmlspecialchars($data['intro']);
        $data['rate'] = (int)$data['rate'];
        if (empty($data['intro'])) {
            $this->baoError('说明不能为空');
        }
        $data['orderby'] = (int)$data['orderby'];
        return $data;
    }

    // update:remove begin

//    public function delete($hotel_id = 0) {
//    	if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
//            $obj = D('Hotel');
//            if ($obj->deleteAll($hotel_id)) {
//            	$this->baoSuccess('删除成功！', U('jiudian/index'));
//            }
//            $this->baoError('删除失败！', U('jiudian/index'));
//        } else {
//            $hotel_id = $this->_post('hotel_id', false);
//            if (is_array($hotel_id)) {
//                $obj = D('Hotel');
//                foreach ($hotel_id as $id) {
//	                if (!$obj->deleteAll($id)) {
//	            		$this->baoError('删除失败！', U('jiudian/index'));
//	            	}
//	            }
//                $this->baoSuccess('删除成功！', U('jiudian/index'));
//            }
//            $this->baoError('请选择要删除的酒店');
//        }
//    }

    // update:remove end

    /**peace
     * @param int $store_id
     *
     */
    public function delete($store_id = 0)
    {
        // 调用私有方法
        self::setDelete($store_id, 'hotel/index', 'Hotel', 'store_id');
    }

    /**peace
     * @param int $store_id
     *
     */
    public function shiftdelete($store_id = 0)
    {
        // 调用私有方法
        self::setDelete($store_id, 'fangchan/index', 'HouseStore', 'store_id');
    }

    public function opened($hotel_id = 0, $type = 'open')
    {
        if (is_numeric($hotel_id) && ($hotel_id = (int)$hotel_id)) {


            $obj = D('Hotel');
            $is_open = 0;
            if ($type == 'open') {
                $is_open = 1;
            }
            $obj->save(array('hotel_id' => $hotel_id, 'is_open' => $is_open));

            $this->baoSuccess('操作成功！', U('jiudian/index'));
        }
    }

    protected function cate($k)
    {
        $data = D('Hotel')->HotelCate();
        return $data[$k];
    }

    /**peace
     * 获取酒店的分类
     */


    /**peace
     * 获取酒店的品牌
     */
    public function getBrand()
    {
        $brands = M('HotelBrand')
            ->field('brand_name, brand_id')
            ->order('order_by ASC')
            ->select();
        echo json_encode(array('data' => $brands, 'error' => '200'));
    }

    /**peace
     * 获取酒店的品牌
     */
    public function getCity()
    {
        $city = M('City')
            ->field('name as city_name, city_id')
            ->select();
        echo json_encode(array('data' => $city, 'error' => '200'));
    }

    /**peace
     * 获取区县信息
     */
    public function getArea($city_id = 0)
    {
        $area = M('Area')
            ->field('area_name, area_id')
            ->where(array('city_id' => $city_id))
            ->select();
        echo json_encode(array('data' => $area, 'error' => '200'));
    }

    /**peace
     * 获取商圈信息
     */
    public function getBusiness($area_id = 0)
    {
        $business = M('Business')
            ->field('business_name, business_id')
            ->where(array('area_id' => $area_id))
            ->select();
        echo json_encode(array('data' => $business, 'error' => '200'));
    }

    protected function type($k)
    {
        $data = D('Hotel')->HotelBrand;
        return $data[$k];
    }

    /*
     * 设为首页推荐
     * 作者：刘弢
     */

    public function to_home()
    {
        $store_id = I('store_id', '0', 'intval');
        if (!$store_id) {
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
            $this->baoSuccess('设置成功', U('jiudian/index'));
        } else {
            $this->error('设置失败');
        }
    }

    /*
     * 取消首页推荐
     * 作者：刘弢
     */
    public function cancel_home()
    {
        $store_id = I('store_id', '0', 'intval');
        if (!$store_id) {
            $this->baoError("参数错误");
        }
        $hotel_model = D('Hotel');
        $home_store_model = D('HomeStore');
        $res = $home_store_model->cancel_home($store_id, 'hotel');
        if ($res) {
            $this->baoSuccess('取消成功', U('jiudian/index'));
        } else {
            $this->baoError('取消失败');
        }
    }

    //酒店查询
    public function select()
    {
        $hotelModel = D('HouseStore');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['store_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int)$this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int)$this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }

        $count = $hotelModel
            ->join('bao_area on bao_house_store.area_id = bao_area.area_id', 'left')
            ->join('bao_city on bao_house_store.city_id = bao_city.city_id', 'left')
            ->join('bao_business on  bao_house_store.business_id = bao_business.business_id', 'left')
            ->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $hotelModel
            ->join('bao_area on bao_house_store.area_id = bao_area.area_id', 'left')
            ->join('bao_city on bao_house_store.city_id = bao_city.city_id', 'left')
            ->join('bao_business  on bao_house_store.business_id = bao_business.business_id', 'left')
            ->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('cities', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**peace
     * 设置为店铺整顿
     */
    public function reorganize()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = I('status',1);
            $this->reorganizeStore(I('post.store_id'), $status, 'HouseStore', 'Fangchan/index');
        } else {
            $status = I('status',1);
            $this->reorganizeStore(I('get.store_id'), $status, 'HouseStore', 'Fangchan/index');
        }
    }

    /**peace
     * 取消店铺整顿，变为正常
     */
    public function cancelReorganize()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 调用公共方法  I('store_id', '0', 'intval'), I('type'), 'Hotel'
            $this->reorganizeStore(I('post.store_id'), 0, 'Hotel');
        } else {
            $this->reorganizeStore(I('get.store_id'), 0, 'Hotel');
        }
    }

    /**peace
     * 初始化搜索引擎，商家列表
     */
    public function initialIndex()
    {
        cookie(md5('HotelSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('hotel/index'), 1000);
    }

    /**peace
     * 初始化搜索引擎，审核列表
     */
    public function initialApply($type)
    {
        cookie(md5('HotelSearchApplyMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U("fangchan/$type"), 1000);
    }


    public function rent_list()
    {
        import('ORG.Util.Page'); // 导入分页类
        $map = array();

        $map['a.closed'] = 0;
        $map['a.audit'] = 0;
        if (IS_POST) {
            $store_name = I('post.store_name', 0);
            if ($store_name) {
                $map['store_name'] = array('like', "%$store_name%");
            }
            $keyword = I('post.keyword', 0);
            if ($keyword) {
                $map['product_name'] = array('like', "%$keyword%");
            }
            $audit = I('post.audit', '');
            if ($audit != '') {
                $map['a.audit'] = $audit;
            }
        }
        $count = D('HouseRent a')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')->where($map)->count();   // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出

        $list = D('HouseRent a')
            ->field('a.product_id, a.product_name,b.store_name, a.audit, a.photo')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        // 是否选中搜索栏名
        $this->assign('city_id_2', $map['bao_house_store.city_id']);
        $this->assign('area_id_2', $map['bao_house_store.area_id']);
        $this->assign('business_id_2', $map['bao_house_store.business_id']);
        $this->assign('cate_id_2', $map['bao_house_store.cate_id']);
        $this->assign('brand_id_2', $map['bao_house_store.brand_id']);
        $this->assign('level_2', $map['bao_house_store.level']);
        //
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function rent_info($product_id)
    {
        $info = D('HouseRent a')
            ->field('a.*,b.store_name')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')
            ->where("a.product_id = $product_id")
            ->find();

        $this->assign('detail', $info)->display();
    }

    public function two_list()
    {
        import('ORG.Util.Page'); // 导入分页类
        $map = array();

        $map['a.closed'] = 0;
        $map['a.audit'] = 0;
        if (IS_POST) {
            $store_name = I('post.store_name', 0);
            if ($store_name) {
                $map['store_name'] = array('like', "%$store_name%");
            }
            $keyword = I('post.keyword', 0);
            if ($keyword) {
                $map['product_name'] = array('like', "%$keyword%");
            }
            $audit = I('post.audit', '');
            if ($audit != '') {
                $map['a.audit'] = $audit;
            }
            $this->assign('audit_2',$audit);
        }
        $count = D('HouseTwo a')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')->where($map)->count();   // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出

        $list = D('HouseTwo a')
            ->field('a.product_id, a.product_name,b.store_name, a.audit, a.photo')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        // 是否选中搜索栏名
        $this->assign('city_id_2', $map['bao_house_store.city_id']);
        $this->assign('area_id_2', $map['bao_house_store.area_id']);
        $this->assign('business_id_2', $map['bao_house_store.business_id']);
        $this->assign('cate_id_2', $map['bao_house_store.cate_id']);
        $this->assign('brand_id_2', $map['bao_house_store.brand_id']);
        $this->assign('level_2', $map['bao_house_store.level']);
        //
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function two_info($product_id)
    {
        $info = D('HouseTwo a')
            ->field('a.*,b.store_name')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')
            ->where(array('a.product_id' => $product_id))
            ->find();
        $this->assign('detail', $info)->display();
    }

    public function new_list()
    {
        import('ORG.Util.Page'); // 导入分页类
        $map = array();

        $map['a.closed'] = 0;
        $map['a.audit'] = 0;
        if (IS_POST) {
            $store_name = I('post.store_name', 0);
            if ($store_name) {
                $map['store_name'] = array('like', "%$store_name%");
            }
            $keyword = I('post.keyword', 0);
            if ($keyword) {
                $map['product_name'] = array('like', "%$keyword%");
            }
            $audit = I('post.audit', '');
            $this->assign('audit_2',$audit);
            if ($audit != '') {
                $map['a.audit'] = $audit;
            }
        }
        $count = D('House a')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')->where($map)->count();   // 查询满足要求的总记录数
        $Page = new Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出

        $picInfo = D('HousePics')->field('product_id,min(pic) as photo')->group('product_id')->select(false);
        $list = D('House a')
            ->field('a.product_id, a.product_name,b.store_name, a.audit,e.photo')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')
            ->join('left join ' . $picInfo . ' e on a.product_id = e.product_id')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        // 是否选中搜索栏名
        $this->assign('city_id_2', $map['bao_house_store.city_id']);
        $this->assign('area_id_2', $map['bao_house_store.area_id']);
        $this->assign('business_id_2', $map['bao_house_store.business_id']);
        $this->assign('cate_id_2', $map['bao_house_store.cate_id']);
        $this->assign('brand_id_2', $map['bao_house_store.brand_id']);
        $this->assign('level_2', $map['bao_house_store.level']);
        //
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function new_info($product_id)
    {
        $picInfo = D('HousePics')->field('product_id,min(pic) as photo')->group('product_id')->select(false);
        $info = D('House a')
            ->field('a.*,b.store_name,e.photo')
            ->join(C('DB_PREFIX') . 'house_store b on a.store_id = b.store_id', 'left')
            ->join(C('DB_PREFIX') . 'shop c on b.shop_id = c.shop_id', 'left')
            ->join(C('DB_PREFIX') . 'users d on c.user_id = d.user_id', 'left')
            ->join('left join ' . $picInfo . ' e on a.product_id = e.product_id')
            ->where("a.product_id = $product_id")
            ->find();
        $this->assign('detail', $info)->display();
    }


    public function active_info()
    {

        if (IS_POST) {
            $data = I('post.');
            if ('active' == $data['type']) {
                if (D('HouseActive')->where(array('id' => $data['id']))->save(array('closed'=>0,'audit' => $data['audit'], 'exame_explain' => $data['exame_explain']))) {
                    $this->success('审核成功');
                } else {
                    $this->error('审核成功');
                }
            } else if ('open_info' == $data['type']) {
                if (D('HouseOpenInfo')->where(array('id' => $data['id']))->save(array('closed'=>0,'audit' => $data['audit'], 'exame_explain' => $data['exame_explain']))) {
                    $this->success('审核成功');
                } else {
                    $this->error('审核成功');
                }
            } else {
                $this->error('类型未知');
            }
        } else {
            $product_id = I('product_id', 0, 'intval');
            if (!$product_id) {
                $this->error('id必须');
            }
            $ActiveInfo = D('HouseActive')
                ->where("product_id = $product_id")
                ->select();

            $HouseOpenInfo = D('HouseOpenInfo')
                ->where("product_id = $product_id")
                ->select();
            $this->assign('ActiveInfo', $ActiveInfo)->assign('HouseOpenInfo', $HouseOpenInfo)->display();
        }


    }

    public function audit($product_id = 0, $type = 'two')
    {
        $post_type=I('get.type',0);
        if($post_type){
            $type = $post_type;
        }
        // 审查状态方法
        $this->allAudit($product_id, 1, '该信息通过审查!', $type);
    }


    public function unAudit($product_id = 0, $type = 'two')
    {
        $post_type=I('get.type',0);
        if($post_type){
            $type = $post_type;
        }
        // 审查状态方法
        $this->allAudit($product_id, 3, '该信息未通过审查!', $type);
    }

    /**peace
     * 商品审查公共方法
     */
    protected function allAudit($product_id, $audit, $pass, $type)
    {
        switch ($type) {
            case 'two':
                $obj = D('HouseTwo');
                break;
            case 'new':
                $obj = D('House');
                break;
            case 'rent':
                $obj = D('HouseRent');
                break;
            default:
                return $this->baoError('类型错误');
        }
        if (is_numeric($product_id) && ($product_id = (int)$product_id)) {
            // 修改单个商品审查，
            // 是否存在该数据
            if (!($obj->where(array('product_id' => $product_id))->find())) {
                return $this->baoError('请选择要审核的商品');
            }
            $res = $obj->save(array('product_id' => $product_id, 'audit' => $audit));
            if ($res) {
                return $this->baoSuccess($pass, U("Fangchan/{$type}_list"));
            } else {
                return $this->baoError($pass);
            }
        } else {

            // 修改多个商品审查，
            $product_id = $this->_post('product_id', false);
            if (is_array($product_id)) {
                foreach ($product_id as $id) {
                    // 验证是否存在该条信息
                    $rst = $obj
                        ->where(array('product_id' => $id))
                        ->find();
                    if (!$rst) {
                        return $this->baoError('请选择要审核的商品');
                    }
                    $obj->save(array('product_id' => $id, 'audit' => $audit));
                }
                return $this->baoSuccess($pass, U("Fangchan/{$type}_list"));
            } else {
                return $this->baoError('请选择要审核的商品');
            }
        }
    }


    public function info_delete($type, $product_id = 0)
    {
        switch ($type) {
            case 'two':
                $returnUrl = 'two_list';
                $oprModel = D('HouseTwo');
                break;
            case 'new':
                $returnUrl = 'new_list';
                $oprModel = D('House');
                break;
            case 'rent':
                $returnUrl = 'rent_list';
                $oprModel = D('HouseRent');
                break;
            default:
                return $this->baoError('类型错误');
        }
        if (!$product_id) {
            return $this->baoError('缺少id');
        }
        if ($oprModel->where(array('product_id' => $product_id))->save(array('closed' => 1))) {
            return $this->baoSuccess('操作成功', $returnUrl);
        } else {
            return $this->baoError('操作错误');
        }
    }

    public function batch_info_delete()
    {
        $type = I('get.type', 0);
        $product_id = I('product_id', 0);
        switch ($type) {
            case 'two':
                $returnUrl = 'two_list';
                $oprModel = D('HouseTwo');
                break;
            case 'new':
                $returnUrl = 'new_list';
                $oprModel = D('House');
                break;
            case 'rent':
                $returnUrl = 'rent_list';
                $oprModel = D('HouseRent');
                break;
            default:
                return $this->baoError('类型错误');
        }
        if (!$product_id) {
            return $this->baoError('缺少id');
        }
        if ($oprModel->where(array('product_id' => array('in', $product_id)))->save(array('closed' => 1))) {
            return $this->baoSuccess('操作成功', $returnUrl);
        } else {
            return $this->baoError('操作错误');
        }
    }
}