<?php

/*
 * 作者：王恒
 * QQ：337886915
 */

class HotelpriceAction extends CommonAction {

   public function _initialize() {
        $this->hotelprice_mod =M('HotelPrice');
        parent::_initialize();
    }

    public function index(){
//        phpinfo();
        import('ORG.Util.Page'); // 导入分页类
//        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = ['closed' => 0,];

        $count = $this->hotelprice_mod->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $this->hotelprice_mod
            ->where($map)
            ->order(array('order_by' => 'ASC'))
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list);
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();
    }

    public function create(){
        if(IS_AJAX){
            //var_dump($_POST);die;
            $data['min_price'] = (int)$_POST['min_price'];
            $data['max_price'] = (int)$_POST['max_price'];
            $data['price_word'] = $_POST['price_word'];
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
            if($data['min_price'] >= $data['max_price']){
                $this->ajaxReturn(array('status'=>'no','info'=>'高价必须大于低价'));
            }
            $status = M('HotelPrice')->add($data);
            if($status){
                $this->ajaxReturn(array('status'=>'yes','info'=>'增加成功'));
            }else{
                $this->ajaxReturn(array('status'=>'no','info'=>'增加失败'));
            }
        }
        $this->display();
    }

    //编辑
    public function edit($price_id=0){
        $hotelPriceModel = M('HotelPrice');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $min_price = (int)$_POST['min_price'];
            $max_price = (int)$_POST['max_price'];
            $price_word = $_POST['price_word'];
            $price_id = (int)$_POST['price_id'];
            $rst = $hotelPriceModel
                ->where(array('price_id' => $price_id))
                ->save(array('min_price' => $min_price, 'max_price' => $max_price, 'price_word' => $price_word, 'create_time' => $_SERVER['REQUEST_TIME']));
            if ($rst) {
                $this->baoSuccess('操作成功', U('hotelprice/index'));
            }
            $this->baoError('操作失败');
        }
        if(is_numeric($price_id) && ($price_id = (int) $price_id)){
            if (!$detail = $hotelPriceModel->find($price_id)) {
                $this->baoError('请选择要编辑的商家分类');
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $this->baoError('请选择要编辑的商家分类');
        }
    }

    public function delete($price_ids = 0) {
        $priceModel = M('HotelPrice');
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $price_ids = I('post.price_ids', 0, 'intval');
            if (!is_array($price_ids)) {
                $this->baoError('请选择要删除的分类管理');
            }
            foreach ($price_ids as $price_id) {
                if(!$priceModel->where(array('price_id' => $price_id))->save(['closed' => 1])){
                    $this->baoError('含不存在的分类');
                };
            }
            $this->baoSuccess('删除成功！', U('hotelprice/index'));
        }
        if (is_numeric($price_ids) && ($price_id = (int) $price_ids)) {
            $findPrice = $priceModel->find($price_id);
            if(!$findPrice){
                $this->baoError('未发现该分类');
            }
            if($priceModel->where(array('price_id' => $price_id))->save(['closed' => 1])){
                $this->baoSuccess('删除成功！', U('hotelprice/index'));
            }else{
                $this->baoError('删除失败');
            }
        }
    }

    /**
     * 分类排序编辑
     * 递减
     */
    public function orderByDe($price_id = 0){
        // 合法性验证
        if(!(is_numeric($price_id) && $price_id = (int)$price_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelPrice = M('HotelPrice')
            ->where(array('price_id' => $price_id))
            ->find();
        if(!$findHotelPrice){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('HotelPrice')
            ->where(array('price_id' => $price_id))
            ->setDec('order_by');
        if(!$rst){
            echo json_encode(array('msg' => '信息修改失败', 'error' => '204'));
        }

        echo json_encode(array('msg' => '修改成功', 'error' => '200'));
    }

    /**
     * 分类排序编辑
     * 递增
     */
    public function orderByIn($price_id = 0){
        // 合法性验证
        if(!(is_numeric($price_id) && $price_id = (int)$price_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelPrice = M('HotelPrice')
            ->where(array('price_id' => $price_id))
            ->find();
        if(!$findHotelPrice){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('HotelPrice')
            ->where(array('price_id' => $price_id))
            ->setInc('order_by');
        if(!$rst){
            echo json_encode(array('msg' => '信息修改失败', 'error' => '204'));
        }

        echo json_encode(array('msg' => '修改成功', 'error' => '200'));
    }

}
