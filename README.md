# TCShare
天翼云API目录列表程序

### 安装方式：

1. 获取一个API，并填入`config.php`中
2. 上传到服务器
3. 配置伪静态
4. 账号授权，获取token，填入`config.php`中
5. 记得每个月访问`/-renew`续期一次。续期的时候不需要重新填写token。

### 功能

#### 已支持
 - 文件下载
 - 视频播放

#### TODO
 - 其他文件类型的预览
 - 主题优化
 - 服务器直接输出
 - header,footer,readme
 - 多盘

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
