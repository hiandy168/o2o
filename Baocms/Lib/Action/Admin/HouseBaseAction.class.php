<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-19
 */
class HouseBaseAction extends CommonAction
{
	private $update_fields = array(
								  'city_id',
								  'house_name',
								  'delivery_time',
								  'open_time',
								  'price',
								  'property_fee',
								  'build_area',
								  'carport',
								  'greening_rate',
								  'plot_ratio',
								  'developers',
								  'property_company',
								  'property_deadline',
								  'lng',
								  'lat',
								  'details',
								);
	const PAGE_SIZE = 20;
								
	public function index()
	{
		$HouseBase = D('HouseBase h');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword','htmlspecialchars');
        $city_id = $this->_param('city_id', 'intval');
        if($keyword){
            $map['h.house_name'] = array('LIKE', '%'.$keyword.'%');
        } 
        if($city_id){
            $map['h.city_id'] = $city_id;
        }           
        $count = $HouseBase->where($map)->count(); 
        $Page = new Page($count, self::PAGE_SIZE); 
        $show = $Page->show(); 
        $list = $HouseBase->field('h.*,c.name as city_name')->join('bao_city as c on h.city_id=c.city_id')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $City = D('City');
		$citys = $City->field('city_id,name')->where(array('is_open' => 1))->order(array('city_id' => 'ASC'))->limit(500)->select();	
		$this->assign('citys', $citys);
        $this->assign('keyword',$keyword);
        $this->assign('city_id',$city_id);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
	}
	
	public function create()
	{
		if (IS_POST) {
			$model = D('HouseBase');
			if (!$model->create()) {
				$this->baoError($model->getError());
			}
			$model->delivery_time = strtotime($model->delivery_time);
			$model->open_time = strtotime($model->open_time);
			$model->create_time = time();
			$admin = $this->_admin;
			$model->create_id = $admin['admin_id'];
			if ($model->add()) {
				$this->baoSuccess('添加成功', U('houseBase/index'));
			} else {
				$this->baoError('添加失败', U('houseBase/create'));
			}
		}
		$City = D('City');
		$list = $City->field('city_id,name')->where(array('is_open' => 1))->order(array('city_id' => 'ASC'))->limit(500)->select();	
		$this->assign('citys', $list);
		$this->display();
 	}
	
	public function update()
	{
		$house_id = I("get.id");
		if (empty($house_id)) {
			$this->error('操作错误', U('houseBase/index'));
		}
		$model = D('HouseBase');
		$data = $model->find($house_id);
		if (!$data) {
			$this->error('操作错误', U('houseBase/index'));
		}
		if (IS_POST) {
			$data = $this->checkFields($_POST, $this->update_fields);
			if (!$model->checkData($data)) {
				$this->baoError($model->error_msg);
			}
			$data['delivery_time'] = strtotime($data['delivery_time']);
			$data['open_time'] = strtotime($data['open_time']);
			if ($model->where(array('id' => $house_id))->save($data)) {
				$this->baoSuccess('添加成功', U('houseBase/index'));
			} else {
				$this->baoError('添加失败');
			}
		}
		$data['delivery_time'] = date('Y-m-d', $data['delivery_time']);
		$data['open_time'] = date('Y-m-d', $data['open_time']);
		$this->assign('data', $data);
		$City = D('City');
		$list = $City->field('city_id,name')->where(array('is_open' => 1))->order(array('city_id' => 'ASC'))->limit(500)->select();	
		$this->assign('citys', $list);
		$this->display();
	}
	
    public function delete($id = 0) {
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('HouseBase');
            $obj->delete($id);
            $this->baoSuccess('删除成功！', U('houseBase/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('HouseBase');
                foreach ($id as $i) {
                    $obj->delete($i);
                }
                $this->baoSuccess('删除成功！', U('houseBase/index'));
            }
            $this->baoError('请选择要删除的楼盘');
        }
    }
}