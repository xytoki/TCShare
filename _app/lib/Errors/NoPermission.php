<?php
namespace xyToki\xyShare\Errors;
class NoPermission extends \Exception{
    protected $message = NULL;
    function __construct(){
        $this->message = "无权限";
    }
}