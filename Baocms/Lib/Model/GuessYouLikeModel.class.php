<?php
/*
 * 首页推荐模型
 * 作者：刘弢
 * QQ：473139299
 */
class GuessYouLikeModel extends CommonModel{
    public function get_type() {
        return array(
            'meishi' => '美食',
            'hotel' => '酒店',
            'house' => '房产',
        );
    }
}