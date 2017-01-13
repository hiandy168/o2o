<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-22
 */
class HotelroomAction extends CommonAction
{ 
    public function _initialize() {
        parent::_initialize();       
    }
    
    public function index()
    {
    	$hotelRoom = D('HotelRoom');
        import('ORG.Util.Page'); 
        $map = array('closed' => $hotelRoom->closed['exist']);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['room_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($hotel_id = (int) $this->_param('hotel_id')) {
            $map['hotel_id'] = $hotel_id;
            $chaoshi = D('Hotel')->find($hotel_id);
            $this->assign('store_name', $chaoshi['store_name']);
            $this->assign('hotel_id', $hotel_id);
        }
        $count = $hotelRoom->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $hotelRoom->where($map)->order(array('room_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $hotel_ids = array();
        foreach ($list as $k => $val) {
            if ($val['hotel_id']) {
                $hotel_ids[$val['hotel_id']] = $val['hotel_id'];       
            }
            $list[$k]['audit'] = $hotelRoom->audit[$val['audit']];
        }
        if ($hotel_ids) {
            $this->assign('hotels', D('Hotel')->itemsByIds($hotel_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
    
    public function delete($room_id = 0) {
    	if (is_numeric($room_id) && ($room_id = (int) $room_id)) {
            $obj = D('HotelRoom');
            if($obj->deleteById($room_id)){
                $this->baoSuccess('删除成功！', U('hotelroom/index'));
            }
            $this->baoError("删除失败");
        } else {
            $room_id = $this->_post('room_id', false);
            if (is_array($room_id)) {
                $obj = D('HotelRoom');
                foreach ($room_id as $id) {
                    $obj->deleteById($id);
                }
                $this->baoSuccess('删除成功！', U('hotelroom/index'));
            }
            $this->baoError('请选择要删除的房间');
        }
    }
    
    //查看
    public function examine($room_id = 0)
    {
    	if (is_numeric($room_id) && ($room_id = (int) $room_id)) {
    		$hotelRoom = D('HotelRoom');
    		$detail = D('HotelRoom r')->field('r.*,h.store_name')
    								  ->join('bao_hotel as h on h.hotel_id=r.hotel_id')
						    		  ->where(array('room_id' =>$room_id, 'r.closed' =>$hotelRoom->closed['exist']))
						    		  ->find();		    
    		if (!$detail) {
    			$this->error('你查看的房间不存在', U('hotelroom/index'));
    		}
    		$this->assign('audit', $hotelRoom->audit);
    		$this->assign('roomCate', $hotelRoom->roomCate);
    		$this->assign('roomType', $hotelRoom->roomType);
    		$this->assign('detail', $detail);
    		$this->display();
    	} else {
    		$this->error('操作错误', U('hotelroom/index'));
    	}
    }
    
    public function check()
    {
    	if (IS_POST) {
    		$post = I('post.');
    		if (empty($post['id'])) {
    			$this->error('操作错误！', U('hotelroom/index'));
    		}
    		$id = intval($post['id']);
    		if (empty($post['audit'])) {
    			$this->baoError('选择审核状态');
    		}
    		$data['audit'] = $post['audit'];
    		$data['examine_mark'] = htmlspecialchars($post['examine_mark']);
    		if (D('HotelRoom')->where(array('room_id' => $id))->save($data)) {
    			$this->baoSuccess('操作成功', U('hotelroom/index'));
    		}
    		$this->error('操作错误');
    	}
    }
}