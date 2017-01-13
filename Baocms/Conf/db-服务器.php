<?php
    return  array(
        'DB_DEPLOY_TYPE'=>1,
        'DB_RW_SEPARATE'=>true,
        'DB_TYPE'   =>  'mysqli',
        'DB_HOST'   =>  '10.0.0.250,10.0.0.251',
        'DB_NAME'   =>  'tc_haojilai,tc_haojilai',//数据库名字
        'DB_USER'   =>  'admin_user,amprotc',//数据库用户名
        'DB_PWD'    =>  'hjl123456,haojilai_6777_tcro',//数据库密码
        'DB_PORT'   =>   3306 ,
        'DB_CHARSET'=>  'utf8',
        'DB_PREFIX' =>  'bao_',
         //图片数据库配置
        'DB_CONFIG3' => array(
            'db_type'  => 'mysqli',
            'db_user'  => 'admin',
            'db_pwd'   => 'hjl,.1234',
            'db_host'  => '10.0.0.250',
            'db_port'  => '3306',
            'db_name'  => 'piclibaray',
            'db_prefix'  => 'pic_',
            'close_fenbu'=>1
        ),
        'AUTH_KEY'  =>  '6d107312f6c254e06451363de506db62', //这个KEY只是保证部分表单在没有SESSION 的情况下判断用户本人操作的作用
        'BAO_KEY'   => '',


        
    );