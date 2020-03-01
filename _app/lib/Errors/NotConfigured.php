<?php
namespace xyToki\xyShare\Errors;
class NotConfigured implements \Throwable{
    public function getMessage(){
        return "配置不正确";
    }
}