<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiAction extends CommonAction {

    private $create_fields = array('shop_id', 'cate', 'distribution', 'is_open', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'audit', 'orderby', 'rate','distance','phone','address');
    private $edit_fields = array('is_open', 'cate', 'distribution', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'orderby', 'audit', 'rate','distance','phone','address');

    public function _initialize() {
        parent::_initialize();
        $getChaoshiCate = D('Chaoshi')->getChaoshiCate();
        $this->assign('getChaoshiCate', $getChaoshiCate);
    }

    /**
     * 显示所有有审查已通过的
     */
    public function index() {
//        phpinfo();
        $Chaoshi = D('Chaoshi');

        import('ORG.Util.Page'); // 导入分页类
        $map = array('bao_chaoshi.closed' => $Chaoshi->flag['exist'], 'bao_chaoshi.audit' => 1, 'bao_chaoshi.level' => 2);

        // 保存搜索信息到cookie中
        if(cookie(md5('ChaoshiSearchIndexMessage'))){
            $map = cookie(md5('ChaoshiSearchIndexMessage'));
            if(cookie(md5('ChaoshiSearchIndexMessageName'))){
                $map['bao_chaoshi.store_name'] = cookie(md5('ChaoshiSearchIndexMessageName'));
            }
        }

        // 根据提交方式判断，是否清除cookie相应信息
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')){
                $map['bao_chaoshi.store_name'] = array('LIKE', '%' . $keyword . '%');
                // 在获取以后再赋值s
                $this->assign('keyword', $keyword);
            }
            if(!$keyword){
                unset($map['bao_chaoshi.store_name']);
            }

            // 店铺类型，1 个人    2 企业
            if ($level = (int) $this->_param('level')) {
                $map['bao_chaoshi.level'] = $level;
                $this->assign('level', $level);
            }

            if ($city_id = (int) $this->_param('city_id')) {
                $map['bao_chaoshi.city_id'] = $city_id;
                $this->assign('city_id', $city_id);
            }
            if($city_id === 0){
                unset($map['bao_chaoshi.city_id']);
                unset($map['bao_chaoshi.area_id']);
                unset($map['bao_chaoshi.business_id']);
            }

            if ($area_id = (int) $this->_param('area_id')) {
                $map['bao_chaoshi.area_id'] = $area_id;
                $this->assign('area_id', $area_id);
            }
            if($area_id === 0){
                unset($map['bao_chaoshi.area_id']);
                unset($map['bao_chaoshi.business_id']);
            }

            if ($business_id = (int) $this->_param('business_id')) {
                $map['bao_chaoshi.business_id'] = $business_id;
                $this->assign('business_id', $business_id);
            }
            if($business_id === 0){
                unset($map['bao_chaoshi.business_id']);
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['bao_chaoshi.store_name'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('ChaoshiSearchIndexMessage'), $map, 900);
        cookie(md5('ChaoshiSearchIndexMessageName'), $map['bao_chaoshi.store_name'], 900);
//        var_dump($map);

        $count = $Chaoshi
            ->where($map)
            ->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        // 使用的表单有
        // bao_chaoshi => 超市详情表
        // bao_shop => 商家详情表
        // bao_users => 用户表
        // bao_city => 城市表
        $list = $Chaoshi
            ->field('bao_chaoshi.*, bao_city.name as city_name, bao_area.area_name')
            ->join('LEFT JOIN bao_city ON bao_city.city_id = bao_chaoshi.city_id')
            ->join('LEFT JOIN bao_area ON bao_area.area_id = bao_chaoshi.area_id')
            ->where($map)
            ->order(array('update_time' => 'desc'))  // 根据更新时间降序
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list[1]);

        // 是否选中搜索栏名
        $this->assign('city_id_2', $map['bao_chaoshi.city_id']);
        $this->assign('area_id_2', $map['bao_chaoshi.area_id']);
        $this->assign('business_id_2', $map['bao_chaoshi.business_id']);
        $this->assign('level_2', $map['bao_chaoshi.level']);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('cities', M('City')->cache(true, '60', 'xcache')->select());
        if($map['bao_chaoshi.city_id']){
            $this->assign('areas',
                M('Area')->where(array('city_id' => $map['bao_chaoshi.city_id']))->cache(true, '60', 'xcache')->select()
            );
        }
        if($map['bao_chaoshi.area_id']){
            $this->assign('businesses',
                M('Business')->where(array('area_id' => $map['bao_chaoshi.area_id']))->cache(true, '60', 'xcache')->select()
            );
        }
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Chaoshi');       
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('chaoshi/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('ID不能为空');
        }
        if (!$shop = D('Shop')->find($data['shop_id'])) {
            $this->baoError('商家不存在');
        }
        $data['store_name'] = $shop['shop_name'];
        $data['lng'] = $shop['lng'];
        $data['lat'] = $shop['lat'];
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['logo'] = $shop['logo'];

        $data['logistics'] = (int)$data['logistics'];
        $data['since_money'] = (int)$data['since_money'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['rate'] = (int) $data['rate'];
        $data['audit'] = (int) $data['audit'];
        $data['distribution'] = (int) $data['distribution'];
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('说明不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    /**
     * @param int $store_id
     * @param int $level
     * 查看商家详情
     */
    public function detail($store_id = 0, $level = 0) {
        if ($store_id = (int) $store_id) {
            $obj = D('Chaoshi');
            if (!$detail = $obj->find($store_id)) {
                $this->baoError('请选择要查看的超市商家');
            }
            if($level == 1){
                // 个人用户申请的店铺
                // 查询个人店铺的身份证照片
                $pics = M('Chaoshi')
                    ->join('LEFT JOIN bao_shop ON bao_chaoshi.shop_id=bao_shop.shop_id')
                    ->join('LEFT JOIN bao_users ON bao_shop.user_id=bao_users.user_id')
                    ->join('LEFT JOIN bao_presonal_store_open_auth ON bao_presonal_store_open_auth.uid=bao_users.user_id')
                    ->field('bao_presonal_store_open_auth.id_face, bao_presonal_store_open_auth.id_back')
                    ->where(array('store_id' => $store_id))
                    ->find();
                $detail['id_face'] = $pics['id_face'];
                $detail['id_back'] = $pics['id_back'];

                $this->assign('detail', $detail);
                $this->display();

            }elseif ($level == 2){
                // 企业用户申请的店铺
                // 查询企业店铺营业执照
                $pics = M('Com_store_open_auth')
                    ->field('business_license, other_pic')
                    ->where(array('store_id' => $store_id, 'store_class_id' => 1))
                    ->find();
                $detail['business_license'] = $pics['business_license'];
                if(!$pics['other_pic']){
                    $detail['other_pic'] = 0;
                }
                $pics = array_filter(explode(',', $pics['other_pic']));
//                var_dump($pics);

                $this->assign('detail', $detail);
                $this->assign('pics', $pics);
                $this->display();

            }else{

                $this->baoError('请选择要查看的超市商家');
            }
        } else {
            $this->baoError('请选择要查看的超市商家');
        }
    }

    public function exame($store_id = 0) {
        $this->chaoshi_info = D('Chaoshi')->find($store_id);
       // var_dump($this->chaoshi_info);
        $this->shop_info = D('shop')->where(array('shop_id'=>$this->chaoshi_info['shop_id']))->find();
        $this->member_info = D('Users')->find($this->shop_info['user_id']);
        if($this->chaoshi_info['level']==1){
            $this->presonl_exame();
        }else{
            $this->com_exame();
        }
    }

    public function presonl_exame(){
        $auth_info = D('PresonalStoreOpenAuth')->find($this->shop_info['user_id']);
        if(IS_POST){
            if(D('Chaoshi')->save($_POST['data'])){
                 if($_POST['data']['audit']=='2'){
                        D('Sms')->sendSms('chaoshi_open_err', $this->member_info['mobile'],array('nickname'=>$this->member_info['nickname'],'shopname'=>$this->chaoshi_info['store_name'],'explain'=>$_POST['data']['exame_explain']));
                    }elseif($_POST['data']['audit']=='1'){
                        //更新认证状态
                        D('presonal_store_open_auth')->where(array('uid'=>$this->shop_info['user_id']))->save(array('auth'=>1));
                        //更新商家状态
                        //D('shop')->where(array('shop_id'=>$this->shop_info['shop_id']))->save(array('audit'=>1));
                        //检测我佣有的店铺
                        $my_store = D('MyHaveStore')->where(array('uid'=>$this->shop_info['user_id'],'sc_id'=>1))->count();
                        if(!$my_store){
                            D('MyHaveStore')->add(array('uid'=>$this->shop_info['user_id'],'sc_id'=>1));
                        }
                        D('Sms')->sendSms('chaoshi_open_ok', $this->member_info['mobile'],array('nickname'=>$this->member_info['nickname'],'shopname'=>$this->chaoshi_info['store_name']));
                    }
                $this->baoSuccess('审核成功', U('Chaoshi/apply'));
            }
        }
        
        //var_dump($auth_info);
        $this->detail = $auth_info;
        $this->display('presonl_exame');
    }

    public function com_exame(){
        $auth_info = D('ComStoreOpenAuth')->where(array('store_id'=>$this->chaoshi_info['store_id'],'store_class_id'=>1))->find();
        //print_r(D('ComStoreOpenAuth')->getLastSql());
        //var_dump($auth_info);
        if(IS_POST){
            if(D('Chaoshi')->save($_POST['data'])){
                 if($_POST['data']['audit']=='2'){
                        D('Sms')->sendSms('chaoshi_open_err', $this->member_info['mobile'],array('nickname'=>$this->member_info['nickname'],'shopname'=>$this->chaoshi_info['store_name'],'explain'=>$_POST['data']['exame_explain']));
                    }elseif($_POST['data']['audit']=='1'){
                        //更新认证状态
                        D('ComStoreOpenAuth')->where(array('store_id'=>$this->chaoshi_info['store_id'],'store_class_id'=>1))->save(array('audit'=>1));
                        //更新商家状态
                        //D('shop')->where(array('shop_id'=>$this->shop_info['shop_id']))->save(array('audit'=>1));
                        //检测我佣有的店铺
                        $my_store = D('MyHaveStore')->where(array('uid'=>$this->shop_info['user_id'],'sc_id'=>1))->count();
                        if(!$my_store){
                            D('MyHaveStore')->add(array('uid'=>$this->shop_info['user_id'],'sc_id'=>1));
                        }
                        D('Sms')->sendSms('chaoshi_open_ok', $this->member_info['mobile'],array('nickname'=>$this->member_info['nickname'],'shopname'=>$this->chaoshi_info['store_name']));
                    }
                $this->baoSuccess('审核成功', U('Chaoshi/apply'));
            }
        }
        
        $auth_info['other_pic']=explode(",",$auth_info['other_pic']);
        //var_dump($auth_info);
        $this->detail = $auth_info;
        $this->display('com_exame');
    }

    // update:remove begin

//    private function editCheck() {
//        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
//        if (!(int) $data['distance']){
//            $this->baoError('配送距离必须是整数');
//        }
//        $data['logistics'] = (int)$data['logistics'];
//        $data['since_money'] = (int)$data['since_money'];
//        $data['sold_num'] = (int) $data['sold_num'];
//        $data['month_num'] = (int) $data['month_num'];
//        $data['distribution'] = (int) $data['distribution'];
//        $data['audit'] = (int) $data['audit'];
//        $data['intro'] = htmlspecialchars($data['intro']);
//        $data['rate'] = (int) $data['rate'];
//        $data['distance'] = (int) $data['distance'];
//        if (empty($data['intro'])) {
//            $this->baoError('说明不能为空');
//        }
//        $data['orderby'] = (int) $data['orderby'];
//        return $data;
//    }

    // update:remove end

    /**peace
     * @param int $store_id
     * 社区超市（商家列表单个及批量）删除列表
     */
    public function delete($store_id = 0){

        // 调用私有方法
        self::setDelete($store_id, 'chaoshi/index', 'Chaoshi', 'store_id');
    }

    /**peace
     * @param int $store_id
     * 社区超市（商家审核单个及批量）删除列表
     */
    public function shiftdelete($store_id = 0) {

        // 调用私有方法
        self::setDelete($store_id, 'chaoshi/apply', 'Chaoshi', 'store_id');
    }

    // update:remove begin
    /**peace
     * @param int $store_id
     * @param $url   // 页面跳转的方向
     * 共本类的 delete及shiftdelete方法调用
     */
//    protected function setDelete($store_id, $url) {
//        $obj = D('Chaoshi');
//        if (is_numeric($store_id) && ($store_id == (int) $store_id)) {
//            // deleteAll自封装方法
//            if ($obj->deleteAll($store_id)) {
//                $this->baoSuccess('删除成功！', U($url));
//            }
//            $this->baoError('删除失败！', U($url));
//        } else {
//            // _post的chaoshi_id是html页面对应的name属性
//            $store_id = $this->_post('store_id', false);
//            if (is_array($store_id)) {
//                foreach ($store_id as $id) {
//                    if (!$obj->deleteAll($id)) {
//                        $this->baoError('删除失败！', U($url));
//                    }
//                }
//                $this->baoSuccess('删除成功！', U($url));
//            }
//            $this->baoError('请选择要删除的商家');
//        }
//    }

    // update:remove end
    
    public function opened($store_id = 0, $type = 'open') {
        if (is_numeric($store_id) && ($store_id = (int) $store_id)) {
            $obj = D('Chaoshi');
            $is_open = 0;
            if ($type == 'open') {
                $is_open = 1;
            }
            $obj->save(array('store_id' => $store_id, 'is_open' => $is_open));
            $this->baoSuccess('操作成功！', U('Chaoshi/index'));
        }
    }

    /**peace
    * 各类型店铺设置为推荐设置
    * 111
    */
    public function tuijian() {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // 调用公共方法
            $this->recommendStore(I('post.store_id'), 1, 'Chaoshi');
        }else{
            $this->recommendStore(I('get.store_id'), 1, 'Chaoshi');

        }
    }

    public function canceltuijian() {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // 调用公共方法
            $this->recommendStore(I('post.store_id'), 0, 'Chaoshi');
        }else{
            $this->recommendStore(I('get.store_id'), 0, 'Chaoshi');
        }
    }
    
    public function select() {
        $Chaoshi = D('Chaoshi');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => $Chaoshi->flag['exist'], 'audit' => 1);
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
    
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Chaoshicate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Chaoshi->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Chaoshi->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {

            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Chaoshicate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    /**
     * 显示所有待审查和审查未通过的
     */
    public function apply() {
        $Chaoshi = M('Chaoshi');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('bao_chaoshi.closed' => 0, 'bao_chaoshi.audit' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('ChaoshiSearchApplyMessage'))){
            $map = cookie(md5('ChaoshiSearchApplyMessage'));
            if(cookie(md5('ChaoshiSearchApplyMessageName'))){
                $map['bao_chaoshi.store_name'] = cookie(md5('ChaoshiSearchApplyMessageName'));
            }
        }

        if ($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['bao_chaoshi.store_name'] = array('LIKE', '%' . $keyword . '%');
            // 在获取以后再赋值
            $this->assign('keyword', $keyword);
        }
        // 根据提交方式判断，是否清除cookie相应信息
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(!$keyword){
                unset($map['bao_chaoshi.keyword']);
            }
        }

        // 店铺类型，1 个人    2 企业
        if ($level = (int) $this->_param('level')) {
            $map['bao_chaoshi.level'] = $level;
            $this->assign('level', $level);
        }

        // 店铺类型，0未审查    2 未通过审查
        if ($audit = (int) $this->_param('audit')) {
            if($audit == 1){
                $audit = 0;
            }
            $map['bao_chaoshi.audit'] = $audit;
            $this->assign('audit', $audit);
        }

        if ($city_id = (int) $this->_param('city_id')) {
            $map['bao_chaoshi.city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        // 根据提交方式判断，是否清除cookie相应信息
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($city_id === 0){
                unset($map['bao_chaoshi.city_id']);
                unset($map['bao_chaoshi.area_id']);
                unset($map['bao_chaoshi.business_id']);
            }
        }

        if ($area_id = (int) $this->_param('area_id')) {
            $map['bao_chaoshi.area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        // 根据提交方式判断，是否清除cookie相应信息
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($area_id === 0){
                unset($map['bao_chaoshi.area_id']);
                unset($map['bao_chaoshi.business_id']);
            }
        }

        if ($business_id = (int) $this->_param('business_id')) {
            $map['bao_chaoshi.business_id'] = $business_id;
            $this->assign('business_id', $business_id);
        }
        // 根据提交方式判断，是否清除cookie相应信息
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($business_id === 0){
                unset($map['bao_chaoshi.business_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('ChaoshiSearchApplyMessage'), $map, 900);
        cookie(md5('ChaoshiSearchApplyMessageName'), $map['bao_chaoshi.store_name'], 900);

        $count = $Chaoshi->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        // 根据更新时间升序
        $list = $Chaoshi
            ->field('bao_chaoshi.*, bao_users.nickname, bao_users.mobile, bao_area.area_name, bao_city.name as city_name')
            ->join('LEFT JOIN bao_shop ON bao_shop.shop_id = bao_chaoshi.shop_id')
            ->join('LEFT JOIN bao_users ON bao_users.user_id = bao_shop.user_id')
            ->join('LEFT JOIN bao_city ON bao_city.city_id = bao_chaoshi.city_id')
            ->join('LEFT JOIN bao_area ON bao_area.area_id = bao_chaoshi.area_id')
            ->order(array('bao_chaoshi.audit' => 'ASC', 'bao_chaoshi.update_time' => 'DESC'))
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        // 是否选中搜索栏名
        $this->assign('audit_2', $map['bao_chaoshi.audit']);
        $this->assign('city_id_2', $map['bao_chaoshi.city_id']);
        $this->assign('area_id_2', $map['bao_chaoshi.area_id']);
        $this->assign('business_id_2', $map['bao_chaoshi.business_id']);
        $this->assign('level_2', $map['bao_chaoshi.level']);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('cities',M('City')->select());
        // 传递市区县数据
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
        $this->display(); // 输出模板
    }

    // update:remove begin
    /**peace
     * @param int $store_id
     * 商家审查
     */
//    public function audit($store_id = 0) {
//        if (is_numeric($store_id) && ($store_id = (int) $store_id)) {
//            $obj = D('Chaoshi');
//            $obj->save(array('store_id' => $store_id, 'audit' => 1));
//            $this->baoSuccess('审核成功！', U('chaoshi/apply'));
//        } else {
//            $store_id = $this->_post('store_id', false);
//            if (is_array($store_id)) {
//                $obj = D('Chaoshi');
//                foreach ($store_id as $id) {
//                    $obj->save(array('store_id' => $id, 'audit' => 1));
//                }
//                $this->baoSuccess('审核成功！', U('chaoshi/apply'));
//            }else {
//                $this->baoError('请选择要审核的商家');
//            }
//        }
//    }
// update:remove end

    /**
     * 设为首页推荐
     * 作者：刘弢
     */
/*    public function to_home() {
        $store_id =  I('store_id','0','intval');
        if(!$store_id) {
            $this->baoError("参数错误");
        }
        $chaoshi_model = D('Chaoshi');
        $home_store_model = D('HomeStore');
        $is_home = $home_store_model->check_is_home($store_id, 'chaoshi');
        if ($is_home) {
            $this->baoError('已是首页推荐');
        }
        $chaoshi = $chaoshi_model->find($store_id);
        $data = array(
            'store_id' => $chaoshi['store_id'],
            'type' => 'chaoshi',
            'city_id' => $chaoshi['city_id'],
            'logo' => $chaoshi['logo'],
            'store_name' => $chaoshi['store_name'],
        );
        if ($home_store_model->add($data)) {
            $this->baoSuccess('设置成功',U('chaoshi/index'));
        }else {
            $this->error('设置失败');
        }
    }*/

    /**
     * 取消首页推荐
     * 作者：刘弢
     */
/*    public function cancel_home() {
        $store_id =  I('store_id','0','intval');
        if(!$store_id) {
            $this->baoError("参数错误");
        }
        $chaoshi_model = D('Chaoshi');
        $home_store_model = D('HomeStore');
        $res = $home_store_model->cancel_home($store_id, 'chaoshi');
        if ($res){
            $this->baoSuccess('取消成功',U('chaoshi/index'));
        }else {
            $this->baoError('取消失败');
        }
    }*/

    //关联商家uid nickname
    protected function shopUsers($shop_id=0)
    {
     $data=D('Shop')->field('user_id')->where(array('shop_id'=>$shop_id))->find();
     if($data)
     {
     $list=D('Users')->field('mobile,nickname')->where(array('user_id'=>$data['user_id']))->find();
         //print_r($list);
     return  $list;
     }
        return array();
    }

    /**peace
     * 设置为店铺整顿
     */
    public function reorganize(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $this->reorganizeStore(I('post.store_id'), 1, 'Chaoshi');
        }else{
            $this->reorganizeStore(I('get.store_id'), 1, 'Chaoshi');
        }
    }

    /**peace
     * 取消店铺整顿，变为正常
     */
    public function cancelReorganize(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // 调用公共方法  I('store_id', '0', 'intval'), I('type'), 'Chaoshi'
            $this->reorganizeStore(I('post.store_id'), 0, 'Chaoshi');
        }else{
            $this->reorganizeStore(I('get.store_id'), 0, 'Chaoshi');
        }
    }

    /**peace
     * 初始化搜索引擎，商家列表
     */
    public function initialIndex(){
        cookie(md5('ChaoshiSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('chaoshi/index'), 1000);
    }

    /**peace
     * 初始化搜索引擎，审核列表
     */
    public function initialApply(){
        cookie(md5('ChaoshiSearchApplyMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('chaoshi/apply'), 1000);
    }

}
