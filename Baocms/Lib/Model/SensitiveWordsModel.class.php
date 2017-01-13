<?php
/**
 * 敏感词检测模型
 * 作者：刘弢
 */
class SensitiveWordsModel extends CommonModel
{
/**
 * 过滤敏感词
 */    
    public function filter($text){
        $words = $this->cache()->select();
        $badwords = array();
        foreach ($words as $k => $v){
            $badwords[] = $v['words'];
        }
        $badwordlist = array_combine($badwords,array_fill(0,count($badwords),'*'));
        
        $str = strtr($text, $badwordlist);
        return $str;
    }
/**
 * 检测敏感词
 * @param 检测文本 $text
 * @return boolean
 */    
    public function check_word($text){
        $words = $this->fetchAll();
        $badwords = array();
        foreach ($words as $k => $v){
            $badwords[] = $v['words'];
        }
        $badwordlist="/".implode("|",$badwords)."/i";       
        if(preg_match($badwordlist, $text)){
            return false;
        }else {
            return true;
        }
    }
}