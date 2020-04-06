## TCShare 模块开发
### 概述
目前TCShare支持三种模块：`Provider(网盘)`，`Rule(规则)`和`View（主题）`。  
### 使用插件
`provider`和`rule`：将插件文件放置在`_plugins`目录下，执行`composer install`，配置文件的`XS_KEY_<id>=`或`XS_SEC_<priority>=`填写类名，如`someNamespace\DemoPlugin`
### Provider
`provider`包含两个类，需分别实现`xyToki\xyShare\contentProvider`和`xyToki\xyShare\authProvider`接口。
其中，`authProvider`负责处理oAuth认证，如无需oauth（如用户名密码登录、填写cookie等），可不实现这个接口。
同时，还需要实现`fileInfo`和`folderInfo`用于存储文件(夹)的信息。
#### `abstractInfo`
文件和文件夹共有接口。
```php
interface abstractInfo{
    /* 文件名 */
    function name();
    /* 修改日期，date('T-m-d H:i:s') */
    function timeModified();
    /* 创建日期，date('T-m-d H:i:s') */
    function timeCreated();
    /* 是否文件夹，true/false */
    function isFolder();
}
```
#### `fileInfo`
文件信息接口，每个文件是一个对象
```php
interface fileInfo extends abstractInfo{
    /* 下载地址，支持动态获取，参见和彩云 */
    function url();
    /* 文件大小 */
    function size();
    /* 后缀名，可实现为 TC::ext($this->name); */
    function extension();
    /* 缩略图地址 */
    function thumbnail();
    /* 预览地址，可以不实现 */
    //function preview();
}
```
#### `folderInfo`
```php
interface folderInfo extends abstractInfo{
    /* 打包下载地址，可以不实现 */
    //function zipDownload();
}
```
#### `contentProvider`  
编写中  
#### `contentProvider`  
编写中  
### Rule
`rule`是一个类，需实现`xyToki\xyShare\Rules\abstractRule`接口，静态暴露check函数。
```php
interface abstractRule {
    static function check(String $path,Array $config,$file);
}
```
返回值为`Int`，表示跳过下面几条规则，也可以返回常量：
 - `XS_RULE_HALT`，即`0`，当前规则不通过，阻止程序运行。
 **请勿使用`die()`或者`exit()`，这会让程序在scf无法正常工作。**
 - `XS_RULE_PASS`，即`1`，当前规则通过。
 - `XS_RULE_SKIP`，即`INT_MAX`，跳过所有规则。
 - 返回其他数字`n`，当前规则通过并跳过接下来的`n-1`条规则。
### View  
主题应放置于`_app/views`下以主题命名的文件夹中。
#### 文件说明：
##### `list.php`: 必须，渲染文件列表。  
参数：
 - `$path`：当前路径
 - `$current`: 当前文件夹的`folderInfo`对象。
 - `$files`：文件数组，元素为Provider提供的`fileInfo`对象。
 - `$folders`: 文件夹数组，元素为Provider提供的`folderInfo`对象。
 - `$sort`: 当前排序模式，`asc` or `desc`
 - `$order`: 当前排序参数，为`fileInfo`对象的函数名。
###### `password.php`: 可选，渲染密码保护页面。
参数参见`_app/rules/password.php`。
###### `config.json`：必须，配置服务端文件预览
格式为`"扩展名":"php文件名"`，例如以下：
```json
{
    "mp4":"preview_video"
}
```
###### 各格式预览文件。可选，需在上面提到的config.json配置  
参数：
 - `$file`：当前预览文件的`fileInfo`对象。
