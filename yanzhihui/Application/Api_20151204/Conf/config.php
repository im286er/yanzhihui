<?php
return array(
    /* 分页设置 */
    'PAGE_NUM'         => 20,  //每页多少条
    'PAGE_NUM_LIST'    => 20,  //每页多少条
    'PAGE_NUM_MAX'     => 100,  //总共最多多少条

    /* 基本配置 */
    'CACHE_TIME'       => 60, //缓存时间(秒)

    /* Auth */
    'AUTH_TOKEN_CHECK' => array(
        'User/edit_info', 'User/edit_password', 'User/authentication_upfile_head', 'User/info', 'User/attention', 'User/fans',
        'UserAttention/add', 'UserAttention/delete',
        'Topic/index_attention',
        'Topic/add',
        'TopicComment/add', 'TopicComment/delete',
        'TopicLike/add',
        'TopicReport/add',
        'UserReport/add',
        'ShopComment/add',
        'ShopLike/add',
        'UserBlocked/add', 'UserBlocked/delete',
        'Coupon/buy',
        'Personal/payorder',
        'ShopUserWent/add',
        'Personal/be_shielded_user',
        'ShopUserWent/unread_count'
    )
);
