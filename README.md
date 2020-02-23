# TCShare v2
支持多盘的天翼云API目录列表程序

### 注意
v2版本由于支持多盘，配置方式以v1不同，请查看config.php仔细填写。v1不再更新。

### 安装方式：

若你要挂载一个网盘到`/`：

1. 获取一个API，并设置环境变量(如SCF等)或填入`.env`或`.env.local`中
2. 上传到服务器，用composer安装依赖
3. 配置伪静态
4. 访问`/-install`进行账号授权，获取token，填入`config.php`中
5. 记得每个月访问`/-renew`续期一次。续期的时候不需要重新填写token。

若将网盘挂载到`/disk`，则授权地址会变成`/disk/-install`、`/disk/-renew`，回调地址会变成`/disk/-callback`，请注意区分。

##### 关于环境变量、`.env`和`config.php`  
v2.5增加了对`.env`和环境变量的支持，因此通过`config.php`配置已被弃用（但仍然兼容）。  
如果使用v2.5以上的版本，并未使用`config.php`配置，TCShare将自动写入获得的accesstoken，无需手动修改文件。

###### 环境变量配置示例
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
 - 视频播放
 - 多盘
 - 腾讯云函数（SCF）
 - 数据缓存（文件，memcache，redis）

#### TODO
 - 其他文件类型的预览
 - 服务器直接输出
 - header,footer,readme
 - 密码加密
 - 防盗链

### Demo

[这里](http://env-3379049.cloud.cloudraft.cn/)

### Rewrite规则：

Apache：
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```
Nginx:
```
try_files $uri $uri/ /index.php$is_args$args;
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

### 在腾讯云云函数(SCF)运行

1. 下载程序
2. 使用`composer`安装依赖
3. 上传到腾讯云，函数名是`index.main_handler`
4. 设置环境变量，然后安装