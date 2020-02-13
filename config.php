<?php
/* 欢迎使用TCShare v2！这是一个天翼云的目录列表程序。
   在下面填写你的网盘信息，然后授权使用吧！
   v1和v2版本主题、配置文件都不兼容，麻烦手动修改一下！
   v2版本支持多盘哦，可以分别配置多个api(key)和挂载路径(app)了
   配置解析：
      Key
         ID: 用于在App中对应
         FD: 应用文件夹名
         AK: AppKey
         SK: SecretKey
         ACCESS_TOKEN: 安装前留空，安装后填写，此后无需修改
      App
         route:   挂载路径
         name:    网盘名称，显示在title中
         theme:   主题，apache/mdui可选
         base:    网盘内起始目录
         key:     此挂载使用的key ID
   每月需要访问 /挂载路径/-renew 延长token有效期，但无需再修改此文件。
   
   @xyToki https://xylog.cn 2020-02-11
   https://github.com/xyToki/TCShare */
global $TC;
/* 多盘配置 */
$TC=[
    "Keys"=>[],
    "Apps"=>[]
];
