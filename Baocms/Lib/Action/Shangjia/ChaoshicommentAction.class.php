<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshicommentAction extends ChaoshiAction {



	public function _initialize() {
		parent::_initialize();

	}

	public function index() {
		$chaoshicomment_model = D('Chaoshicomment');
		import('ORG.Util.Page'); // 导入分页类

		$type = I('get.type');
		if(!in_array($type, array('h', 'm', 'l', ''))){
			$this->baoError('评论星级错误');
		}

		$map = array('store_id'=>$this->chaoshi['store_id']);
		$counts = $chaoshicomment_model
		->field("score, COUNT('comment_id') as cou")
		->where($map)
		->group('score')
		->select();

		// 统计每个级别的评论总数
		$sumScore = $highScore = $middleScore = $lowerScore = 0;
		foreach ($counts as $count) {
			if(in_array($count['score'], array(0, 1, 2, 3, 4, 5))){
				$sumScore += $count['cou'];
			}

			// 商品的每一种状态都对应唯一的数值，此处对数值序号进行重新排序，保证序号正确性
			switch($count['score']){
				case 0:
					$lowerScore += $count['cou'];
					break;
				case 1:
					$lowerScore += $count['cou'];
					break;
				case 2:
					$middleScore += $count['cou'];
					break;
				case 3:
					$middleScore += $count['cou'];
					break;
				case 4:
					$middleScore += $count['cou'];
					break;
				case 5:
					$highScore += $count['cou'];
					break;
			}
		}

		switch ($type){
			case '':
				$Page = new Page($sumScore, 20);   // 实例化分页类 传入总记录数和每页显示的记录数
				$show = $Page->show();   // 分页显示输出
				$lists = $chaoshicomment_model->where($map)->relation(true)
				->order(array('create_time' => 'DESC'))->limit($Page->firstRow, $Page->listRows)->select();
				break;
			case 'h':
				$Page = new Page($highScore, 20);
				$show = $Page->show();
				$map = array('store_id'=>$this->chaoshi['store_id'], 'score'=>array('eq', '5'));
				$lists = $chaoshicomment_model->relation(true)->where($map)->relation(true)
				->order(array('create_time' => 'DESC'))->limit($Page->firstRow, $Page->listRows)->select();
				break;
			case 'm':
				$Page = new Page($middleScore, 20);
				$show = $Page->show();
				$map = array('store_id'=>$this->chaoshi['store_id'], 'score'=>array('between', '2, 4'));
				$lists = $chaoshicomment_model->relation(true)
				->where($map)->order(array('create_time' => 'DESC'))->limit($Page->firstRow, $Page->listRows)->select();
				break;
			case 'l':
				$Page = new Page($lowerScore, 20);
				$show = $Page->show();
				$map = array('store_id'=>$this->chaoshi['store_id'], 'score'=>array('between', '0, 1'));
				$lists = $chaoshicomment_model->relation(true)->where($map)->relation(true)
				->order(array('create_time' => 'DESC'))->limit($Page->firstRow, $Page->listRows)->select();
				break;
		}

		$this->assign('all_count', $sumScore);
		$this->assign('h_count', $highScore);
		$this->assign('m_count', $middleScore);
		$this->assign('l_count', $lowerScore);
		$this->assign('type',$type);
		$this->assign('list', $lists); // 赋值数据集
		$this->assign('page', $show); // 赋值分页输出
		$this->display(); // 输出模板
	}

	public function reply($comment_id) {
		$comment_id = (int) $comment_id;
		$detail = D('Chaoshicomment')->find($comment_id);
		if (empty($detail) || $detail['store_id'] != $this->chaoshi['store_id']) {
			$this->error('没有该评论');
		}
		if ($this->isPost()) {
			if ($reply = I('reply')) {
				$data = array('comment_id' => $comment_id, 'reply' => $reply);
				if (D('Chaoshicomment')->save($data)) {
					$this->success('回复成功', U('chaoshicomment/index'));
				}
			}
			$this->error('请填写回复');
		} else {
			$this->assign('detail', $detail);
			$this->display();
		}
	}

}
