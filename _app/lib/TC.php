<?php
/* @file TC.php 工具类
 * @package TCShare
 * @author xyToki
 */
Class TC{
    static function get($k){
        global $RUN;
        return isset($RUN['app'][$k])?$RUN['app'][$k]:$RUN[$k];
    }
    static function toArr($a){
        if(!is_array($a)||isset($a['id']))$a=[$a];
        return $a;
    }
    static function get_preview_ext(){
        try{
            return json_decode(file_get_contents(_LOCAL."/views/".self::get('theme')."/config.json"),true);
        }catch(Throwable $e){
            return ["unsupported"=>""];
        }
    }
    static function path($path,$withurl=true){
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
        return ($withurl?self::get('URLBASE'):"").str_replace('//','/','/'.implode('/', $absolutes));
    }
    static function abspath($path,$path2="/"){
        return self::path(self::get('route').$path.$path2);
    }
    static function viewpath($file){
        return self::path("/_app/views/".$file);
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
            <script>window.TC=window.TC||{};TC.preview_exts=<?php echo json_encode(array_keys(self::get_preview_ext()));?></script>
            <script src="<?php echo self::viewpath("/readypreview.js");?>"></script>
        <?php
	}
	static function layout($vars=[],$callback=false){
	    Flight::render(self::get('theme')."/layout",array_merge($vars,[
	        "callback"=>$callback
	    ]));
	}

}