<?php

/**
 *描述：前端初始化控制器 
 *作者：王恒
 *时间：2016-4-19
 *联系方式：QQ337886915
 */

class CommonAction extends Action

{

	protected $uid = 0;

	protected $member = array();

	protected $_CONFIG = array();

	protected $seodatas = array();

	protected $shopcates = array();

	protected $citys = array();

	protected $areas = array();

	protected $bizs = array();

	protected $template_setting = array();

	protected $city_id = 0;

	protected $city = array();



	protected function _initialize(){
		/* if (is_mobile()) {
			 $url_mobile = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/mobile'.$_SERVER['REQUEST_URI'];
            header('Location:'.$url_mobile);
            die;
        }*/
        if(empty($_COOKIE['PHPSESSIDS'])){
        	setcookie('PHPSESSIDS',session_id(),strtotime(date("Y-m-d",time()))+2*86400,'/',C('COOKIE_DOMAIN'));
        }
       // $this->display();
		define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
		$this->_CONFIG = D('Setting')->fetchAll();
		$this->citys = D('City')->fetchAll();
		$this->assign('citys', $this->citys);
		$this->city_id = cookie('city_id');

        if (empty($this->city_id)) {
			import('ORG/Net/IpLocation');
			$IpLocation = new IpLocation('UTFWry.dat');
        	$result = $IpLocation->getlocation($_SERVER['REMOTE_ADDR']);
		//	$result = $IpLocation->getlocation("58.60.63.216");
			foreach ($this->citys as $val) {
				if (strstr($result['country'], $val['name'])) {
					$city = $val;
					$this->city_id = $val['city_id'];
					break;
				}
			}
			if (empty($city)) {
                //自动保存当前地址
                 $city = D('City')->autoregister($_SERVER['REMOTE_ADDR']);
                // $city = D('City')->autoregister('58.60.63.216');
                    cookie('city_id',$city['city_id']);
				$this->city_id = $city['city_id'];
				$city = $city;
               // header('Refresh: 1');
			}
		}
		else {
			$city = $this->citys[$this->city_id];
		}
		$this->city = $city;
		searchwordfrom();
		$this->uid = getUid();
		if (!empty($this->uid)) {
			$this->member = D('Users')->find($this->uid);
		}
		$this->shopcates = D('Shopcate')->fetchAll();
		$this->assign('shopcates', $this->shopcates);
		$this->Tuancates = D('Tuancate')->fetchAll();
        //var_dump($this->Tuancates);
		$this->assign('tuancates', $this->Tuancates);
		$this->areas = D('Area')->fetchAll();
		$this->assign('areas', $this->areas);
		$limit_area = array();
		foreach ($this->areas as $k => $val) {
			if ($val['city_id'] == $this->city_id) {
				$limit_area[] = $val['area_id'];
			}
		}

		$this->bizs = D('Business')->fetchAll();
		$this->assign('bizs', $this->bizs);
		$this->assign('limit_area', $limit_area);
		$this->assign('ctl', strtolower(MODULE_NAME));
		$this->assign('act', ACTION_NAME);
		$this->assign('nowtime', NOW_TIME);
		$this->assign('city_name', $city['name']);
		$this->assign('city_id', $this->city_id);
		$this->getTemplateTheme();
		$this->template_setting = D('Templatesetting')->detail($this->theme);
		$this->assign('CONFIG', $this->_CONFIG);
		$this->assign('MEMBER', $this->member);
		$this->assign('today', TODAY);
		$city_ids = array('0', $this->city_id);
		$city_ids = join(',', $city_ids);
		$this->assign('city_ids', $city_ids);
		// 帮助导航
		$this->assign('helpCates', $helpCates = $this->helpCates());
	}

	protected function helpCates(){
		// 获取帮助列表
		$sql = "SELECT `cate_id`,`cate_name`,`parent_id` FROM `bao_article_cate` WHERE `parent_id`=(SELECT `cate_id` FROM `bao_article_cate` WHERE `parent_id`=0 AND `cate_name`='系统信息') AND `closed`=0 ORDER BY `orderby` LIMIT 0,6";
		$getHelpCates = M('ArticleCate')->query($sql);
//        var_dump($getHelpCates);
		// 子分类
		$ids = [];
		foreach ($getHelpCates as &$getHelpCate){
			$ids[] = $getHelpCate['cate_id'];
		}
		// 类名
		$getHelpCates[0]['icon'] = 'foot-icon foot-icon-user';
		$getHelpCates[1]['icon'] = 'foot-icon foot-icon-service';
		$getHelpCates[2]['icon'] = 'foot-icon foot-icon-pay';
		$getHelpCates[3]['icon'] = 'foot-icon foot-icon-love';
		$getHelpCates[4]['icon'] = 'foot-icon foot-icon-set';
		$getHelpCates[5]['icon'] = 'foot-icon foot-icon-cooperate';
		$sql = "SELECT `cate_id`,`cate_name`,`parent_id` FROM `bao_article_cate` WHERE `parent_id` IN (".implode(',', $ids).") AND `closed`=0 ORDER BY `orderby`";
		$getHelpSons = M('ArticleCate')->query($sql);
		// 每个分类限制4个
//        $helpSons = [];
		foreach ($getHelpCates as &$getHelpCate){
			$num = 0;
			foreach ($getHelpSons as &$getHelpSon){
				if($getHelpCate['cate_id'] == $getHelpSon['parent_id'] && $num < 5){
					$getHelpCate['son'][] = $getHelpSon;
				}
			}
			unset($num);
		}
		return $getHelpCates;
	}



	private function seo()

	{

		$seo = D('Seo')->fetchAll();

		$this->seodatas['sitename'] = $this->_CONFIG['site']['sitename'];

		$this->seodatas['tel'] = $this->_CONFIG['site']['tel'];

		$key = strtolower(MODULE_NAME . '_' . ACTION_NAME);



		if (isset($seo[$key])) {

			$this->assign('seo_title', $this->tmplToStr($seo[$key]['seo_title'], $this->seodatas));

			$this->assign('seo_keywords', $this->tmplToStr($seo[$key]['seo_keywords'], $this->seodatas));

			$this->assign('seo_description', $this->tmplToStr($seo[$key]['seo_desc'], $this->seodatas));

		}

		else {

			$this->assign('seo_title', $this->_CONFIG['site']['title']);

			$this->assign('seo_keywords', $this->_CONFIG['site']['keyword']);

			$this->assign('seo_description', $this->_CONFIG['site']['description']);

		}

	}



	private function tmplToStr($str, $datas)

	{

		return tmpltostr($str, $datas);

	}



	public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')

	{

		$this->seo();

		parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');

	}



	private function parseTemplate($template = '')

	{

		$depr = c('TMPL_FILE_DEPR');

		$template = str_replace(':', $depr, $template);

		$theme = $this->getTemplateTheme();

		define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Pchome/');

		define('THEME_PATH', BASE_PATH . '/themes/default/Pchome/');

		define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Pchome/');



		if ('' == $template) {

			$template = strtolower(MODULE_NAME) . $depr . strtolower(ACTION_NAME);

		}

		else if (false === strpos($template, '/')) {

			$template = strtolower(MODULE_NAME) . $depr . strtolower($template);

		}



		$file = NOW_PATH . $template . c('TMPL_TEMPLATE_SUFFIX');



		if (file_exists($file)) {

			return $file;

		}



		return THEME_PATH . $template . c('TMPL_TEMPLATE_SUFFIX');

	}



	private function getTemplateTheme()

	{

		define('THEME_NAME', 'default');



		if ($this->theme) {

			$theme = $this->theme;

		}

		else {

			$theme = D('Template')->getDefaultTheme();



			if (c('TMPL_DETECT_THEME')) {

				$t = c('VAR_TEMPLATE');



				if (isset($_GET[$t])) {

					$theme = $_GET[$t];

				}

				else if (cookie('think_template')) {

					$theme = cookie('think_template');

				}



				if (!in_array($theme, explode(',', c('THEME_LIST')))) {

					$theme = c('DEFAULT_THEME');

				}



				cookie('think_template', $theme, 864000);

			}



			$this->theme = $theme;

		}



		return $theme ? $theme . '/' : '';

	}



	protected function baoMsg($message, $jumpUrl = '', $time = 3000, $callback = '', $parent = true)

	{

		$parents = ($parent ? 'parent.' : '');

		$str = '<script>';

		$str .= $parents . 'bmsg("' . $message . '","' . $jumpUrl . '","' . $time . '","' . $callback . '");';

		$str .= '</script>';

		exit($str);

	}

//开始

 protected function niuSuccess($message, $jumpUrl = '', $time = 3000, $parent = true) {
        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        $str .=$parent . 'success("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str.='</script>';
        exit($str);
    }
	

    protected function niuJump($jumpUrl) {
        $str = '<script>';
        $str .='parent.jumpUrl("' . $jumpUrl . '");';
        $str.='</script>';
        exit($str);
    }

    protected function niuErrorJump($message, $jumpUrl = '', $time = 3000) {
        $str = '<script>';
        $str .='parent.error("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str.='</script>';
        exit($str);
    }

    protected function niuError($message, $time = 3000, $yzm = false, $parent = true) {

        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        if ($yzm) {
            $str .=$parent . 'error("' . $message . '",' . $time . ',"yzmCode()");';
        } else {
            $str .= $parent . 'error("' . $message . '",' . $time . ');';
        }
        $str.='</script>';
        exit($str);
    }

    protected function niuLoginSuccess() { //异步登录
        $str = '<script>';
        $str .='parent.parent.LoginSuccess();';
        $str.='</script>';
        exit($str);
    }

//结束



	protected function baoOpen($message, $close = true, $style)

	{

		$str = '<script>';

		$str .= 'parent.bopen("' . $message . '","' . $close . '","' . $style . '");';

		$str .= '</script>';

		exit($str);

	}



	protected function baoSuccess($message, $jumpUrl = '', $time = 3000, $parent = true)

	{

		$this->baoMsg($message, $jumpUrl, $time, '', $parent);

	}



	protected function baoJump($jumpUrl)

	{

		$str = '<script>';

		$str .= 'parent.jumpUrl("' . $jumpUrl . '");';

		$str .= '</script>';

		exit($str);

	}



	protected function baoErrorJump($message, $jumpUrl = '', $time = 3000)

	{

		$this->baoMsg($message, $jumpUrl, $time);

	}



	protected function baoError($message, $time = 3000, $yzm = false, $parent = true)

	{

		$parent = ($parent ? 'parent.' : '');

		$str = '<script>';



		if ($yzm) {

			$str .= $parent . 'bmsg("' . $message . '","",' . $time . ',"yzmCode()");';

		}

		else {

			$str .= $parent . 'bmsg("' . $message . '","",' . $time . ');';

		}



		$str .= '</script>';

		exit($str);

	}



	protected function baoLoginSuccess()

	{

		$str = '<script>';

		$str .= 'parent.parent.LoginSuccess();';

		$str .= '</script>';

		exit($str);

	}



	protected function ajaxLogin()

	{

		if ($mini = $this->_get('mini')) {

			exit('0');

		}



		$str = '<script>';

		$str .= 'parent.ajaxLogin();';

		$str .= '</script>';

		exit($str);

	}



	protected function checkFields($data = array(), $fields = array())

	{

		foreach ($data as $k => $val) {

			if (!in_array($k, $fields)) {

				unset($data[$k]);

			}

		}



		return $data;

	}



	protected function ipToArea($_ip)

	{

		return iptoarea($_ip);

	}



	protected function getMenus()

	{

		$menus = $this->memberMenu();

		return $menus;

	}



	protected function memberMenu()

	{

		return array(

	'account' => array(

		'name'  => '账户管理',

		'url'   => u('member/password'),

		'items' => array(

			'info' => array(

				'name'  => '我的账户',

				'items' => array(

					array('name' => '昵称设置', 'url' => u('member/nickname')),

					array('name' => '修改密码', 'url' => u('member/password')),

					array('name' => '修改头像', 'url' => u('member/face')),

					array('name' => '收货地址', 'url' => u('member/myaddress'))

					)

				),

			'auth' => array(

				'name'  => '认证管理',

				'items' => array(

					array('name' => '手机认证', 'url' => u('member/mobile')),

					array('name' => '邮件认证', 'url' => u('member/email'))

					)

				),

			'logs' => array(

				'name'  => '日志管理',

				'items' => array(

					array('name' => '积分日志', 'url' => u('member/integral')),

					array('name' => '金块日志', 'url' => u('member/goldlogs')),

					array('name' => '余额日志', 'url' => u('member/moneylogs')),

					array('name' => '代金券日志', 'url' => u('member/rechargecard'))

					)

				)

			)

		),

	'consume' => array(

		'name'  => '消费管理',

		'url'   => u('member/order'),

		'items' => array(

			'order' => array(

				'name'  => '我的订单',

				'items' => array(

					array('name' => '抢购订单', 'url' => u('member/order')),

					array('name' => '订餐订单', 'url' => u('member/eleorder')),

					array('name' => '商城订单', 'url' => u('member/goods'))

					)

				),

			'card'  => array(

				'name'  => '票券积分',

				'items' => array(

					array('name' => '我的抢购券', 'url' => u('member/ordercode')),

					array('name' => '优惠券下载', 'url' => u('member/coupon')),

					array('name' => '我的兑换', 'url' => u('member/exchange')),

					array('name' => '我的预约', 'url' => u('member/yuyue'))

					)

				)

			)

		),

	'other'   => array(

		'name'  => '其他管理',

		'url'   => u('member/order'),

		'items' => array(

			'order'    => array(

				'name'  => '充值管理',

				'items' => array(

					array('name' => '余额充值', 'url' => u('member/money')),

					array('name' => '代金券充值', 'url' => u('member/recharge')),

					array('name' => '金块充值', 'url' => u('member/gold'))

					)

				),

			'cash'     => array(

				'name'  => '提现管理',

				'items' => array(

					array('name' => '提现记录', 'url' => u('member/cashlog')),

					array('name' => '申请提现', 'url' => u('member/cash'))

					)

				),

			'huodong'  => array(

				'name'  => '活动信息',

				'items' => array(

					array('name' => '我的活动', 'url' => u('member/myactivity')),

					array('name' => '我的信息', 'url' => u('member/life')),

					array('name' => '我的点评', 'url' => u('member/dianping')),

					array('name' => '我的关注', 'url' => u('member/favorites')),

					array('name' => '我的分享', 'url' => u('member/bbs'))

					)

				),

			'tuiguang' => array(

				'name'  => '代理商',

				'items' => array(

					array('name' => '商户列表', 'url' => u('member/myshop')),

					array('name' => '代理成果', 'url' => u('member/tongji'))

					)

				)

			)

		)

	);

	}

}



?>

