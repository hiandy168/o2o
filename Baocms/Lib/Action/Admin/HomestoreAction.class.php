<?php

/**
 * APP首页推荐商家管理
 * 作者：刘弢
 * QQ：473139299
 */
class HomestoreAction extends CommonAction {
    protected $home_store_model;
    protected function _initialize(){
        parent::_initialize();
        $this->home_store_model = D('HomeStore');
        $types = $this->home_store_model->get_type();
        $citys = D('City')->fetchAll();
        $this->assign('types',$types);        
        $this->assign('citys',$citys);
    }
    public function menu() {
        $this->display();
    }
    public function index() {        
        $type = I('type');
        $city_id =  I('city_id','0','intval');
        $list = $this->home_store_model->where(array('type'=>$type,'city_id'=>$city_id))->select();
        $this->assign('city_id',$city_id);
        $this->assign('list',$list);
        $this->assign('type',$type);
        $this->display();
    }
    public function edit() {        
        $id = I('id','0','intval');
        $type=I('type');
        $detail = $this->home_store_model->find($id);
        if (IS_POST){
            if (!$data = $this->home_store_model->create()){
                $this->baoError($this->home_store_model->getError());
            }
            if ($this->home_store_model->save($data)){
                $this->baoSuccess('编辑成功',U('homestore/index',array('type'=>$detail['type'],'city_id'=>$detail['city_id'])));
            }else {
                $this->baoError('编辑失败');
            }
        }else {                  
            $this->assign('detail',$detail);
            $this->assign('type',$type);
            $this->display();
        }
    }
    public function city() {
        $type = I('type');        
        $this->assign('type',$type);       
        $this->display();
    }
    public function meishi_store_select() {
        $store_model = D('Meishi');
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => $store_model->flag['exist'], 'audit' => 1, 'city_id'=>$city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['store_name|telephone'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $store_model->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $store_model->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('city_id', $city_id);
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function hotel_store_select() {
        $store_model = D('Hotel');
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => $store_model->flag['exist'], 'audit' => 1, 'city_id'=>$city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['store_name|telephone'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $store_model->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $store_model->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('city_id', $city_id);
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function house_store_select() {

        $type=I('house_type');
        //var_dump($type);
        $houseType=['1'=>'House','2'=>'HouseTwo','3'=>'HouseRent'];
        $store_model = D($houseType[$type]);
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' =>0, 'audit' => 1, 'city_id'=>$city_id);
        $count = $store_model->where($map)->count(); // 查询满足要求的总记录数
        if($keyword=I('post.keyword'))
        {
         $map['product_name']=['like','%'.$keyword.'%'];
        }
        if($type==1)
        {
         $product_id=$store_model->query("select count(*) as product_id from bao_house where product_id  in (select product_id from bao_house_active where audit='1' and closed='0') and city_id = $city_id");
         $count=$product_id['product_id'];
        }
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $store_model->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if($type==1)
        {
            $list = $store_model
                ->query("select * from bao_house where product_id  in (select product_id from bao_house_active where audit='1' and closed='0') and city_id = $city_id limit $Page->firstRow ,$Page->listRows ");
//                ->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
            if($name=I('post.keyword'))
            {
                $list = $store_model
                    ->query("select * from bao_house where product_id  in (select product_id from bao_house_active where audit='1' and closed='0') and product_name like '%$name%' and city_id = $city_id limit $Page->firstRow ,$Page->listRows ");
            }
        }
        //var_dump($store_model->_sql());
        $this->assign('city_id', $city_id);
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function create() {
        $city_id = I('city_id','0','intval');
        $type = I('type');
        $houseType=I('post.houseType');
        $types = $this->home_store_model->get_type();       
        $type_keys = array_keys($types);       
        if (!in_array($type,$type_keys)) {
            $this->error("类型不存在");
        }
        if (!$city = D('City')->find($city_id)) {
            $this->error("城市不存在");
        }
        if (IS_POST){
            $count = $this->home_store_model->where(array('type'=>$type,'city_id'=>$city_id))->count();
            if ($type=='hotel' && $count>5){
                $this->baoError("数量已达上线");
            }elseif ($count>7) {
                $this->baoError("数量已达上线");
            }
            if (!$data = $this->home_store_model->create()){
                $this->baoError($this->home_store_model->getError());
            }
            if($houseType)
            {
              $data['house_type']=$houseType;
            }
            if ($this->home_store_model->add($data)){
                $this->baoSuccess('添加成功',U('homestore/index',array('type'=>$type,'city_id'=>$city_id)));
            }else {
                $this->baoError('添加失败');
            }
        }else {
            $this->assign('type',$type);
            $this->assign('city_id',$city_id);
            $this->display();
        }
    }
}
