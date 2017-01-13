<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-19
 */

class HouseAttributesAction extends CommonAction
{
	//属性分类	
	private $house_Attributes = array(
									1 => '户型类型',
									2 => '物业类型',
									3 => '装修类型',
									4 => '楼盘类型',
									);
	private function checkType()
	{
		$type = I("get.type");
		if (empty($type)) {
			$this->error('操作错误', U('houseAttributes/index?type=1'));
		}
		$type = intval($type);
		$status = false;
		$attr = '';
		foreach ($this->house_Attributes as $k =>$v) {
			if ($type == $k) {
				$status = true;
				$attr = $v;
			}
		}
		if (!$status) {
			$this->error('操作错误', U('houseAttributes/index?type=1'));
			return $status;
		} else {
			$this->assign('attr', $attr);
			$this->assign('type', $type);
			return $type;
		}	
	}
	
	public function index()
	{
		$type = $this->checkType();
		$list = D('HouseAttributes')->where(array('p_id' => $type))->limit(100)->select();
		$this->assign('list', $list);
		$this->display();
	}
	
	public function create()
	{
		$type = $this->checkType();
		if (IS_POST) {
			if (empty($_POST['value'])) {
				$this->baoError($this->house_Attributes[$type].'不能为空');
			}
			$data['value'] = htmlspecialchars($_POST['value']);
			$data['p_id'] = $type;
			if (D('HouseAttributes')->add($data)) {
				$this->baoSuccess('添加成功', U('houseAttributes/index?type='.$type));
			} else {
				$this->baoError('添加失败', U('houseAttributes/create?type='.$type));
			}
 		}
		$this->display();
	}
	
	public function update()
	{
	    $id = I("get.id");
		if (empty($id)) {
			$this->error('操作错误', U('HouseAttributes/index?type=1'));
		}
		$model = D('HouseAttributes');
		$data = $model->find($id);
		if (!$data) {
			$this->error('操作错误', U('HouseAttributes/index?type=1'));
		}
		$type = $data['p_id'];
		if (IS_POST) {
			if (empty($_POST['value'])) {
				$this->baoError($this->house_Attributes[$type].'不能为空');
			}
			$data['value'] = htmlspecialchars($_POST['value']);
			$data['p_id'] = $type;
			if (D('HouseAttributes')->where(array('id' => $id))->save($data)) {
				$this->baoSuccess('添加成功', U('houseAttributes/index?type='.$type));
			} else {
				$this->baoError('添加失败', U('houseAttributes/create?type='.$type));
			}
		}
		$this->assign('data', $data);
		$this->assign('attr', $this->house_Attributes[$type]);
		$this->display();
	}
	
    public function delete($id = 0, $type = 0) {
    	if (is_numeric($type) && ($type = (int) $type)) {
    	} else {
    		$this->baoError('操作失败！', U('HouseAttributes/index?type=1'));
    	}
     	if (is_numeric($id) && ($id = (int) $id)) {
	            $obj = D('HouseAttributes');
	            $obj->delete($id);
	            $this->baoSuccess('删除成功！', U('HouseAttributes/index?type='.$type));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('HouseAttributes');
                foreach ($id as $i) {
                    $obj->delete($i);
                }
                $this->baoSuccess('删除成功！', U('HouseAttributes/index?type='.$type));
            }
            $this->baoError('请选择要删除的'.$this->house_Attributes[$type]);
        }
    }
}