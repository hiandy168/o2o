<?php


class AddressAction extends CommonAction {

    public function index() {
        $Useraddr = D('Useraddr');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id' => $this->uid, 'closed' => 0);
        $count = $Useraddr->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Useraddr->where($map)->order(array('addr_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $area_ids = $business_ids = array();
        foreach ($list as $k => $val) {
            $area_ids[$val['area_id']] = $val['area_id'];
            $business_ids[$val['business_id']] = $val['business_id'];
        }
        //var_dump( D('Business')->itemsByIds($business_ids));die();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('areas', D('Area')->itemsByIds($area_ids));
        $this->assign('business', D('Business')->itemsByIds($business_ids));
        $this->display(); // 输出模板
    }

    private function addressCheck() {
        $data = $this->checkFields($this->_post('data', false), array('city_id', 'area_id', 'business_id', 'name', 'mobile', 'addr'));
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('收货人不能为空');
        }
        $data['user_id'] = (int) $this->uid;
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        if (empty($data['business_id'])) {
            $this->baoError('商圈不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机号码不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机号码格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('具体地址不能为空');
        }
        return $data;
    }

    public function addressadd() {
        if ($this->isPost()) {
            $data = $this->addressCheck();
            $obj = D('Useraddr');
            $data['is_default'] = 0;
            if ($obj->add($data)) {
                $backurl = $this->_post('backurl', 'htmlspecialchars');
                $this->baoSuccess('新增收货地址成功', $backurl);
            }
            $this->baoError('操作失败！');
        } else {
            if (!empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
                $backurl = $_SERVER['HTTP_REFERER'];
            } else {
                $backurl = U('address/index');
            }
            $default = D('Useraddr')->where(array('user_id' => $this->uid, 'is_default' => 1, 'closed' => 0))->find();
            $this->assign('default', $default);
            $this->assign('backurl', $backurl);
            $this->assign('areas', D('Area')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }

    public function addressdel($addr_id) {
        $addr_id = (int) $addr_id;
        if (empty($addr_id)) {
            $this->baoError('收货地址不存在');
        }
        if (!$detail = D('Useraddr')->find($addr_id)) {
            $this->baoError('收货地址不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->baoError('请不要操作别人的收货地址');
        }
        $obj = D('Useraddr');
        $obj->save(array('addr_id' => $addr_id, 'closed' => 1));
        $this->baoSuccess('删除成功！', U('address/index'));
    }

    public function editaddr($addr_id = 0) {
        if ($addr_id = (int) $addr_id) {
            $obj = D('Useraddr');
            if (!$detail = $obj->find($addr_id)) {
                $this->baoError('请选择要编辑的收货地址');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要试图操作其他人的内容');
                die;
            }
            if ($this->isPost()) {
                // var_dump($addr_id);die();
                $data = $this->addressCheck();
                $data['addr_id'] = $addr_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('address/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的收货地址');
        }
    }

    public function deleteaddr($addr_id = 0) {
        if (is_numeric($addr_id) && ($addr_id = (int) $addr_id)) {

            $obj = D('Useraddr');
            $detail = $obj->find($addr_id);
            if (empty($detail) || $detail['user_id'] != $this->uid) {
                $this->baoError('没有您要设置的地址');
            }
            $obj->save(array('addr_id' => $addr_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('address/index'));
        } else {

            $this->baoError('请选择要删除的收货地址');
        }
    }

    public function updatedefault($addr_id = 0) {
        if (is_numeric($addr_id) && ($addr_id = (int) $addr_id)) {

            $obj = D('Useraddr');
            $detail = $obj->find($addr_id);
            if (empty($detail) || $detail['user_id'] != $this->uid) {
                $this->baoError('没有您要设置的地址');
            }
            $obj->save(array('is_default' => 0), array("where" => array('user_id' => $this->uid)));
            //print_r($obj->getLastSql());die();
            $obj->save(array('is_default' => 1), array("where" => array('addr_id' => $addr_id)));
            $this->baoSuccess('设置成功！', U('address/index'));
        } else {
            $this->baoError('请选择要设置的收货地址');
        }
    }

    public function cancel($addr_id = 0) {
        if (is_numeric($addr_id) && ($addr_id = (int) $addr_id)) {

            $obj = D('Useraddr');
            $detail = $obj->find($addr_id);
            if (empty($detail) || $detail['user_id'] != $this->uid) {
                $this->baoError('没有您要设置的地址');
            }
            $obj->save(array('is_default' => 0), array("where" => array('user_id' => $this->uid)));
            $this->baoSuccess('设置成功！', U('address/index'));
        } else {
            $this->baoError('请选择要设置的收货地址');
        }
    }
	
    
    public function address() {
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $data['user_id'] = $this->uid;
        if (IS_AJAX) {
            $data['name'] = htmlspecialchars($_POST['name']);
            if (empty($data['name'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '联系人不能为空！'));
            }
            $data['city_id'] = (int) $_POST['city_id'];
            if (empty($data['city_id'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '城市不能为空！'));
            }
            $data['area_id'] = (int) $_POST['area_id'];
            if (empty($data['area_id'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '地区不能为空！'));
            }
            $data['business_id'] = (int) $_POST['business_id'];
            if (empty($data['business_id'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '商圈不能为空！'));
            }
            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if (empty($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码不能为空！'));
            }
            if (!isMobile($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码格式不正确！'));
            }
            $data['addr'] = htmlspecialchars($_POST['addr']);
            if (empty($data['addr'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '地址不能为空！'));
            }
            $addrs_id = (int)$_POST['addrs_id'];
            if (D('Useraddr')->add($data)) {
                $res = D('Useraddr')->where(array('user_id' => $this->uid))->limit(5)->select();
                $addr_array = array();
                foreach ($res as $k => $val) {
                    $addr_array[$k]['addr_id'] = $val['addr_id'];
                    $addr_array[$k]['city_id'] = $val['city_id'];
                    $addr_array[$k]['area_id'] = $val['area_id'];
                    $addr_array[$k]['business_id'] = $val['business_id'];
                    $addr_array[$k]['city'] = $this->citys[$val['city_id']]['name'];
                    $addr_array[$k]['area'] = $this->areas[$val['area_id']]['area_name'];
                    $addr_array[$k]['bizs'] = $this->bizs[$val['business_id']]['business_name'];
                    $addr_array[$k]['name'] = $val['name'];
                    $addr_array[$k]['addr'] = $val['addr'];
                    $addr_array[$k]['mobile'] = $val['mobile'];
                }
                foreach($addr_array as $key=>$v){
                    if($v['addr_id'] == $addrs_id){
                        $shift_arr = $addr_array[$key];
                        unset($addr_array[$key]);
                        array_unshift($addr_array,$shift_arr);
                    }
                }
                $this->ajaxReturn(array('status' => 'success', 'msg' => '添加地址成功！', 'res' => $addr_array));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '添加失败！'));
            }
        } else {
            $this->display();
        }
    }

    public function addredit() {

        if (empty($this->uid)) {
            $this->ajaxLogin();
        }

        $data['user_id'] = $this->uid;
        if (IS_AJAX) {
            $data['addr_id'] = (int) $_POST['addr_id'];
            if (!$detail = D('Useraddr')->find($data['addr_id'])) {

                $this->ajaxReturn(array('status' => 'error', 'msg' => '地址不存在！'));
            }
            if ($detail['user_id'] != $this->uid) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '不能修改别人的地址！'));
            }
            $data['name'] = htmlspecialchars($_POST['name']);
            if (empty($data['name'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '联系人不能为空！'));
            }
            $data['city_id'] = (int) $_POST['city_id'];
            if (empty($data['city_id'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '城市不能为空！'));
            }
            $data['area_id'] = (int) $_POST['area_id'];
            if (empty($data['area_id'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '地区不能为空！'));
            }
            $data['business_id'] = (int) $_POST['business_id'];
            if (empty($data['business_id'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '商圈不能为空！'));
            }
            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if (empty($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码不能为空！'));
            }
            if (!isMobile($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码格式不正确！'));
            }
            $data['addr'] = htmlspecialchars($_POST['addr']);
            if (empty($data['addr'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '地址不能为空！'));
            }
            $addrs_id = (int)$_POST['addrs_id'];
            if (false !== D('Useraddr')->save($data)) {
                $res = D('Useraddr')->where(array('user_id' => $this->uid))->limit(5)->select();
                $addr_array = array();
                foreach ($res as $k => $val) {
                    $addr_array[$k]['addr_id'] = $val['addr_id'];
                    $addr_array[$k]['city_id'] = $val['city_id'];
                    $addr_array[$k]['area_id'] = $val['area_id'];
                    $addr_array[$k]['business_id'] = $val['business_id'];
                    $addr_array[$k]['city'] = $this->citys[$val['city_id']]['name'];
                    $addr_array[$k]['area'] = $this->areas[$val['area_id']]['area_name'];
                    $addr_array[$k]['bizs'] = $this->bizs[$val['business_id']]['business_name'];
                    $addr_array[$k]['name'] = $val['name'];
                    $addr_array[$k]['addr'] = $val['addr'];
                    $addr_array[$k]['mobile'] = $val['mobile'];
                }
                foreach($addr_array as $key=>$v){
                    if($v['addr_id'] == $addrs_id){
                        $shift_arr = $addr_array[$key];
                        unset($addr_array[$key]);
                        array_unshift($addr_array,$shift_arr);
                    }
                }
                $this->ajaxReturn(array('status' => 'success', 'msg' => '修改成功！', 'res' => $addr_array));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '修改失败！'));
            }
        } else {
            $this->display();
        }
    }

    //ajax添加地址
    public function ajaxadd(){
        $Useraddr = D('Useraddr');
        if(IS_POST){
           
            $_POST['user_id'] = $this->uid;
            $data = $Useraddr->create();

            if($data){
                if(empty($data['addr_id'])){
                    $id = $Useraddr->add();
                    if($id){
                        if($data['is_default']){
                            if($Useraddr->where(array('addr_id'=>array('neq',$id)))->save(array('is_default'=>0))){
                                $this->ajaxReturn(array('status'=>'yes','info'=>'增加成功'));
                            }else{
                                $this->ajaxReturn(array('status'=>'yes','info'=>'更新默认收货地址失败'));
                            }
                        }else{
                            $this->ajaxReturn(array('status'=>'yes','info'=>'增加成功'));
                        }
                    }else{
                        $this->ajaxReturn(array('status'=>"no",'info'=>'增加收货地址失败'));
                    }
                }else{
                    $status = $Useraddr->save();
                    if($status){
                        if($data['is_default']){
                            if($Useraddr->where(array('addr_id'=>array('neq',$data['addr_id'])))->save(array('is_default'=>0))){
                                $this->ajaxReturn(array('status'=>'yes','info'=>'修改成功'));
                            }else{
                                $this->ajaxReturn(array('status'=>'yes','info'=>'更新默认收货地址失败'));
                            }
                        }else{
                            $this->ajaxReturn(array('status'=>'yes','info'=>'更新收货地址成功'));
                        }
                        
                    }else{
                        $this->ajaxReturn(array('status'=>'no','info'=>'没有修改内容，或重复提交'));
                    }

                }
            }else{
                $this->ajaxReturn(array('status'=>'no','info'=>$Useraddr->getError()));

            }

        }
        if(I('addr_id')){
           $this->assign('address',$address=$Useraddr->find(I('addr_id'))); 
        }

        $this->display();
    }

    //ajax获得收货地址
    public function ajax_getlist(){
        $user_addr_mod = D('UserAddr');
        $where['user_id'] = $this->uid;
        $where['closed'] = 0;
        $alllist = $user_addr_mod->order('is_default desc')->where($where)->select();
        if (I('lng') && I('lat')&&I('distance')){
            $where['_string'] = I('distance').'>(1000*ACOS(SIN(('.I('lat').' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.I('lat').' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.I('lng').'* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380) ';                      
        }        
        $list = $user_addr_mod->order('is_default desc')->where($where)->select();
        $addr_keys = array();
        foreach ($list as $k => $v){
            $addr_keys[] = $v['addr_id'];
        }
        $otherlist = array();
        foreach ($alllist as $key => $val){
            if (!in_array($val['addr_id'], $addr_keys)){
                $otherlist[] = $val;
            }
        }
        $this->ajaxReturn(array('status'=>'yes','info'=>'获得成功','data'=>array('list'=>$list,'otherlist'=>$otherlist)));
    }
    //设置默认收货地址
    public function set_default_addr($addr_id){
        $Useraddr = D('Useraddr');
        $Useraddr->where(array('addr_id'=>$addr_id))->save(array('is_default'=>1));
        $Useraddr->where(array('addr_id'=>array('neq',$addr_id)))->save(array('is_default'=>0));
        if(IS_AJAX&&IS_POST){
            $this->ajaxReturn(array('status'=>'yes','info'=>'设置成功'));
        }
    }



    public function del(){
        $addr_id=I('addr_id');
        if(D('UserAddr')->where(array('addr_id'=>$addr_id))->delete())
        {
            $this->ajaxReturn(1);

        }else{

            $this->ajaxReturn(2);
        }

    }











    
}
