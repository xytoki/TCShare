<?php
namespace xyToki\xyShare\Errors;
class NotConfigured extends \Exception{
    protected $message = NULL;
    function __construct(){
        $this->message = "配置不正确";
    }
}