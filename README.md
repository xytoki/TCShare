# TCShare
不只是天翼云API的目录列表程序

### 安装方式：

若你要挂载一个网盘到`/`：

1. 获取一个API，并设置环境变量(如SCF等)或填入`.env`或`.env.local`中
2. 上传到服务器，用composer安装依赖
3. 配置伪静态
4. 访问`/-install`进行账号授权，获取token。
5. 记得每个月访问`/-renew`续期一次。续期的时候不需要重新填写token。

若将网盘挂载到`/disk`，则授权地址会变成`/disk/-install`、`/disk/-renew`，回调地址会变成`/disk/-callback`，请注意区分。

##### 关于环境变量、`.env`和`config.php`  
v2.5增加了对`.env`和环境变量的支持，因此通过`config.php`配置已被弃用（但仍然兼容）。  
如果使用v2.5以上的版本，并未使用`config.php`配置，TCShare将自动写入获得的accesstoken，无需手动修改文件。
##### v3版本将废弃config.php支持。

###### 环境变量配置示例
可以放置于.env中。请一定禁止访问env文件！
```shell
#   XS 是前缀
#   | -KEY 是配置种类，可选KEY，APP，SEC
#   | | - -ct是key的ID（类似config.php）
#   | | - | - something是配置名称
#   | | - | - | - - - - value在等号右边
#   XS_KEY_ct_something=value

    XS_KEY_ct=ctyun   #必填，值为ctyun
    XS_KEY_ct_FD=     #应用文件夹名
    XS_KEY_ct_AK=     #AK
    XS_KEY_ct_SK=     #SK

#   这里APP后面的可以是任意值，一般就123456下去
#          ↓
    XS_APP_1=/              #挂载路径
    XS_APP_1_NAME=TCShare   #网盘名称
    XS_APP_1_THEME=mdui     #界面主题
    XS_APP_1_BASE=/         #网盘内路径
    XS_APP_1_KEY=ct         #对应上面Key的ID

```

### 功能

#### 已支持
 - 文件下载
 - 视频播放 (mp4,webm,mkv)
 - Office预览 (doc(x),ppt(x),xls(x),pdf)
 - 多盘
 - 腾讯云函数（SCF）
 - 数据缓存（文件，memcache，redis）
 - 密码加密
 - 防盗链
 - Token鉴权

#### TODO
 - 其他文件类型的预览
 - 服务器直接输出
 - header,footer,readme

### Demo

[这里](http://env-3379049.cloud.cloudraft.cn/)

### Rewrite规则：

Apache：
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```
Nginx:
```
try_files $uri $uri/ /index.php$is_args$args;
location ~ /\.env {
    deny all;
}
```
### 配置缓存  
默认情况下，TCShare将使用文件缓存数据，您可以设置如下配置而是用memcache或Redis：
```bash
  XS_CACHE_MODE=memcached
  XS_CACHE_PATH=memcached://127.0.0.1:11211
# XS_CACHE_PATH=memcached:///tmp/memcached.sock
# XS_CACHE_PATH=memcached://127.0.0.1:11211;memcached://127.0.0.12:11211
# https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html

  XS_CACHE_MODE=redis
  XS_CACHE_PATH=redis://127.0.0.1:6379
# XS_CACHE_PATH=redis:///tmp/redis.sock
# https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html
```

### 安全规则
支持密码保护、Token鉴权、Referrer防盗链三种方式。
该功能仅支持在环境变量中配置，不支持config.php。
配置如下：
```bash
XS_SEC_1=/dir1/* 
# 路径规则，参照http://flightphp.com/learn/#routing
# 例如，/dir1 会匹配/dir1 /dir1/
# /dir1/* 匹配 /dir1 /dir1/ 和dir1之下的所有文件
XS_SEC_1_TYPE=referrer
# 模式
XS_SEC_1_MODE=black
# 如果是referrer，设置黑白名单
XS_SEC_1_VAL=baidu.com,google.com
# 黑白名单的域名，逗号分隔
XS_SEC_1_EMPTY=true
# 允许空referrer

# Token鉴权，需要和密码配合使用
# 若token正确优先级在token之后的所有规则都将被跳过
# token不正确将继续下一条规则
# Token计算方式见下
XS_SEC_2=/dir2/*
XS_SEC_2_TYPE=token
XS_SEC_2_VAL=tcshare_demo_key #secret值

# 密码保护
XS_SEC_3=/dir2/*
XS_SEC_3_TYPE=password
XS_SEC_3_VAL=password123
#      ↑
# 此数字决定优先级，优先级高的规则将先执行。
# 除了Token之外，其他规则返回失败时将终止程序
# Token失败会跳到下一条规则 争取会忽略下面所有
# 所以目前如需使用token必须在下面放置password。
```
#### Token计算方式
可用于附件CDN等高级用途。
```php
function token($path,$secret,$exps){
    $baseTime=1580646002;        
    // Magic Number, 实际上是2020-02-02 20:20:02 CST, 别管我为啥这么写
    $exptime=time()+$exps-$baseTime;
    // 过期时间减去上面的magic number
    $usign=substr(md5($path.$secret.$exptime),0,16);
    // 16位MD5
    return $usign.dechex($exptime);
    // 16进制的过期时间
}
$path   = "/dir1/1.jpg?query_string=should_be_calcuated";
    // querystring也需要计算
$secret = "tcshare_demo_key";
$expire = 300;
echo $path."&_tcshare=",token($path,$secret,$expire);
// returns /dir1/1.jpg?query_string=should_be_calcuated&_tcshare=1d435a917e04e8c824eb21
```
### 在腾讯云云函数(SCF)运行

1. 下载程序
2. 使用`composer`安装依赖
3. 上传到腾讯云，函数名是`index.main_handler`
4. 设置环境变量，然后安装
5. 若绑定域名，请增加一条环境变量：`scf_base=/`，其中`/`是绑定的路径。