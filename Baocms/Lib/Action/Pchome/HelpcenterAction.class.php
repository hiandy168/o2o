<?php
class  HelpcenterAction extends  CommonAction{
   
    public function index($cate_id = 0, $article_id = 0){
        // 数量判断
        $getArticles = M('Article')
            ->field(['article_id', 'title', 'create_time'])
            ->where(['cate_id' => $cate_id])
            ->select();
        // 帮助信息分类
        $getHelpCate = D('ArticleCate')
            ->field(['cate_id', 'cate_name', 'parent_id'])
            ->where(['closed' => 0])
            ->order('orderby ASC')
            ->select();
        $this->assign('helpCates', $getHelpCate);

        if(count($getArticles) == 1 || (count($getArticles) == 0 && $article_id != 0)){
            if(count($getArticles) == 1){
                $article_id = $getArticles[0]['article_id'];
            }
            if ($article_id = intval($article_id)) {
                $obj = D('Article');
                if (!$detail = $obj->find($article_id)) {
                    $this->error('没有该文章');
                }
                $cates = D('Articlecate')->fetchAll();
                $obj->updateCount($article_id, 'views');

                //回复列表
//                import('ORG.Util.Pageabc'); // 导入分页类
//                $count =  D('Articlecomment')->where(array('post_id'=>$article_id,'parent_id'=>0))->count();   // 获取评论总数
//                $Page = new Page($count, 15);   // 实例化分页类 传入总记录数和每页显示的记录数
//                $show = $Page->show();   // 分页显示输出
//                $this->assign('count',$count);
//                $list=array();
//                $list=$this->getCommlist($article_id,0,$Page->firstRow,$Page->listRows);   // 获取评论列表
//                $this->assign("list",$list);
//                $this->assign('page', $show);   // 赋值分页输出

                $this->assign('detail', $detail);
                $this->assign('parent_id', D('Articlecate')->getParentsId($detail['cate_id']));
                $this->assign('cates', $cates);
                $this->assign('cate',$cates[$detail['cate_id']]);
                $this->seodatas['title'] = $detail['title'];
                $this->seodatas['cate_name'] = $cates[$detail['cate_id']];
                $this->seodatas['keywords'] = $detail['keywords'];
                if(!empty($detail['desc'])){
                    $this->seodatas['desc'] = $detail['desc'];
                }else{
                    $this->seodatas['desc'] = bao_msubstr($detail['details'],0,200,false);
                }
                $this->assign('listDetail', 1);
                $this->display();
            }
        }elseif(count($getArticles) > 1){
            // 该类的列表
            $this->assign('articles', $getArticles);
            $this->assign('listDetail', 2);
            $this->display();
        }else{
            $this->error('该类没有文章');
        }

    }

    public function right_content(){
        $this->display('right_content');
    }

    public function detials(){
        $this->display();
    }

    /**
     *递归获取评论列表
     */
    protected function getCommlist($post_id,$parent_id = 0,$start,$end,&$result = array()){
        $map = array();
        $map['post_id'] = $post_id;
        $map['parent_id'] = $parent_id;
        if($parent_id != 0){
            $arr = D('Articlecomment')->where($map)->order("zan desc")->select();
        }else{
            $arr = D('Articlecomment')->where($map)->order("zan desc")->limit($start.','.$end)->select();
        }

        if(empty($arr)){
            return array();
        }
        foreach ($arr as $cm) {
            $thisArr=&$result[];
            $cm["children"] = $this->getCommlist($cm["post_id"],$cm["comment_id"],$thisArr);
            $thisArr = $cm;
        }
        return $result;
    }

    
}