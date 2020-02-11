<?php
/* 欢迎使用TCShare v2！这是一个天翼云的目录列表程序。
   在下面填写你的网盘信息，然后授权使用吧！
   v1和v2版本配置文件不兼容，麻烦手动修改一下！但是主题和配置是兼容的~
   v2版本支持多盘哦，可以分别配置多个api(key)和挂载路径(app)了
   @xyToki https://xylog.cn 2020-02-12
   https://github.com/xyToki/TCShare */
global $TC;
/* 多盘配置 */
$TC=[
    "Keys"=>[
        [
            "ID"=>"key1",
            "FD"=>"",
            "AK"=>"",
            "SK"=>"",
            "ACCESS_TOKEN"=>""
        ]
    ],
    "Apps"=>[
        [
            "route"=>"/",           /* 挂载路径 */
            "name"=>"TCShare",      /* 显示名称 */
            "theme"=>"mdui",        /* 设置主题 */
            "base"=>"/",            /* 起始目录 */
            "key"=>"key1",          /* 要用的API的ID */
        ]
    ]
];
/*
 * 运行环境配置
 * 空白 - 标准虚拟主机
 * scf  - 腾讯云云函数
 */
define("TC_RUNAS","");