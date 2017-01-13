<?php

/*
 * 作者：王恒
 * QQ：337886915
 */

class MeishicateAction extends CommonAction {

   public function _initialize() {
        $this->meishicate_mod =D('MeishiCate');
        parent::_initialize();
    }

    public function index(){
        import('ORG.Util.Page'); // 导入分页类
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = array();

        if ($keyword) {
            $map['cate_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);

        $count = $this->meishicate_mod->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $this->meishicate_mod
            ->where($map)
            ->order(array('order_by' => 'ASC'))
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
//        var_dump($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function create(){
        if(IS_AJAX){
            //var_dump($_POST);die;
            $data['cate_name']=$_POST['cate_name'];
            $status = M('Meishi_cate')->add($data);
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
        $data['cate_name']=I('param');
        $status = D('MeishiCate')->where($data)->count();
        if($status){
            $this->ajaxReturn(array('status'=>'no','info'=>"该分类已存在"));
        }else{
            $this->ajaxReturn(array('status'=>'yes','info'=>"该分类名可以使用"));
        }
    }

    //编辑
    public function edit($cate_id=0){
        $meishiModel = M('MeishiCate');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cate_name = $_POST['cate_name'];
            $cate_id = (int)$_POST['cate_id'];
            $rst = $meishiModel
                ->where(array('cate_id' => $cate_id))
                ->save(array('cate_name' => $cate_name));
            if ($rst) {
                $this->baoSuccess('操作成功', U('meishicate/index'));
            }
            $this->baoError('操作失败');
        }
        if(is_numeric($cate_id) && ($cate_id = (int) $cate_id)){
            if (!$detail = $meishiModel->find($cate_id)) {
                $this->baoError('请选择要编辑的商家分类');
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $this->baoError('请选择要编辑的商家分类');
        }
    }

    public function delete($cate_ids = 0) {
        $meishiModel = M('MeishiCate');
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $cate_ids = I('post.cate_ids', 0, 'intval');
            if (!is_array($cate_ids)) {
                $this->baoError('请选择要删除的分类管理');
            }
            foreach ($cate_ids as $cate_id) {
                if(!$meishiModel->where(array('cate_id' => $cate_id))->delete()){
                    $this->baoError('含不存在的分类');
                };
            }
            $this->baoSuccess('删除成功！', U('meishicate/index'));
        }
        if (is_numeric($cate_ids) && ($cate_id = (int) $cate_ids)) {
            $findCate = $meishiModel->find($cate_id);
            if(!$findCate){
                $this->baoError('未发现该分类');
            }
            if($meishiModel->where(array('cate_id' => $cate_id))->delete()){
                $this->baoSuccess('删除成功！', U('meishicate/index'));
            }else{
                $this->baoError('删除失败');
            }
        }
    }

    /**
     * 分类排序编辑
     * 递减
     */
    public function orderByDe($cate_id = 0){
        // 合法性验证
        if(!(is_numeric($cate_id) && $cate_id = (int)$cate_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelCate = M('MeishiCate')
            ->where(array('cate_id' => $cate_id))
            ->find();
        if(!$findHotelCate){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('MeishiCate')
            ->where(array('cate_id' => $cate_id))
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
    public function orderByIn($cate_id = 0){
        // 合法性验证
        if(!(is_numeric($cate_id) && $cate_id = (int)$cate_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelCate = M('MeishiCate')
            ->where(array('cate_id' => $cate_id))
            ->find();
        if(!$findHotelCate){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('MeishiCate')
            ->where(array('cate_id' => $cate_id))
            ->setInc('order_by');
        if(!$rst){
            echo json_encode(array('msg' => '信息修改失败', 'error' => '204'));
        }

        echo json_encode(array('msg' => '修改成功', 'error' => '200'));
    }

}
