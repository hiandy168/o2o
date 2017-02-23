<?php

/**
 * APP首页猜你喜欢管理
 * 作者：刘弢
 * QQ：473139299
 */
class GuessyoulikeAction extends CommonAction {
    protected $guess_you_like_model;
    protected function _initialize(){
        parent::_initialize();
        $this->guess_you_like_model = D('GuessYouLike');
        $types = $this->guess_you_like_model->get_type();
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
        $list = $this->guess_you_like_model->where(array('type'=>$type,'city_id'=>$city_id))->select();

        $this->assign('city_id',$city_id);
        $this->assign('list',$list);
        $this->assign('type',$type);
        $this->display();
    }
    public function city() {
        $type = I('type');        
        $this->assign('type',$type);       
        $this->display();
    }
    public function meishi_goods_select() {
        $goods_model = D('Meishi');
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => $goods_model->flag['exist'], 'audit' => 1);
        
//        $store_ids = array();
//        $stores = D('Meishi')->where(array('city_id'=>$city_id))->select();
//        foreach ($stores as $key=>$val){
//            $store_ids[] = $val['store_id'];
//        }
//        $map['store_id'] = array('in',$store_ids);
        $map['city_id']=$city_id;
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['goods_name|description'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $goods_model->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $goods_model->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('city_id', $city_id);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function hotel_goods_select() {
        $goods_model = D('Hotel');
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' =>0, 'audit' => 1);
        
        $store_ids = array();
//        $stores = D('Hotel')->where(array('city_id'=>$city_id))->select();
//        foreach ($stores as $key=>$val){
//            $store_ids[] = $val['store_id'];
//        }
//        $map['store_id'] = array('in',$store_ids);
        $map['city_id']=$city_id;
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['room_name|content_room'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $goods_model->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $goods_model->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //var_dump($goods_model->_sql());
        //var_dump($list);
        $this->assign('city_id', $city_id);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('type','hotel');
        $this->display(); // 输出模板
    }
    public function house_goods_select() {
        $goods_model = D('House');
        $city_id = I('city_id','0','intval');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'audit' => 1, 'city_id'=>$city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['product_name|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $goods_model->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $goods_model
            ->field('bao_house.*,bao_house_pics.pic as photo')
            ->join('LEFT JOIN bao_house_pics ON bao_house.product_id=bao_house_pics.product_id')
            ->where($map)->limit($Page->firstRow . ',' . $Page->listRows)
            ->group('product_id')
            ->select();
        //var_dump($goods_model->_sql());
        $this->assign('city_id', $city_id);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function hotel_goods_add() {
        $hotel_room_model = D('Hotel');
        $goods_id = I('goods_id','0','intval');

        $res = $this->guess_you_like_model->where(array('goods_id'=>$goods_id,'type'=>'hotel'))->find();
        $price=D('HotelProduct')->field('min(price) as price_min')->where(['store_id'=>$goods_id])->group('store_id')->select();
        $price[0]['price_min']=$price[0]['price_min']?$price[0]['price_min']:0;
       // var_dump($price);
        if ($res) {
            $this->baoError("商品已存在");
        }
        if(!$room = $hotel_room_model->find($goods_id)) {
            $this->baoError("商品不存在");
        }
        $store = D('Hotel')->find($room['store_id']);
        $data = array(
            'goods_name' => $room['store_name'],
            'goods_id' => $room['store_id'],
            'price' => $price[0]['price_min'],
            'city_id' => $store['city_id'],
            'logo' => $room['logo'],
            'address'=>$room['address'],
            'sales'=>$room['month_num'],
            'intro' => $room['intro'],
            'type' => 'hotel',
        );
        if ($this->guess_you_like_model->add($data)) {
            $this->baoSuccess('添加成功',U('guessyoulike/index',array('type'=>'hotel','city_id'=>$store['city_id'])));
        }else {
//           print_r($this->guess_you_like_model->_sql()) ;
            $this->baoError('添加失败');
        }
    }
    public function meishi_goods_add() {
        $meishi_goods_model = D('Meishi');
        $goods_id = I('goods_id','0','intval');
        //var_dump($goods_id);
        $res = $this->guess_you_like_model->where(array('goods_id'=>$goods_id,'type'=>'meishi'))->find();
        $price=D('MeishiProduct')->field('min(price) as price_min')->where(['store_id'=>$goods_id])->group('store_id')->select();
        if ($res) {
            $this->baoError("商品已存在");
        }
        if(!$goods = $meishi_goods_model->find($goods_id)) {
            $this->baoError("商品不存在");
        }
        $store = D('Meishi')->find($goods['store_id']);
        $data = array(
            'goods_name' => $goods['store_name'],
            'goods_id' => $goods['store_id'],
            'price' => $price[0]['price_min'],
            'city_id' => $store['city_id'],
            'logo' => $goods['logo'],
            'intro' => $goods['bulletin'],
            'sales'=>$goods['month_num'],
            'address'=>$goods['address'],
            'type' => 'meishi',
        );
        if ($this->guess_you_like_model->add($data)) {
            $this->baoSuccess('添加成功',U('guessyoulike/index',array('type'=>'meishi','city_id'=>$store['city_id'])));
        }else {
            $this->baoError('添加失败');
        }
    }
    public function house_goods_add() {
        $goods_model = D('House');
        $goods_id = I('goods_id','0','intval');
        $photo=I('photo','0','intval');
        $res = $this->guess_you_like_model->where(array('goods_id'=>$goods_id,'type'=>'house'))->find();
        if ($res) {
            $this->baoError("商品已存在");
        }
        if(!$goods = $goods_model->find($goods_id)) {
            $this->baoError("商品不存在");
        }
        $data = array(
            'goods_name' => $goods['product_name'],
            'goods_id' => $goods['product_id'],
            'price' => $goods['house_price'],
            'city_id' => $goods['city_id'],
            'logo' => $photo,
            'intro' => $goods['intro'],
            'sales'=>$goods['sold_num'],
            'address'=>$goods['address'],
            'type' => 'house',
        );
        if ($this->guess_you_like_model->add($data)) {
            $this->baoSuccess('添加成功',U('guessyoulike/index',array('type'=>'house','city_id'=>$goods['city_id'])));
        }else {

            $this->baoError('添加失败');
        }
    }
    public function delete() {
        $id = I('id','0','intval');
        if(!$detail = $this->guess_you_like_model->find($id)) {
            $this->baoError('记录不存在');
        }
        if ($this->guess_you_like_model->delete($id)) {
            $this->baoSuccess('删除成功',U('guessyoulike/index',array('type'=>$detail['type'],'city_id'=>$detail['city_id'])));
        }else {
            $this->baoError('删除失败');
        }
    }
}
