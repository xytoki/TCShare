# TCShare v2
支持多盘的天翼云API目录列表程序

### 注意
v2版本由于支持多盘，配置方式以v1不同，请查看config.php仔细填写。v1不再更新。

### 安装方式：

若你要挂载一个网盘到`/`：

1. 获取一个API，并填入`config.php`中
2. 上传到服务器，用composer安装依赖
3. 配置伪静态
4. 访问`/-install`进行账号授权，获取token，填入`config.php`中
5. 记得每个月访问`/-renew`续期一次。续期的时候不需要重新填写token。

若将网盘挂载到`/disk`，则授权地址会变成`/disk/-install`、`/disk/-renew`，回调地址会变成`/disk/-callback`，请注意区分。

### 功能

#### 已支持
 - 文件下载
 - 视频播放
 - 多盘
 - 腾讯云函数（SCF）

#### TODO
 - 其他文件类型的预览
 - 服务器直接输出
 - header,footer,readme

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
### 在腾讯云云函数(SCF)运行

1. 下载程序
2. 使用`composer`安装依赖，照常填写`config.php`
3. 上传到腾讯云，函数名是`index.main_handler`