<?php

/*
 * 作者：王恒
 * QQ：337886915
 */

class MeishipeopleAction extends CommonAction {

   public function _initialize() {
        $this->people_mod =M('MeishiPeople');
        parent::_initialize();
    }

    public function index(){
        import('ORG.Util.Page'); // 导入分页类
//        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = array();

        $count = $this->people_mod->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $this->people_mod
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
            $data['people'] = $_POST['people'];
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
            $status = M('MeishiPeople')->add($data);
            if($status){
                $this->ajaxReturn(array('status'=>'yes','info'=>'增加成功'));
            }else{
                $this->ajaxReturn(array('status'=>'no','info'=>'增加失败'));
            }
        }
        $this->display();
    }

    //编辑
    public function edit($id=0){
        $PeopleModel = M('MeishiPeople');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $people = $_POST['people'];
            $id = (int)$_POST['id'];
            $rst = $PeopleModel
                ->where(array('id' => $id))
                ->save(array('people' => $people, 'create_time' => $_SERVER['REQUEST_TIME']));
            if ($rst) {
                $this->baoSuccess('操作成功', U('meishipeople/index'));
            }
            $this->baoError('操作失败');
        }
        if(is_numeric($id) && ($id = (int) $id)){
            if (!$detail = $PeopleModel->find($id)) {
                $this->baoError('请选择要编辑的商家分类');
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $this->baoError('请选择要编辑的商家分类');
        }
    }

    public function delete($ids = 0) {
        $peopleModel = M('MeishiPeople');
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $ids = I('post.ids', 0, 'intval');
            if (!is_array($ids)) {
                $this->baoError('请选择要删除的分类管理');
            }
            foreach ($ids as $id) {
                if(!$peopleModel->where(array('id' => $id))->delete()){
                    $this->baoError('含不存在的分类');
                };
            }
            $this->baoSuccess('删除成功！', U('meishipeople/index'));
        }
        if (is_numeric($ids) && ($id = (int) $ids)) {
            $findPeople = $peopleModel->find($id);
            if(!$findPeople){
                $this->baoError('未发现该分类');
            }
            if($peopleModel->where(array('id' => $id))->delete()){
                $this->baoSuccess('删除成功！', U('meishipeople/index'));
            }else{
                $this->baoError('删除失败');
            }
        }
    }

    /**
     * 分类排序编辑
     * 递减
     */
    public function orderByDe($id = 0){
        // 合法性验证
        if(!(is_numeric($id) && $id = (int)$id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelPeople = M('MeishiPeople')
            ->where(array('id' => $id))
            ->find();
        if(!$findHotelPeople){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('MeishiPeople')
            ->where(array('id' => $id))
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
    public function orderByIn($id = 0){
        // 合法性验证
        if(!(is_numeric($id) && $id = (int)$id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }

        // 查找分类
        $findHotelPeople = M('MeishiPeople')
            ->where(array('id' => $id))
            ->find();
        if(!$findHotelPeople){
            echo json_encode(array('msg' => '未找到分类信息', 'error' => '204'));
        }

        // 更改对应数据
        $rst = M('MeishiPeople')
            ->where(array('id' => $id))
            ->setInc('order_by');
        if(!$rst){
            echo json_encode(array('msg' => '信息修改失败', 'error' => '204'));
        }

        echo json_encode(array('msg' => '修改成功', 'error' => '200'));
    }

}
