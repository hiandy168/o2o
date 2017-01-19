<?php
class  SystemcontentAction extends  CommonAction{

    public function index() {
        $content_id = (int) $this->_get('content_id');
        $titles = D('Systemcontent')->field(['content_id', 'title'])->order('orderby ASC')->select();
        $nums = [];
        foreach ($titles as $title){
            $nums[] = $title['content_id'];
        }
        if(!in_array($content_id, $nums)){
            $content_id = $titles[0]['content_id'];
        }

        $content = D('Systemcontent')->field(['content_id', 'title', 'contents', 'create_time'])->where(['content_id' => $content_id])->find();
        $this->assign('titles', $titles);
        $this->assign('content', $content);
        $this->display();
    }


}