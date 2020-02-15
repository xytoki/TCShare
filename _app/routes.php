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
        if(!isset($RUN['provider'])||!class_exists($RUN['provider'])){
            throw new Error("Undefined provider ".$RUN['provider']);
        }
        //初始化sdk
        $RUN['BASE']=$RUN['app']['base'];
        $app=new $RUN['provider']($RUN);
        //格式化path
        $path="/".urldecode(urldecode(str_replace("?".$_SERVER['QUERY_STRING'],"",$route->splat)));
        $path=str_replace("//","/",$path);
        //获取文件信息
        $fileInfo=$app->getFileInfo($path);
        //有md5的，都是文件，跳走
        if(!$fileInfo->isFolder()){
            //预览
            if($_SERVER['REQUEST_METHOD']=="POST"||isset($_GET['TC_preview'])){
                $config=TC::get_preview_ext();
                if(isset($config[$fileInfo->ext()])){
                    Flight::render(
                        $RUN['app']['theme']."/".$config[$fileInfo->ext()],
                        array_merge($RUN,["file"=>$fileInfo])
                    );
                    return;
                }else{
                    var_dump($fileInfo);
                    return;
                }
            }else{
                //下载
                Flight::redirect($fileInfo->url());
                return;
            }
        }
        //列目录
        list($folders,$files)=$app->listFiles($fileInfo);
        //渲染
        if(substr($path,-1)!="/")$path=$path."/";
        Flight::response()->header("X-TCShare-Type","List");
        Flight::render($RUN['app']['theme']."/list",array_merge($RUN,[
            "path"=>$path,
            "folders"=>$folders,
            "files"=>$files
        ]));
    },true);
}