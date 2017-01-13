<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class housecommentAction extends CommonAction {

    /**peace
     * 显示处理待审查的评论
     *
     */
    public function index() {
        $HouseComment = D('HouseComment');
        import('ORG.Util.Page'); // 导入分页类

        // 显示待审核的评论
        $map = array('closed'=>0);


        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $this->assign('keyword', $keyword);
                $map['contents'] = array('LIKE', '%' . $keyword . '%');
            }

            if ($store_id = (int) $this->_param('store_id')) {
                $map['store_id'] = $store_id;
                if($this->_param('store_name')){
                    $store = M('Chaoshi')->find($store_id);
                    $this->assign('store_name', $store['store_name']);
                    $this->assign('store_id', $store_id);
                }
            }

            // 审核状态搜索
            if ($audit = (int) $this->_param('audit')) {
                if($audit == 100){
                    unset($map['audit']);
                }elseif($audit == 99){
                    $map['audit'] = 0;
                }else{
                    $map['audit'] = $audit;
                }
                $this->assign('audit', $map['audit']);
            }

            if(empty($keyword)){
                unset($map['contents']);
            }
            $temp = $this->_param('store_name');
            if(empty($temp)){
                unset($map['store_id']);
                unset($map['store_name']);
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['contents'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
            if($map['store_id'] == true){
                $store = M('Hotel')->find($map['store_id']);
                $this->assign('store_name', $store['store_name']);
                $this->assign('store_id', $map['store_id']);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('HouseCommentSearchIndexMessage'), $map, 900);
        cookie(md5('HouseCommentSearchIndexMessageComm'), $map['contents'], 900);

        // 分页
        $Page = new Page($HouseComment->where($map)->count(), 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        // 开始联合查询获取评论的数据，包括外表的商品名，超市名，用户名
        $list = $HouseComment
            ->field('*')
            ->where($map)
            ->order(array('audit' => 'ASC', 'comment_id' => 'DESC'))
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
//        var_dump($list[0]);
        $this->assign('audit_2', $map['audit']);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    /**peace
     * @param int $comment_id
     * 评论审查处理 通过
     */
    public function apply($comment_id = 0) {
        self::getApply($comment_id, 1, '该评论已通过审查');
    }

    /**peace
     * @param int $comment_id
     * 评论审查处理 通过
     */
    public function unApply($comment_id = 0) {
        self::getApply($comment_id, 2, '该评论未通过审查');
    }

    /**
     * 评论公共方法
     */
    private function getApply($comment_id, $closed, $words){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $comment_id = $this->_post('comment_id', false);
            if (is_array($comment_id)) {
                $obj = D('HouseComment');
                foreach ($comment_id as $id) {
                    $obj->save(array('comment_id' => $id, 'audit' => $closed));
                }
                $this->baoSuccess($words, U('Housecomment/index'));
            }else {
                $this->baoError('请选择要审查的评论');
            }
        }

        // is_get
        if (is_numeric($comment_id) && ($comment_id = (int) $comment_id)) {
            $obj = D('HouseComment');
            if ($obj->save(array('comment_id' => $comment_id, 'audit' => $closed))){
                $this->baoSuccess($words, U('Housecomment/index'));
            } else {
                $this->baoError('请选择要审查的评论');
            }
        }
    }

    /**peace
     * 初始化搜索引擎，评论列表
     */
    public function initialIndex(){
        cookie(md5('ChaoshiCommentSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('chaoshicomment/index'), 1000);
    }
    
}
