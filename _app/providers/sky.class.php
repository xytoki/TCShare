<?php
/**
 * Created by PhpStorm.
 * User: 337962552@qq.com
 * Date: 2016/1/17
 * Time: 16:27
 */

/**
 * @ignore
 */
class OAuthException extends Exception {
    // pass
}


/**
 * 天翼云 OAuth 认证类(OAuth2)
 *
 * 授权机制说明请大家参考天翼云开放平台文档：{@link http://cloud.189.cn/developer.action 或http://cloud.189.cn/t/baYNNjfANNfq}
 *
 * @author Zheng Qiang
 * @version 1.0
 */
class Sky {
    function __construct($akey,$skey)
    {
        $this->ak = $akey;
        $this->sk = $skey;
    }
    /**
     * Set API URLS
     *
     * 用户授权认证URL
     */
    function authorizeURL()    { return 'https://cloud.189.cn/open/oauth2/authorize.action'; }

    /**
     *
     * 获取 OAuth2 access token 的URL
     *
     */
    function accessTokenURL()  { return 'https://cloud.189.cn/open/oauth2/accessToken.action'; }

    /**
     * authorize接口
     *
     * 对应API：{@link http://cloud.189.cn/open/oauth2/authorize.action}
     *
     * @param  string  $appKey         用户申请应用获取的值
     * @param  string  $appSignature   应用签名，使用hash_hmac方法生成带有密钥的哈希值
     * @param  string  $response_type  支持的值包括 code 和token 默认值为code
     * @param  long    $timestamp      时间戳 unix timestamp
     * @param  string  $url            授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写Authorized callback page的地址
     * @param  string  $state          用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
     * @param  string  $display        授权页面类型 可选范围:
     *          - default				默认授权页面
     *          - mobile				支持html5的手机
     *
     * @return array
     */
    function getAuthorizeURL( $url, $response_type = 'code', $state = NULL, $display = NULL ) {
        $params = array();
        $params['appKey'] = $this->ak;
        $params['appSignature'] =hash_hmac("sha1","appKey=".$this->ak."&timestamp=".time()."",$this->sk);
        $params['responseType'] = $response_type;
        $params['timestamp'] = time();
        $params['callbackUrl'] = $url;
        $params['state'] = $state;
        $params['display'] = $display;
        return $this->authorizeURL() . "?" . http_build_query($params);
    }


    /**
     * access_token接口
     *
     * 对应API：{@link http://cloud.189.cn/open/oauth2/accessToken.action}
     *
     * @param  string  $appKey         用户申请应用获取的值
     * @param  string  $appSignature   应用签名，使用hash_hmac方法生成带有密钥的哈希值
     * @param  string  $type           请求的类型,可以为:authorization_code,e189_accessToken,device_Token
     * @param  string  $code           调用authorize 获得的 code 值
     * @param  array   $timestamp      参数描述：时间戳，unix timestamp
     *
     * @return array
     */
    function getAccessToken( $type = 'code', $keys ) {
        $params = array();
        $params['appKey'] = $this->ak;
        $params['appSignature'] =hash_hmac("sha1","appKey=".$this->ak."&timestamp=".time()."",$this->sk);
        if ( $type === 'code' ) {
            $params['grantType'] = 'authorization_code';
            $params['code'] = $keys;
            $params['timestamp'] = time();
        } else {
            throw new OAuthException("wrong auth type");
        }
        $url=$this->accessTokenURL(). "?" . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $error=curl_error($ch);
        curl_close($ch);
        
        if(!$response){
            throw new OAuthException("Curl Error >".$error);
        }
        
        $token = json_decode($response, true);
        if ( is_array($token) && !isset($token['error']) ) {
            $this->access_token = $token['accessToken'];
        } else {
            throw new OAuthException("get access token failed." . $response);
        }
        return $token;
    }

}


/**
 * 天翼云云盘操作类SkyHandle
 *
 *
 * @author Zheng Qiang
 * @version 1.0
 */


class SkyHandle
{
    /**
     * 构造函数
     *
     * @access public
     * @param mixed $akey 天翼云开放平台应用APP KEY
     * @param mixed $skey 天翼云开放平台应用APP SECRET
     * @param mixed $access_token OAuth认证返回的token
     *
     * @return void
     */
    function __construct($akey,$skey,$access_token)
    {
        $this->oauth = new Sky($akey,$skey);
        $this->token=$access_token;
        $this->sk=$skey;
    }

    /**
     * 获取用户基本信息
     *
     * 对应API：{@link http://api.cloud.189.cn/getUserInfo.action }
     *
     * HTTP请求方式: GET
     *
     * @return xml
     */
    function getUserInfo()
    {
        $method='GET';
        return $this->get('/getUserInfo.action',$method,$this->token);
//        print_r($arr);die;
    }


    /**
     * 获取指定的文件夹信息
     *
     * 对应API：{@link http://api.cloud.189.cn/getFolderInfo.action  }
     * HTTP请求方式: GET
     *
     * @return xml
     */
    function getFolderInfo($path)
    {
        $method='GET';
        $data=array("folderPath"=>$path);
        return $this->get('/getFolderInfo.action',$method,$this->token,$data);
    }

    /**
     * 获取指定的文件夹下的子文件和文件夹列表
     *
     * 对应API：{@link http://api.cloud.189.cn/listFiles.action }
     *
     * HTTP请求方式: GET
     * 请求参数还有很多，详情请参见天翼云能力开放接口规范 v1.1.19			12页
     * @param：integer $pic    获取文件缩略图选项:
     *          -  0		   或空-不获取缩略图
     *          -  1		   获取小尺寸缩略图(80max)
     *          -  2		   获取中尺寸缩略图(160max)
     *          -  4		   获取大尺寸缩略图(320max)
     *          -  8		   600max 缩略图
     *          -   		   各种尺寸缩略图选项可采用叠加方式同时存在
     *
     * @return xml
     */
    function listFiles($id,$pic=1)
    {
        $access_token=$this->token;
        $method='GET';
        $parameters=array();
        $parameters['folderId']=$id;
        $parameters['iconOption']=$pic;
        return $this->get('/listFiles.action',$method,$access_token,$parameters);
    }
    /**
     * 获取指定的文件信息
     *
     * 对应API：{@link http://api.cloud.189.cn/getFileInfo.action  }
     * HTTP请求方式: GET
     *
     * @return xml
     */
    function getFileInfo($path)
    {
        $method='GET';
        $data=array("filePath"=>$path,"mediaAttr"=>1,"iconOption"=>1);
        return $this->get('/getFileInfo.action',$method,"",$data);
    }
    /**
     * 获取用户扩展信息
     *
     * 对应API：{@link http://api.cloud.189.cn/getUserInfoExt.action  }
     *
     * HTTP请求方式: GET
     *
     * @return xml
     */
    function getUserInfoExt()
    {
        $access_token=$this->token;
        $method='GET';
        return $this->get('/getUserInfoExt.action',$method,$access_token);
    }

    /**
     * 获取文件的下载地址
     *
     * 对应API：{@link http://api.cloud.189.cn/getFileDownloadUrl.action  }
     *
     * HTTP请求方式: GET
     * @param：long     $id     文件 Id
     * @param：boolean  $short  是否获取短地址，true：获取短地址格式下载URL；为空或 false：获取原始下载URL
     *
     * @return string
     */

    function getFileDownloadUrl($id,$short="false"){
        $access_token=$this->token;
        $method='GET';
        $parameters['fileId']=$id;
        $parameters['short']=$short;
        return $this->get('/getFileDownloadUrl.action',"GET",$access_token,$parameters);
    }


    /**
     * 删除文件
     *
     * 对应API：{@link http://api.cloud.189.cn/deleteFile.action  }
     *
     * HTTP请求方式: POST
     * @param：long   $id             要删除的文件 Id
     * @param：short  $forcedDelete   0 或空 - 普通删除，放入回收站 ；1 - 强制删除，不放入回收站
     *
     */

    function deleteFile($access_token,$id)
    {
        $access_token=$this->token;
        $method='POST';
        $parameters['id']=$id;
        $parameters['forcedDelete']=0;
        return $this->post('/deleteFile.action',$method,$access_token,$parameters);
    }

    /**
     * 上传并创建文件（一次性 PUT 上传）
     *
     * 对应API：{@link http://upload.cloud.189.cn/uploadFile.action  }
     *
     * HTTP请求方式: PUT
     * @param：long     $folder_id   文件夹Id，为空时上传文件父目录为应用默认文件夹
     * @param：string   $filename    文件名称
     * @param：string   $data        文件二进制数据
     * @param：long     $file_size   文件大小
     * @param：string   $md5         文件的 MD5 码
     *
     * @return xml
     */

    function uploadFile($folder_id,$file=array())
    {
        $method='GET';
        $xml=$this->get('/getFolderInfo.action',$method,$this->token);
        $parameters['folder_id']=$xml->id;
        $method='PUT';
        $parameters['filename']=urlencode($file['name']);
        $parameters['data']=file_get_contents($file['tmp_name']);
        $parameters['file_size']=$file['size'];
        $parameters['md5']=md5_file($file['tmp_name']);
        return $this->put('/uploadFile.action',$method,$this->token,$parameters);
    }

    /**
     * 上传文件（断点续传）
     *
     * 对应API：创建上传文件{@link http://api.cloud.189.cn/createUploadFile.action  }
     * 对应API：获取上传文件状态{@link http://api.cloud.189.cn/getUploadFileStatus.action  }
     *
     * @param：long       $parentFolderId          父文件夹 ID，为空时，上传文件父目录为应用默认文件夹
     * @param：string     $filename                创建的上传文件名称
     * @param：long       $size                    上传文件大小（字节)
     * @param：string     $localPath               源文件的全路径
     * @param：string     $lastWrite               源文件的最后修改时间
     * @param：string     $localPath               源文件的全路径
     * @param：string     $commit_url              获取上传文件状态的URL
     * @param：string     $upload_url              上传文件的URL
     * @param：string     $data                    文件二进制数据
     * @param：long       $FileId/$uploadFileId    上传文件Id
     *
     * 注：断点续传功能调用三个接口，详细流程参见天翼云开发指南 v1.0 ，24页
     *
     * @return xml
     */

    function createUploadFile($file=array())
    {
        $access_token=$this->token;
        $method = 'GET';
        $xml = $this->get('/getFolderInfo.action', $method, $access_token);
        $parameters['parentFolderId'] = $xml->id;

        $method = 'POST';
        $parameters['filename'] = $file['name'];
        $parameters['size'] = $file['size'];
        $parameters['localPath'] = $file['tmp_name'];
        $parameters['lastWrite'] = date ("Y-M-D h:i:s", filemtime($file['tmp_name']));
        $parameters['md5'] = md5_file($file['tmp_name']);

        $xml = $this->post('/createUploadFile.action', $method, $access_token, $parameters);

        if ($xml->fileDataExists == 0) {
            $method='PUT';
            $commit = $xml->fileCommitUrl;
            $url = $xml->fileUploadUrl;
            $commit_url = substr($commit, stripos($commit, 'cn') + 2);
            $upload_url = substr($url, stripos($url, 'cn') + 2);
            $parameter['commit_url'] = $commit_url;
            $parameter['upload_url'] = $upload_url;

            $parameter['data'] = file_get_contents($file['tmp_name']);;
            $parameter['file_status'] =0;
            $parameter['FileId'] = $xml->uploadFileId;
            @$this->put($url,$method,$access_token,$parameter);

            $method='POST';
            $parameter1['uploadFileId'] = $xml->uploadFileId;
            $parameter1['commit_url'] = $commit_url;

             return $this->post($commit,$method,$access_token,$parameter1);
        }else if($xml->fileDataExists == 1){
            $method='POST';
            $commit = $xml->fileCommitUrl;
            $commit_url = substr($commit, stripos($commit, 'cn') + 2);
            $parameter1['uploadFileId'] = $xml->uploadFileId;
            $parameter1['commit_url'] = $commit_url;

            return $this->post($commit,$method,$access_token,$parameter1);
        }
    }

    /**
     * 搜索文件
     *
     * 对应API：{@link http://api.cloud.189.cn/searchFiles.action }
     *
     * HTTP请求方式: POST
     * @param：string  $filename      搜索文件名
     * @param：Integer $recursive     递归标志 0 或空：只搜索单层文件 1：递归搜索文件列表及子目录文件
     * @param：integer $pic           获取文件缩略图选项:
     *          -  0				   或空-不获取缩略图
     *          -  1				   获取小尺寸缩略图(80max)
     *          -  2				   获取中尺寸缩略图(160max)
     *          -  4				   获取大尺寸缩略图(320max)
     *          -  8				   600max 缩略图
     *          -   				   各种尺寸缩略图选项可采用叠加方式同时存在
     *
     * @return xml
     *
     */
    function searchFiles($access_token,$sel_val){

        $access_token=$this->token;
        $method='POST';
        $parameters['filename']=$sel_val;
        $parameters['recursive']=1;
        $parameters['pic']="iconOption";
        return $this->post('/searchFiles.action',$method,$access_token,$parameters);

    }


    /**
     * GET wrappwer for oAuthRequest
     *
     * @param：string    $time          请求日期和时间
     * @param：string    $sign          请求签名，密钥为 App Secret，使用 hash_hmac 算法
     * @param：string    $access_token  使用 OAuth2授权认证获得的AccessToken
     * @param：long      $id            文件 Id
     * @param  string    $url           请求URL的地址
     * @param：boolean   $short         是否获取短地址，true：获取短地址格式下载URL；为空或 false：获取原始下载URL
     * @param：integer   $iconOption    获取文件缩略图选项:
     *          -  0					 或空-不获取缩略图
     *          -  1					 获取小尺寸缩略图(80max)
     *          -  2					 获取中尺寸缩略图(160max)
     *          -  4					 获取大尺寸缩略图(320max)
     *          -  8					 600max 缩略图
     *          -   					 各种尺寸缩略图选项可采用叠加方式同时存在
     *
     * @return mixed
     *
     */
    function get($url,$method,$access_token,$parameters = array()) {
        $access_token=$this->token;
        $time=gmdate("D, d M Y H:i:s")." GMT";
        $wurl = "https://api.cloud.189.cn" . $url . "?" .http_build_query($parameters);
        $stxt=urldecode(http_build_query(array(
            "AccessToken"=>$this->token,
            "Operate"=>"GET",
            "RequestURI"=>$url,
            "Date"=>$time
        )));
        $sign=hash_hmac('sha1',$stxt,$this->sk);

        $header=array(
            "Date:$time",
            "AccessToken:$access_token",
            "Signature:$sign",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $wurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        $output = curl_exec($ch);
        $arr=simplexml_load_string($output);
        $arr1=json_decode(json_encode($arr),true);
        if(!is_object($arr)){
            curl_close($ch);
            return $output;
        } else {
            curl_close($ch);
            return $arr1;
        }

    }


    /**
     * POST wrappwer for oAuthRequest
     *
     * @param：string    $time               请求日期和时间
     * @param：string    $sign               请求签名，密钥为 App Secret，使用 hash_hmac算法
     * @param：string    $access_token       使用 OAuth2授权认证获得的AccessToken
     * @param：long      $id/$uploadFileId   文件 Id/上传文件Id
     * @param  string    $url                请求URL的地址
     * @param：short     $forcedDelete        0 或空 - 普通删除，放入回收站 ；1 - 强制删除，不放入回收站
     *
     * @return mixed
     * 
     */

    function post($url,$method,$access_token,$parameters = array()){

        $time=gmdate(DATE_RFC1123);
        $sign=hash_hmac('sha1',"AccessToken=$access_token&Operate=$method&RequestURI=$url&Date=$time",$this->sk);

        if(isset($parameters['id'])&&!empty($parameters['id'])) {

            $url = "http://api.cloud.189.cn/" . $url . "?"."fileId=".$parameters['id']."&forcedDelete=$parameters[forcedDelete]";

        }else if(isset($parameters['uploadFileId'])&&!empty($parameters['uploadFileId'])){

            $sign=hash_hmac('sha1',"AccessToken=$access_token&Operate=$method&RequestURI=$parameters[commit_url]&Date=$time",$this->sk);

        }else if(isset($parameters['filename'])&&!empty($parameters['filename'])){

            $url = "http://api.cloud.189.cn/" . $url . "?"."filename=$parameters[filename]&$parameters[pic]=1&&recursive=$parameters[recursive]";

        } else{

            $url="http://api.cloud.189.cn/" . $url . "?";

        }

        $header = array(
            "Date:$time",
            "AccessToken:$access_token",
            "Signature:$sign",
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        $arr=simplexml_load_string($output);
        if(isset($arr->code)&&isset($arr->message)){
            $info = curl_getinfo($ch);
            echo "<span style='color: red'>请求时间</span>：" . $info['total_time'] ."秒";
            echo "<br/><span style='color: red'>错误信息：</span>".$output;
            curl_close($ch);die;
        } else {
            curl_close($ch);
            return $arr;
        }
    }

    /**
     * PUT wrappwer for oAuthRequest.
     *
     * @param：string    $time               请求日期和时间
     * @param：string    $data               文件二进制数据
     * @param：long      $file_status        作者自定义断点续传状态
     * @param：string    $file_sign          请求签名，密钥为 App Secret，使用 hash_hmac算法
     * @param：string    $access_token       使用 OAuth2授权认证获得的AccessToken
     * @param：string    $FileId             断点续传文件 Id
     * @param  string    $url                请求URL的地址
     * @param：long      $folder_id          父文件夹 ID，为空时，上传文件父目录为应用默认文件夹
     * @param：string    $filename           创建的上传文件名称
     * @param：string    $md5                文件的 MD5 码
     * @param：long      $file_size          上传文件大小（字节)
     *
     * @return mixed
     *
     */
    function put($url,$method,$access_token,$parameters = array()){

        $time=gmdate(DATE_RFC1123);
        $data=$parameters['data'];

        if(isset($parameters['file_status'])&&$parameters['file_status']==0) {

            $file_sign = hash_hmac('sha1', "AccessToken=$access_token&Operate=$method&RequestURI=$parameters[upload_url]&Date=$time",$this->sk);

            $header = array(
                "AccessToken:$access_token",
                "Signature:$file_sign",
                "Date:$time",
                "Edrive-UploadFileId:$parameters[FileId]",
            );

        }else{

            $file_sign = hash_hmac('sha1', "AccessToken=$access_token&Operate=$method&RequestURI=$url&Date=$time",$this->sk);
            $url = "http://upload.cloud.189.cn" . $url ."";

            $header = array(
                "AccessToken:$access_token",
                "Signature:$file_sign",
                "Date:$time",
                "Edrive-ParentFolderId:$parameters[folder_id]",
                //新文件为空
                "Edrive-BaseFileId:",
                "Edrive-FileName:$parameters[filename]",
                "Edrive-FileMD5:$parameters[md5]",
                "Content-Length:$parameters[file_size]",
            );
        }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $output = curl_exec($ch);
            $arr=simplexml_load_string($output);
            if(isset($arr->code)&&isset($arr->message)){
                $info = curl_getinfo($ch);
                echo "<span style='color: red'>请求时间</span>：" . $info['total_time'] ."秒";
                echo "<br/><span style='color: red'>错误信息：</span>".$output;
                curl_close($ch);die;
            } else {
                curl_close($ch);
                return $arr;
            }
    }

}
