<?php
/* @file routes.php 主路由
 * @package TCShare
 * @author xyToki
 */
function TC_MainRoute($base=""){
    global $RUN;
    /* 安装 */
    if(!isset($RUN['ACCESS_TOKEN'])||$RUN['ACCESS_TOKEN']==""){
        Flight::route($base."/-install",function(){
            global $RUN;
            $oauthClient=new Sky($RUN['AK'],$RUN['SK']);
            $url=($oauthClient->getAuthorizeURL("http://127.0.0.1/-callback?"));
            ?>
                <h1>TC Install</h1>
                Please set a <code>access_token</code> in <code>index.php</code> or environment variables.<br>
                <a target="_blank" href="<?php echo $url;?>">Click here to get a token</a><br><br>
                After the redirect, replace <code>http://127.0.0.1/</code> with <script>document.write('<code>'+location.href.split("-install")[0]+'</code>');</script> to continue.
            <?php
        });
    }else{
        Flight::route($base."/-renew",function(){
            global $RUN;
            $oauthClient=new Sky($RUN['AK'],$RUN['SK']);
            $url=($oauthClient->getAuthorizeURL("http://127.0.0.1/-callback?"));
            ?>
                <h1>TC Renew</h1>
                <a target="_blank" href="<?php echo $url;?>">Click here to renew your token</a><br><br>
                After the redirect, replace <code>http://127.0.0.1/</code> with <script>document.write('<code>'+location.href.split("-renew")[0]+'</code>');</script> to continue.
            <?php
        });
    }
    /* 授权回调 */
    Flight::route($base."/-callback",function(){
        global $RUN;
        if( isset($RUN['ACCESS_TOKEN']) && $RUN['ACCESS_TOKEN']!="" ){
            $oauthClient=new Sky($RUN['AK'],$RUN['SK']);
            $acctk=$oauthClient->getAccessToken("code",$_GET['code']);
            if($acctk['accessToken']!==$RUN['ACCESS_TOKEN']){
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
        $oauthClient=new Sky($RUN['AK'],$RUN['SK']);
        $acctk=$oauthClient->getAccessToken("code",$_GET['code']);
        ?>
        <h1>TC Install</h1>
        Please set the <code>access_token</code> below in <code>index.php</code> or environment variables.<br>
        <textarea style="width:100%"><?php echo($acctk['accessToken']);?></textarea>
        Please renew your token again before <code><?php echo date("Y-m-d H:i:s",$acctk['expiresIn']/1000);?></code><br/>
        <?php
        echo "<pre>",print_r($acctk,true),"</pre>";
    	
    });
    /* 主程序 */
    Flight::route($base."/*",function($route){
        global $RUN;
    	if($RUN['AK']==""||$RUN['SK']=="")
            throw new Error("请填写API参数");
        if(empty($RUN['ACCESS_TOKEN'])){
            Flight::redirect("/-install",302);
            exit;
        }
        //初始化sdk
        $sky=new SkyHandle($RUN['AK'],$RUN['SK'],$RUN['ACCESS_TOKEN']);
        //格式化path
        $path="/".urldecode(urldecode(str_replace("?".$_SERVER['QUERY_STRING'],"",$route->splat)));
        $path=str_replace("//","/",$path);
        $finpath="/我的应用/".$RUN['FD'].$RUN['app']['base'].$path;
        //获取文件信息
        $fileInfo=$sky->getFileInfo($finpath);
        if($RUN['FD']==""||isset($fileInfo['code'])&&$fileInfo['code']=="PermissionDenied"){
          throw new Error("应用无访问<code>"."/我的应用/".$RUN['FD'].$RUN['app']['base']."</code>文件夹的权限，请检查应用目录是否正确填写");
        }
        if(isset($fileInfo['code'])&&$fileInfo['code']=="FileNotFound"){
          	if($finpath=="/我的应用/".$RUN['FD'].$RUN['app']['base']||$finpath=="/我的应用/".$RUN['FD'].$RUN['app']['base']."/"){
            	throw new Error("请手动建立<code>"."/我的应用/".$RUN['FD'].$RUN['app']['base']."</code>文件夹");
            }
            return Flight::notFound();
        }
        if(isset($fileInfo['code'])&&isset($fileInfo['message'])){
            throw new Error("API错误: ".$fileInfo['message']);
        }
        //有md5的，都是文件，跳走
        if(!is_array($fileInfo['md5'])){
            //预览
            if($_SERVER['REQUEST_METHOD']=="POST"||isset($_GET['TC_preview'])){
                $config=TC::get_preview_ext();
                if(isset($config[TC::ext($fileInfo['name'])])){
                    Flight::render(
                        $RUN['app']['theme']."/".$config[TC::ext($fileInfo['name'])],
                        array_merge($RUN,(array)$fileInfo)
                    );
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
        
        if(substr($path,-1)!="/")$path=$path."/";
        Flight::response()->header("X-TCShare-Type","List");
        Flight::render($RUN['app']['theme']."/list",array_merge($RUN,[
            "path"=>$path,
            "folders"=>TC::toArr($list['folder']),
            "files"=>TC::toArr($list['file'])
        ]));
    },true);
}