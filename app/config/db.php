<?php
//mysql配置

return array(
    'main' => array(
        //主库配置 可以配置 slave从 例如 slave => array(配置1, 配置2, 配置3)
        'master' => array(
            //mysql 主机地址
            'host' => '127.0.0.1',
            //mysql 表前缀
            'port' => 3301,
            //mysql 数据库名
            'dbname' => 'test',
            //mysql 用户名
            'uname' => 'test',	
            //mysql 密码
            'upwd' => 'test',
            //mysql 编码
            'charset' => 'utf8',
        ),
        //mysql 是否开始调试模式
        'debug' => false,			        
        //mysql 该字段不为空字符串的话 则将调试信息输出到文件中
        'debugFile' => '',
        //mysql 是否显示sql语句错误
        'errReport' => true,
    ),
);
