<?php
class CommonAction extends Action{
	public function _initialize(){
		// 获取当前主题的模版路径
        define('THEME_PATH', BASE_PATH . '/themes/default/Meishi/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Meishi/');
	}
}