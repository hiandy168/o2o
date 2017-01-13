<?php

/*
 * 作者：王恒
 * QQ：337886915
 */

class HotelbrandAction extends CommonAction {

   public function _initialize() {
        $this->hotelbrand_mod =M('HotelBrand');
        parent::_initialize();
    }

    public function index(){
        import('ORG.Util.Page'); // 导入分页类
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = ['closed' => 0,];

        if ($keyword) {
            $map['brand_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);

        $count = $this->hotelbrand_mod->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $this->hotelbrand_mod
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
            $data['brand_name'] = $_POST['brand_name'];
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
            $status = M('HotelBrand')->add($data);
            if($status){
                $this->ajaxReturn(array('status'=>'yes','info'=>'增加成功'));
            }else{
                $this->ajaxReturn(array('status'=>'no','info'=>'增加失败'));
            }
        }
        $this->display();
    }

    //检测分类名是否存在
    public function checkcatename(){
        $data['brand_name']=I('param');
        $status = D('HotelBrand')->where($data)->count();
        if($status){
            $this->ajaxReturn(array('status'=>'no','info'=>"该分类已存在"));
        }else{
            $this->ajaxReturn(array('status'=>'yes','info'=>"该分类名可以使用"));
        }
    }

    //编辑
    public function edit($brand_id=0){
        $hotelModel = M('HotelBrand');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $brand_name = $_POST['brand_name'];
            $brand_id = (int)$_POST['brand_id'];
            $rst = $hotelModel
                ->where(array('brand_id' => $brand_id))
                ->save(array('brand_name' => $brand_name));
            if ($rst) {
                $this->baoSuccess('操作成功', U('hotelbrand/index'));
            }
            $this->baoError('操作失败');
        }
        if(is_numeric($brand_id) && ($brand_id = (int) $brand_id)){
            if (!$detail = $hotelModel->find($brand_id)) {
                $this->baoError('请选择要编辑的酒店品牌');
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $this->baoError('请选择要编辑酒店品牌');
        }
    }

    public function delete($brand_ids = 0) {
        $brandModel = M('HotelBrand');
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $cate_ids = I('post.brand_ids', 0, 'intval');
            if (!is_array($cate_ids)) {
                $this->baoError('请选择要删除的分类管理');
            }
            foreach ($brand_ids as $brand_id) {
                if(!$brandModel->where(array('brand_id' => $brand_id))->save(['closed' => 1])){
                    $this->baoError('含不存在的分类');
                };
            }
            $this->baoSuccess('删除成功！', U('hotelbrand/index'));
        }
        if (is_numeric($brand_ids) && ($brand_id = (int) $brand_ids)) {
            $findBrand = $brandModel->find($brand_id);
            if(!$findBrand){
                $this->baoError('未发现该分类');
            }
            if($brandModel->where(array('brand_id' => $brand_id))->save(['closed' => 1])){
                $this->baoSuccess('删除成功！', U('hotelbrand/index'));
            }else{
                $this->baoError('删除失败');
            }
        }
    }

    /**
     * 分类排序编辑
     * 递减
     */
    public function orderByDe($brand_id = 0){
        // 合法性验证
        if(!(is_numeric($brand_id) && $brand_id = (int)$brand_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelBrand = M('HotelBrand')
            ->where(array('brand_id' => $brand_id))
            ->find();
        if(!$findHotelBrand){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('HotelBrand')
            ->where(array('brand_id' => $brand_id))
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
    public function orderByIn($brand_id = 0){
        // 合法性验证
        if(!(is_numeric($brand_id) && $brand_id = (int)$brand_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelBrand = M('HotelBrand')
            ->where(array('brand_id' => $brand_id))
            ->find();
        if(!$findHotelBrand){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('HotelBrand')
            ->where(array('brand_id' => $brand_id))
            ->setInc('order_by');
        if(!$rst){
            echo json_encode(array('msg' => '信息修改失败', 'error' => '204'));
        }

        echo json_encode(array('msg' => '修改成功', 'error' => '200'));
    }

}
