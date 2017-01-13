<?php
    return  array(
        //'DB_DEPLOY_TYPE'=>1,
        //'DB_RW_SEPARATE'=>true,
        'DB_TYPE'   =>  'mysqli',   
        'DB_HOST'   =>  '192.168.0.254',
        //'DB_HOST'   =>  'www.haojilai.com',
        'DB_NAME'   =>  'tc_haojilai',//数据库名字
        'DB_USER'   =>  'root',//数据库用户名
       // 'DB_USER'   =>  'amprotc',//数据库用户名
        'DB_PWD'    =>  '',//数据库密码
        //'DB_PWD'    =>  'haojilai_6777_tcro',//数据库密码
        'DB_PORT'   =>  '3306' ,
        'DB_CHARSET'=>  'utf8',
        'DB_PREFIX' =>  'bao_',
             //图片数据库配置
        'DB_CONFIG3' => array(
            'db_type'  => 'mysql',
            'db_user'  => 'root',
            'db_pwd'   => '',
            'db_host'  => '192.168.0.123',
            'db_port'  => '3306',
            'db_name'  => 'piclibaray',
            'db_prefix'  => 'pic_',
        //    'close_fenbu'=>1
        ),
        'AUTH_KEY'  =>  '6d107312f6c254e06451363de506db62', //这个KEY只是保证部分表单在没有SESSION 的情况下判断用户本人操作的作用
        'BAO_KEY'   => '',
        //写数据库
        'DB_W_CONFIG'=>"mysql://ampwrtc:hjl,.1234tc@10.0.0.250:3306/tc_haojilai",
        //读数据库
        'DB_R_CONFIG'=>"mysql://amprotc:hjl,.1234tc@10.0.0.250:3306/tc_haojilai",
    );