<?php
    $m = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    $player=$_GET['player'];
    if(!isset($_GET['player'])){
        if($m)$player="dplayer";
    }
    if($player=='dplayer'){
        include(dirname(__FILE__)."/preview_dplayer.php");
    }else{
        include(dirname(__FILE__)."/preview_artplayer.php");
    }
?>