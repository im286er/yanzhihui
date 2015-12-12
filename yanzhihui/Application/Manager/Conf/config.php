<?php
return array(
    /* 分页设置 */
    'PAGE_NUM'              => 12, //每页多少条
    'ROLL_PAGE'             => 5,  //共显示多少页

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX'        => C('APP_FLAG') . '_Manager', //SESSION前缀
    'COOKIE_PREFIX'         => C('APP_FLAG') . '_Manager', //COOKIE前缀

    /* 管理权限 */
    'AUTH_CONFIG'           => array(
        'AUTH_TYPE'         => 2, //认证方式，1为时时认证；2为登录认证
        /* 权限访问模块 */
        'AUTH_DENY_VISIT'   => array(), //非超管禁止访问的模块
        'AUTH_ALLOW_VISIT'  => array('Index/index', 'Index/home'), //非超管可直接访问的模块
        'AUTH_ALLOW_ACTION' => array('upload', 'upload_editor') //非超管可直接访问的节点
    ),

    /* 超级管理员ID */
    'MANAGER_ADMINISTRATOR' => 1
);