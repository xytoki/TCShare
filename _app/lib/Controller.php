<?php
/* @file routes.php 主路由
 * @package TCShare
 * @author xyToki
 */
namespace xyToki\xyShare;
use TC;
use Flight;
use flight\net\Route;
use xyToki\xyShare\Errors\NotFound;
use xyToki\xyShare\Errors\NotAuthorized;
class Controller{
    static function cachedUrl(){
        Flight::route("/_app/cached/@key",function($key){
            $res = Cache::getInstance()->getItem("tcshare_cached_.".$key);
            if ($res->isHit()) {
                return Flight::redirect($res->get(),302);
            }
        });
    }
    static function prepare($app,$base=""){
        global $TC;
        Flight::route($base."/*",function() use($app,$TC){
            Flight::response()->header("X-Powered-by","TCShare@xyToki");
            Flight::response()->header("X-TCshare-version",TC_VERSION);
            global $RUN;
            $RUN['app']=$app;
            $hasKey=false;
            foreach($TC['Keys'] as $k){
                if($k['ID']==$app['key']){
                    $RUN=array_merge($RUN,$k);
                    $hasKey=true;
                    break;
                }
            }
            if(!$hasKey){
                throw new Error("请正确配置Key");
            }
            $urlbase=Flight::request()->base;
            if($urlbase=="/")$urlbase="";
            $RUN=array_merge($RUN,$k);
            $RUN['URLBASE']=$urlbase;
            if(!isset($RUN['provider'])||empty($RUN['provider'])){
                $RUN['provider']="xyToki\\xyShare\\Providers\\ctyun";
            }else if(!empty($RUN['provider'])&&!strstr($RUN['provider'],"\\")){
                $RUN['provider']="xyToki\\xyShare\\Providers\\".$RUN['provider'];
            }
            return true;
        });
    }
    static function rules($rules){
        Flight::route("/*",function($route) use($rules){
            $rs=[];
            foreach($rules as $rule){
                $rs[]=$rule;
            }
            for($i=0;$i<count($rs);){
                $ret=self::rule($rs[$i],"/".$route->splat);
                if($ret==XS_RULE_HALT){
                    return;
                }
                if($ret==XS_RULE_SKIP){
                    break;
                }
                $i+=$ret;
            }
            return true;
        },true);
    }
    static function rule($rule,$path){
        $pattern = $rule['route'];
        $encoded = TC::encodeURI($rule['route']);
        if(self::urlMatch($pattern)||self::urlMatch($encoded)){
            $type=$rule['type'];
            if(!$type)return XS_RULE_PASS;
            if(!empty($type)&&!strstr($type,"\\")){
                $type="xyToki\\xyShare\\Rules\\".$type;
            }
            return $type::check($path,$rule);
        }
        return XS_RULE_PASS;
    }
    static function urlMatch($pattern){
        $url = $pattern;
        $methods = array('*');
        if (strpos($pattern, ' ') !== false) {
            list($method, $url) = explode(' ', trim($pattern), 2);
            $methods = explode('|', $method);
        }
        $route = new Route($url, false, $methods, false);
        $request = Flight::request();
        if ($route !== false && $route->matchMethod($request->method) && $route->matchUrl($request->url, false)) {
            return true;
        }
        return false;
    }
    static function installer($base=""){
        Flight::route($base."/-authurl",function(){
            global $RUN;
            if(!isset($RUN['provider'])||!class_exists($RUN['provider'])){
                throw new Error("Undefined provider >".$RUN['provider']."<");
            }
            $authProvider=isset($RUN['authProvider'])?$RUN['authProvider']:($RUN['provider']."Auth");
            $oauthClient=new Provider($authProvider,$RUN);
            Flight::json(["url"=>$oauthClient->url($_GET['callback'])]);
        });
        $cb=function(){
            Flight::render("install/ready");
        };
        Flight::route($base."/-install",$cb);
        Flight::route($base."/-renew",$cb);
        /* 授权回调 */
        Flight::route($base."/-callback",function() use($base){
            global $RUN;
            if(!isset($RUN['provider'])||!class_exists($RUN['provider'])){
                throw new Error("Undefined provider >".$RUN['provider']."<");
            }
            $authProvider=isset($RUN['authProvider'])?$RUN['authProvider']:($RUN['provider']."Auth");
            $oauthClient=new Provider($authProvider,$RUN);
            $oauthClient->getToken();
            $res=Config::write("XS_KEY_".$RUN['ID']."_ACCESS_TOKEN",$oauthClient->token());
            if( isset($RUN['ACCESS_TOKEN']) && $RUN['ACCESS_TOKEN']!="" ){
                    ?>
                    <h1>xyShare Renew</h1>
                    Renew proceeded successfully.<br/>
                    <?php
                    if($oauthClient->needRenew()){ ?>
                        Please renew your token MAUNALLY again before <code><?php echo $oauthClient->expires();?></code><br/>
                    <?php
                    }
                return;
            }
            
            if($res){
                Flight::redirect($base."/");
                return;
            }
            ?>
            <h1>xyShare Install</h1>
            <?php if(defined('XY_USE_CONFPHP')){ ?>
                Please set <code>ACCESS_TOKEN</code> below in <code>config.php</code>
            <?php }else{ ?>
                Please set  <code><?php echo "XS_KEY_".$RUN['ID']."_ACCESS_TOKEN" ?></code> below in environment variables.<br>
            <?php } ?>
            <textarea style="width:100%"><?php echo($oauthClient->token());?></textarea>
            Please renew your token again before <code><?php echo $oauthClient->expires();?></code><br/>
            <?php
            
        });
    }
    static function disk($base=""){
        /* 主程序 */
        Flight::route($base."/*",function($route) use($base){
            global $RUN;
            //初始化sdk
            $RUN['BASE']=$RUN['app']['base'];
            try{
                $app=new Provider($RUN['provider'],$RUN);;
            }catch(NotAuthorized $e){
                return Flight::redirect($base."/-install");
            }
            //格式化path
            $path="/".urldecode(urldecode(str_replace("?".$_SERVER['QUERY_STRING'],"",$route->splat)));
            $path=str_replace("//","/",$path);
            //获取文件信息
            try{
                $fileInfo=$app->getFileInfo($path);
            }catch(NotFound $e){
                return true;
                //Go to next disk until really 404.
            }
            //有md5的，都是文件，跳走
            if(!$fileInfo->isFolder()){
                //预览
                if($_SERVER['REQUEST_METHOD']=="POST"||isset($_GET['TC_preview'])){
                    $config=TC::get_preview_ext();
                    if(isset($config[$fileInfo->extension()])){
                        Flight::render(
                            $RUN['app']['theme']."/".$config[$fileInfo->extension()],
                            array_merge($RUN,["file"=>$fileInfo,"base"=>$base,"path"->$path])
                        );
                        return;
                    }else{
                        var_dump($fileInfo);
                        return;
                    }
                }else{
                    //下载
                    Flight::redirect($fileInfo->url(),302);
                    return;
                }
            }
            //列目录
            list($folders,$files)=$app->listFiles($fileInfo);
            //排序
                $s = isset($_GET['sort'])?$_GET['sort']:"name";
                $sortableF=["name","timeModified","timeCreated","size","ext"];
                $sortableD=["name","timeModified","timeCreated"];
                if(in_array($s,$sortableF)){
                    usort($files, function($a, $b) use($s) {
                        $x = $a->$s();
                        $y = $b->$s();
                        return is_numeric($x)?$x-$y:strcmp($x,$y);
                    });
                }
                if(in_array($s,$sortableD)){
                    usort($folders, function($a, $b) use($s) {
                        $x = $a->$s();
                        $y = $b->$s();
                        return is_numeric($x)?$x-$y:strcmp($x,$y);
                    });
                }
                $o="asc";
                if(isset($_GET['order'])&&$_GET['order']=="desc"){
                    $folders = array_reverse($folders);
                    $files = array_reverse($files);
                    $o="desc";
                }
            //渲染
            if(substr($path,-1)!="/")$path=$path."/";
            Flight::response()->header("X-TCShare-Type","List");
            Flight::render($RUN['app']['theme']."/list",array_merge($RUN,[
                "path"=>$path,
                "folders"=>$folders,
                "files"=>$files,
                "sort"=>$s,
                "order"=>$o
            ]));
        },true);
    }
}