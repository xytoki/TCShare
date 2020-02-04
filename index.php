<?php
use Firebase\JWT\JWT;
define("_LOCAL",dirname(__FILE__));
ini_set("display_errors",1);
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
require 'vendor/autoload.php';
require 'sky.class.php';
require 'config.php';
Flight::set('flight.views.path', dirname(__FILE__).'/views');
header("X-TCShare-Version: 1.0.3");
//安装
if(!defined("ACCESS_TOKEN")||ACCESS_TOKEN==""){
    Flight::route("/-install",function(){
        $oauthClient=new Sky(AK,SK);
        $url=($oauthClient->getAuthorizeURL("http://127.0.0.1/-callback?"));
        ?>
            <h1>TC Install</h1>
            Please set a <code>access_token</code> in <code>index.php</code> or environment variables.<br>
            <a target="_blank" href="<?php echo $url;?>">Click here to get a token</a><br><br>
            After the redirect, replace <code>http://127.0.0.1/</code> with <script>document.write('<code>'+location.href.split("-install")[0]+'</code>');</script> to continue.
        <?php
    });
}else{
    Flight::route("/-renew",function(){
        $oauthClient=new Sky(AK,SK);
        $url=($oauthClient->getAuthorizeURL("http://127.0.0.1/-callback?"));
        ?>
            <h1>TC Renew</h1>
            <a target="_blank" href="<?php echo $url;?>">Click here to renew your token</a><br><br>
            After the redirect, replace <code>http://127.0.0.1/</code> with <script>document.write('<code>'+location.href.split("-renew")[0]+'</code>');</script> to continue.
        <?php
    });
}
//接受回调
Flight::route("/-callback",function(){
    if( defined("ACCESS_TOKEN") && ACCESS_TOKEN!="" ){
        $oauthClient=new Sky(AK,SK);
        $acctk=$oauthClient->getAccessToken("code",$_GET['code']);
        if($acctk['accessToken']!==ACCESS_TOKEN){
            ?>
            <h1>TC Renew</h1>
            Renew faild:AccessToken not match.<br/>
            <?php
    echo nl2br(print_r($acctk,true));
        }else{
            ?>
            <h1>TC Renew</h1>
            Renew proceeded successfully.<br/>
            Please renew your token again before <code><?php echo date("Y-m-d H:i:s",$acctk['expiresIn']/1000);?></code><br/>
            <?php
            echo "<pre>",print_r($acctk,true),"</pre>";
        }
        return;
    }
    $oauthClient=new Sky(AK,SK);
    $acctk=$oauthClient->getAccessToken("code",$_GET['code']);
    ?>
    <h1>TC Install</h1>
    Please set the <code>access_token</code> below in <code>index.php</code> or environment variables.<br>
    <textarea style="width:100%"><?php echo($acctk['accessToken']);?></textarea>
    <?php
    echo "<pre>",print_r($acctk,true),"</pre>";
});

//工具函数一个
function toArr($a){
    if(!is_array($a)||isset($a['id']))$a=[$a];
    return $a;
}
//主程序
Flight::route("/*",function($route){
	if(AK==""||SK=="")
        throw new Error("请填写API参数");
    if(empty(ACCESS_TOKEN)){
        Flight::redirect("/-install",302);
        exit;
    }
    //初始化sdk
    $sky=new SkyHandle(AK,SK,ACCESS_TOKEN);
    //格式化path
    $path="/".urldecode(urldecode(str_replace("?".$_SERVER['QUERY_STRING'],"",$route->splat)));
    $path=str_replace("//","/",$path);
    $finpath="/我的应用/".FD.APP_PATH.$path;
    //获取文件信息
    $fileInfo=$sky->getFileInfo($finpath);
    if(isset($fileInfo['code'])&&$fileInfo['code']=="PermissionDenied"){
      throw new Error("应用无访问<code>"."/我的应用/".FD.APP_PATH."</code>文件夹的权限，请检查应用目录是否正确填写");
    }
    if(isset($fileInfo['code'])&&$fileInfo['code']=="FileNotFound"){
      	if($finpath=="/我的应用/".FD.APP_PATH||$finpath=="/我的应用/".FD.APP_PATH."/"){
        	throw new Error("请手动建立<code>"."/我的应用/".FD.APP_PATH."</code>文件夹");
        }
        return Flight::notFound();
    }
    //有md5的，都是文件，跳走
    if(!is_array($fileInfo['md5'])){
        //预览
        if($_SERVER['REQUEST_METHOD']=="POST"||isset($_GET['TC_preview'])){
            $config=TC::get_preview_ext();
            if(isset($config[TC::ext($fileInfo['name'])])){
                Flight::render(APP_THEME."/".$config[TC::ext($fileInfo['name'])],$fileInfo);
                return;
            }else{
                var_dump($fileInfo);
                return;
            }
        }else{
            //下载
            Flight::redirect($fileInfo['fileDownloadUrl']);
            return;
        }
    }
    //列目录
    $list=$sky->listFiles($fileInfo['id'])['fileList'];
    //渲染
    
    $base=Flight::request()->base;
    if($base=="/")$base="";
    define("APP_BASE_PATH",$base);
    if(substr($path,-1)!="/")$path=$path."/";
    Flight::render(APP_THEME."/list",[
        "path"=>$path,
        "folders"=>toArr($list['folder']),
        "files"=>toArr($list['file'])
    ]);
},true);
Class TC{
    static function get_preview_ext(){
        try{
            return include("views/".APP_THEME."/config.php");
        }catch(Throwable $e){
            return ["unsupported"=>""];
        }
    }
    static function abspath($path,$path2="/"){
        $path.=$path2;
        $path = str_replace(array('/', '\\', '//'), '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return APP_BASE_PATH.str_replace('//','/','/'.implode('/', $absolutes));
    }
    static function human_filesize($size, $precision = 1) {
		for($i = 0; ($size / 1024) > 1; $i++, $size /= 1024) {}
		return round($size, $precision).(['B','KB','MB','GB','TB','PB','EB','ZB','YB'][$i]);
	}
	static function ext($file){
	    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
	}
	static function readyPreview(){
	    ?>
            <script src="https://lib.baomitu.com/jquery/3.4.1/jquery.slim.min.js"></script>
            <script>window.TC=window.TC||{};TC.preview_exts=<?php echo json_encode(array_keys(TC::get_preview_ext()));?></script>
            <script src="<?php echo self::abspath("/views/readypreview.js");?>"></script>
        <?php
	}
	static function layout($vars=[],$callback=false){
	    Flight::render(APP_THEME."/layout",array_merge($vars,[
	        "callback"=>$callback   
	    ]));
	}

}
Flight::start();
