<?php
namespace xyToki\xyShare\Errors;
class NotAuthorized extends \Exception{
    protected $message = NULL;
    function __construct(){
        $this->message = "未授权";
    }
}