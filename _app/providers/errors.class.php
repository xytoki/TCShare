<?php
namespace xyToki\xyShare\Errors;
class NotAuthorized extends \Error{

}
class NotConfigured implements \Throwable{
    function getMessage(){
        return "Key not configured.Please check config.php";
    }
}
class NotFound extends \Error{

}
class NoPermission extends \Error{

}