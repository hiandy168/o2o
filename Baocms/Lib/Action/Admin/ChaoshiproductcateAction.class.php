<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiproductcateAction extends CommonAction {

	public function ajax($shop_id=0){
        $datas = D('Chaoshiproductcate')->where(array('shop_id'=>$shop_id))->select();
        $str = '';
        foreach($datas as $var){     
           
            $str.='<option value="'.$var['cate_id'].'">'.$var['cate_name'].'</option>'."\n\r";                
           
        }
        echo $str;die;
    }

}
