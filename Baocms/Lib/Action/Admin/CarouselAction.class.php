<?php

/**
 * APP首页轮播管理
 * 作者：刘弢
 * QQ：473139299
 */
class CarouselAction extends CommonAction {
    protected $_carousel_model;
    public function _initialize() {
        parent::_initialize();
        $this->_carousel_model = D('AppCarousel');
        $types = $this->_carousel_model->get_type();
        $this->citys = D('City')->fetchAll();
        
        $this->assign('types', $types);        
        $this->assign('citys', $this->citys);
    }
    
    public function index() {       
        $map = array();
        $keyword = I('keyword');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $city_id = I('city_id','0','intval');
        if ($city_id){
            if ($city_id == -1){
                $map['city_id'] = 0;
            }else {
                $map['city_id'] = $city_id;
            }          
        }
        import('ORG.Util.Page'); // 导入分页类
        $count = $this->_carousel_model->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $this->_carousel_model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('keyword',$keyword);
        $this->display();
    }
    
    public function create() {
        if(IS_POST) {
            if (!$data = $this->_carousel_model->create()) {
                $this->baoError($this->_carousel_model->getError());
            }
            $link_type = I('link_type','0','intval');
            if ($link_type == 1){
                $models = $this->_carousel_model->get_model_name();
                $type = I('type');
                //var_dump($type);die;
                $store_id = I('store_id','0','intval');               
                $model_name = $models[$type];
                $store = D($model_name)->find($store_id);
                if (!$store) {
                    $this->baoError('商家不存在');
                }
                $data['link'] = $store_id;
            }
            if ($this->_carousel_model->add($data)){
                $this->baoSuccess('添加成功',U('carousel/index'));
            }else {
                $this->baoError('添加失败');
            }
        }
        $this->display();
    }
    
    public function edit() {
        $id = I('id','0','intval');
        if (!$detail = $this->_carousel_model->find($id)) {
            $this->error('轮播图片不存在');
        }
        $models = $this->_carousel_model->get_model_name();
        $model_name = $models[$detail['type']];
        $store = D($model_name)->find($detail['link']);
        if ($this->isPost()) {
            if (!$data = $this->_carousel_model->create()){
                $this->baoError($this->_carousel_model->getError());
            }
            $link_type = I('link_type','0','intval');
            if ($link_type != 1){
                unset($data['type']);
            } 
            if ($this->_carousel_model->save($data)){
                $this->baoSuccess('编辑成功',U('carousel/index'));
            }else {
                $this->baoError('编辑失败');
            }
        }else {           
            $detail['store_name'] = $store['store_name'];
            $this->assign('detail',$detail);
            $this->display();
        }
    }
    
    public function delete() {
        $id = I('get.id');
        if ($id = (int)$id) {
            if ($this->_carousel_model->delete($id)) {
                $this->baoSuccess('删除成功',U('carousel/index'));
            }else {
                $this->baoError('删除失败');
            }
        }else {
            $id = I('post.id');
            if (is_array($id)) {     
                if($this->_carousel_model->where(array('id'=>array('in',$id)))->delete()) {
                    $this->baoSuccess('删除成功',U('carousel/index'));
                }else {
                    $this->baoError('删除失败');
                }               
            }else {
                $this->baoError('参数错误');
            }
        }          
    }
    public function store_select() {
        $models = $this->_carousel_model->get_model_name();
        $type = I('type','hotel');
        $model_name = $models[$type];
        $store_model = D($model_name);
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' =>0, 'audit' => 1);
        $keyword = $this->_param('keyword', 'htmlspecialchars');        
        if(isset($keyword) && !empty($keyword)) {         //因为字段不统一惹的祸
            if ($model_name == 'Meishi' || $model_name == 'Hotel') {
                $map['store_name|telephone'] = array('LIKE', '%' . $keyword . '%');
            }elseif ($model_name == 'HouseStore') {
                $map['store_name|attn_phone'] = array('LIKE', '%' . $keyword . '%');
            }           
            $this->assign('keyword', $keyword);
        }
        $count = $store_model->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $store_model->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
//        var_dump($store_model->_sql());
        $this->assign('type',$type);
        $this->assign('types',$this->_carousel_model->get_type());
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
//        var_dump($list);
//        if(in_array($type,[0=>'house',1=>'houserent',2=>'housetwo']))
//        {
//        $this->assign('adk',1);
//        }
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
}
