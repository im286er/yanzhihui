<?php
return array(
    /* 分页设置 */
    'PAGE_NUM'       => 12, //每页多少条
    'ROLL_PAGE'      => 5,  //共显示多少页

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => C('APP_FLAG') . '_Account', //SESSION前缀
    'COOKIE_PREFIX'  => C('APP_FLAG') . '_Account', //COOKIE前缀
);