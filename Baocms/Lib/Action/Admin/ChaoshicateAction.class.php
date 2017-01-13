<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshicateAction extends CommonAction {

   public function _initialize() {
        $this->chaoshicate_mod =D('ChaoshiCate');
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

        $count = $this->chaoshicate_mod->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = (int)$_GET[$var];
        $this->assign('p',$p);
        $list = $this->chaoshicate_mod->where($map)->order(array('chaoshi_cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        
        
        $this->display();
    }
    public function create(){
        if(IS_AJAX){
            //var_dump($_POST);die;
            $data['cate_name']=$_POST['cate_name'];
            $data['channel_url']=$_POST['channel_url'];
            $status = M('chaoshi_cate')->add($data);

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
        $status = D('ChaoshiCate')->where($data)->count();
        if($status){
            $this->ajaxReturn(array('status'=>'no','info'=>"该分类已存在"));
        }else{
            $this->ajaxReturn(array('status'=>'yes','info'=>"该分类名可以使用"));
        }
    }
    //编辑
    public function edit($chaoshi_cate_id=0){

         if ($cate_id = $chaoshi_cate_id) {
            $obj = $this->chaoshicate_mod;
            if (!$detail = $obj->find($cate_id)) {
               
                $this->baoError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                
                $data['chaoshi_cate_id'] = $cate_id;
                $data = array_merge($data,$_POST['data']);
                //var_dump($_POST);die;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('chaoshicate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                //$this->assign('channelmeans', D('Lifecate')->getChannelMeans());
                $this->display();
            }
        } else {
            
            $this->baoError('请选择要编辑的商家分类');
        }
    }
    public function delete($chaoshi_cate_id = 0) {
        if (is_numeric($chaoshi_cate_id) && ($cate_id = (int) $chaoshi_cate_id)) {
            $obj = $this->chaoshicate_mod;
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('chaoshicate/index'));
        } else {
            $cate_id = $this->_post('chaoshi_cate_id', false);
            if (is_array($cate_id)) {
                $obj = $this->chaoshicate_mod;
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('chaoshicate/index'));
            }
            $this->baoError('请选择要删除的分类管理');
        }
    }

   

}
