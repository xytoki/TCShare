<?php
namespace xyToki\xyShare{
interface authProvider{

};
interface contentProvider {
    function getFileInfo($file);
    function listFiles($path);
}
interface abstractInfo{
    function name();
    function timeModified();
    function timeCreated();
    function isFolder();
}
interface fileInfo extends abstractInfo{
    function url();
    function size();
    function extension();
    function thumbnail();
}
interface folderInfo extends abstractInfo{
    function hasIndex();
}
}
namespace xyToki\xyShare\Rules{
    interface abstractRule {
        static function check(String $path,Array $config);
    }
}