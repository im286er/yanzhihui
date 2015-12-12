<?php
return array(
    /**
     * 模块相关配置
     */
    'MODULE_DENY_LIST'     => array('Common', 'Runtime'), //禁止访问的模块列表
    'MODULE_ALLOW_LIST'    => array('Home', 'Api', 'Manager', 'Account'), //允许访问的模块列表
    'DEFAULT_MODULE'       => 'Home', //默认模块

    /**
     * URL配置
     */
    'URL_MODEL'            => 2,
    'URL_HTML_SUFFIX'      => '', //定义伪静态
    'URL_CASE_INSENSITIVE' => false, //区分大小写

    /* 上传相关配置 */
    'FILE_UPLOAD'          => array(
        'mimes'    => '',                //允许上传的文件MiMe类型
        'maxSize'  => 1024 * 1024 * 3, //上传的文件大小限制 (0-不做限制)
        'exts'     => array('jpg', 'jpeg', 'gif', 'png'), //允许上传的文件后缀
        'autoSub'  => false,             //自动子目录保存文件
        'savePath' => './Temp/'          //保存路径
    ),

    /**
     * 异常处理
     */
    'ERROR_PAGE'           => '/Empty/404.html',

    /**
     * 语言设置
     */
    'LANG_SWITCH_ON'       => true, //开启语言包功能

    /**
     * 数据库配置
     */
    'DB_TYPE'              => 'mysql',     // 数据库类型
    'DB_HOST'              => '127.0.0.1', // 服务器地址
    //'DB_HOST'              => '112.74.128.210',
    'DB_NAME'              => 'yanzhihui', // 数据库名
    //'DB_USER'              => 'yanzhihui', // 用户名
    //'DB_PWD'               => 'T53fzKKTe9aKAjBr', // 密码
    'DB_USER'              => 'root', // 用户名
    'DB_PWD'               => '123', // 密码
    'DB_PORT'              => '3306',      // 端口
    'DB_PREFIX'            => 'xian_',     // 数据库表前缀
    'DB_CHARSET'           => 'utf8mb4',   // 字符集
    'DB_DEBUG'             => false,       // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
    'DB_PARAMS'            => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL), // 数据库字段名大小写

    /**
     * 日志设置
     */
    'LOG_RECORD'           => true, //默认不记录日志
    'LOG_EXCEPTION_RECORD' => true, //是否记录异常信息日志

    /**
     * 静态规则的定义
     */
    'HTML_FILE_SUFFIX'     => '.html', //默认静态文件后缀

    /**
     * 接口设置
     */
    /* 极光推送 */
    'API_PUSH_JPUSH'       => array(
        'key'           => '802287df91ef8451009dc51c',
        'secret'     => '2f2045b2abd7f0f7b16f5dcd'
    ),
    /* 短信接口 */
    'API_SMS'              => array(
        'accounts_id'   => '8a48b5514f73ea32014f74eadbc00233', //SMS主帐号,对应开官网发者主账号下的 ACCOUNT SID
        'account_token' => 'cd284502ecfd4da0b2b96e13f3d6ed1e', //SMS主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        'app_id'        => '8a48b5514fba2f87014fcc6fca052a71', //SMS应用Id,在官网应用列表中点击应用,对应应用详情中的APP ID,在开发调试的时候,可以使用官网自动为您分配的测试 Demo的APP ID
        'server_ip'     => 'app.cloopen.com',                  //SMS请求地址 沙盒环境(用于应用开发调试):sandboxapp.cloopen.com,生产环境(用户应用上线使用):app.cloopen.com
        'server_port'   => '8883',                             //SMS请求端口,生产环境和沙盒环境一致
        'soft_version'  => '2013-12-26',                       //REST版本号,在官网文档REST介绍中获得
        'lost_time'     => '1800'                              //30分钟
    ),
    /* 环信IM接口 */
    'EASEMOB'              => array(
        'AppKey'         => 'yanzhihui/yanzhihui',
        'client_id'      => 'YXA6oCNYQD_wEeWGpcfvtJTsFg',
        'client_secret'  => 'YXA66IriRGb5eYmRPF4dTFkCPpmYcmQ',
        'EASEMOB_PREFIX' => 'yanzhihui_'
    ),

    /**
     * 自定义设置
     */
    'APP_FLAG'             => 'YANZHIHUI',
    'APP_URL'              => 'http://www.yanzhihui.cn',
);
