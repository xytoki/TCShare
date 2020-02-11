<?php
/* @file TC.php 工具类
 * @package TCShare
 * @author xyToki
 */
Class TC{
    static function toArr($a){
        if(!is_array($a)||isset($a['id']))$a=[$a];
        return $a;
    }
    static function get_preview_ext(){
        try{
            return include("views/".APP_THEME."/config.php");
        }catch(Throwable $e){
            return ["unsupported"=>""];
        }
    }
    static function abspath($path,$path2="/"){
        $path.=$path2;
        $path = str_replace(array('/', '\\', '//'), '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return APP_BASE_PATH.str_replace('//','/','/'.implode('/', $absolutes));
    }
    static function viewpath($file){
        return self::abspath("/_app/views",$file);
    }
    static function human_filesize($size, $precision = 1) {
		for($i = 0; ($size / 1024) > 1; $i++, $size /= 1024) {}
		return round($size, $precision).(['B','KB','MB','GB','TB','PB','EB','ZB','YB'][$i]);
	}
	static function ext($file){
	    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
	}
	static function readyPreview(){
	    ?>
            <script src="https://lib.baomitu.com/jquery/3.4.1/jquery.slim.min.js"></script>
            <script>window.TC=window.TC||{};TC.preview_exts=<?php echo json_encode(array_keys(TC::get_preview_ext()));?></script>
            <script src="<?php echo self::viewpath("/readypreview.js");?>"></script>
        <?php
	}
	static function layout($vars=[],$callback=false){
	    Flight::render(APP_THEME."/layout",array_merge($vars,[
	        "callback"=>$callback   
	    ]));
	}

}