<?php
define('TODAY', date("Y-m-d"));
$dbconfigs = require BASE_PATH . '/' . APP_NAME . '/Conf/db.php';
$temp = require BASE_PATH . '/' . APP_NAME . '/Conf/site.php';
$dbconfigs = array_merge($temp, $dbconfigs);
$configs = array(

    //'配置项'=>'配置值'

    'APP_GROUP_LIST' => 'Admin,Shangjia,Mobile,Store,Delivery,App,Wuye,Mcenter,Member,Pchome,Common,Meishi,Mobileapp', //项目分组设定

    'DEFAULT_GROUP' => 'Pchome', //默认分组
    'DATA_AUTH_KEY' => 'abcdefg!@#',

    //SESSION 的设置

    'SESSION_AUTO_START' => true,
    //cookie设置
    'COOKIE_DOMAIN' => '.haojilai.cn',


    'DEFAULT_APP' => 'Baocms',

    'LOAD_EXT_FILE' => 'tplfunction', //自动加载

    //URL设置

    'URL_MODEL' => 1,

    'URL_HTML_SUFFIX' => '.html',

    'URL_ROUTER_ON' => true,

    'URL_CASE_INSENSITIVE' => true, //url不区分大小写

    'URL_ROUTE_RULES' => array(),

    'APP_SUB_DOMAIN_DEPLOY' => false,

    //图片库ＵＲＬ
    //'PICLIB_URL'=>"http://img1.haojilai.co/",
    'PICLIB_URL' => "http://picturelib.haojilai.com/",
    'PICLIBUPLOAD_URL' => "http://picturelib.haojilai.com/index.php?s=home/file/upload.html",
    //'PICLIBUPLOAD_URL'=>"http://img1.haojilai.co/index.php?s=home/file/upload.html",

    //默认系统变量

    'VAR_GROUP' => 'g',

    'VAR_MODULE' => 'm',

    'VAR_ACTION' => 'a',

    'VAR_TEMPLATE' => 'theme',
    //模板
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => BASE_SITE_URL . '/static/haojilai',
        'BASE_SITE_URL' => BASE_SITE_URL,
        '__IMG__' => BASE_SITE_URL . '/Public/' . MODULE_NAME . '/' . C('DEFAULT_THEME') . '/images',
        '__CSS__' => BASE_SITE_URL . '/Public/' . MODULE_NAME . '/' . C('DEFAULT_THEME') . '/css',
        '__JS__' => BASE_SITE_URL . '/Public/' . MODULE_NAME . '/' . C('DEFAULT_THEME') . '/js',
        '__PICURL__' => "http://picturelib.haojilai.com/",
        '__PICUPLOADURL__' => "http://picturelib.haojilai.com/index.php?s=home/file/upload.html",

    ),


    //模版设置相关

    'DEFAULT_THEME' => 'default',

    'TMPL_L_DELIM' => '<{',

    'TMPL_R_DELIM' => '}>',

    'TMPL_ACTION_SUCCESS' => 'public/dispatch_jump',

    'TMPL_ACTION_ERROR' => 'public/dispatch_jump',


    'TAGLIB_LOAD' => true,

    'APP_AUTOLOAD_PATH' => '@.TagLib',

    'TAGLIB_BUILD_IN' => 'Cx,Calldata',


    //表单令牌
    'TOKEN_ON' => false, //是否开启令牌验证

    'TOKEN_NAME' => 'token',// 令牌验证的表单隐藏字段名称

    'TOKEN_TYPE' => 'md5',//令牌验证哈希规则
    //首页频道分类
    'pchome_channel_list' => array(
        array(
            'url' => "http://o2o.haojilai.cn/index.php/pchome/index/index.html",
            'title' => '首页',
            'current' => 'index'
        ),
        array(
            'url' => "http://o2o.haojilai.cn/index.php/pchome/tuan/location.html",
            'title' => '身边的好吉来',
            'current' => 'tuan'
        ),
        array(
            'url' => "http://o2o.haojilai.cn/index.php/shop/index.html",
            'title' => '商家',
            'current' => 'shop'
        ),
        array(
            //'url'=>"http://haoshangcheng.haojilai.cn",
            'url' => "#",
            'title' => '好商城',
            'current' => 'mall'
        ),
        array(
            'url' => "http://mobile.haojilai.cn/index.php/Chaoshi/chaoshi/index.html",
            'title' => '社区超市',
            'current' => 'chaoshi'
        ),
        array(
            'url' => "http://mobile.haojilai.cn/index.php/waimai.html",
            'title' => '外卖',
            'current' => 'ele'
        ),
/*        array(
            'url' => "http://o2o.haojilai.cn/index.php/pchome/coupon/index.html",
            'title' => '领劵',
            'current' => 'coupon'
        ),
        array(
            'url' => "http://o2o.haojilai.cn/index.php/community/index.html",
            'title' => '小区',
            'current' => 'community'
        ),*/
    ),
//加密函数KEY
    'DATA_AUTH_KEY' => 'v6uavn2kk6gxs5hvtrzhfpml2d1bqr9i',
);

return array_merge($configs, $dbconfigs);

?>