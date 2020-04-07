<?php   
TC::layout(
["path"=>$path],
function() use($files,$folders,$path,$sort,$order,$current){
    
    $file_ico=function($ext){
        if(in_array($ext,['bmp','jpg','jpeg','png','gif',"webp"])){
      	    return "image";
        }
        if(in_array($ext,['mp4','mkv','webm','avi','mpg', 'mpeg', 'rm', 'rmvb', 'mov', 'wmv', 'mkv', 'asf'])){
      	    return "ondemand_video";
        }
        if(in_array($ext,['ogg','mp3','wav','flac','m4a'])){
      	    return "audiotrack";
		}
        if(in_array($ext,['ass','srt'])){
      	    return "playlist_play";
		}
		if($ext=="lrc"){
			return "queue_music";
		}
        return "insert_drive_file";
    }
?>
<link rel="stylesheet" href="<?php echo TC::viewpath("/static/css/glightbox.min.css");?>">
<div class="mdui-container-fluid">
<div class="mdui-typo nexmoe-item header-content" style="padding: 5px 20px !important;display:none;"></div>
<style>
.thumb .th{
	display: none;
}
.thumb .mdui-text-right{
	display: none;
}
.thumb .mdui-list-item{
	max-width:calc( 50% - 20px );
    background-size: contain;
	background-repeat: no-repeat;
	background-position: center;
}
.thumb .mdui-list-item a ,.thumb .mdui-list-item {
	width:217px;
	height: 230px;
	float: left;
	margin: 10px 10px !important;
}

.thumb .mdui-col-xs-12,.thumb .mdui-col-sm-7{
	width:100% !important;
	height:230px;
}

.thumb .mdui-list-item .mdui-icon{
	font-size:100px;
	display: block;
	margin-top: 40px;
	color: #7ab5ef;
}
.thumb .mdui-list-item span{
	float: left;
	display: block;
	text-align: center;
	width:100%;
	position: absolute;
    top: 180px;
}
.forcedownload{
    display:block;
    float:right;
}
.thumb .forcedownload{
    display:none;
}
.forcedownload:hover{
    color:#555;
}
.header-content p {
    margin: 0;
}
</style>
<div class="nexmoe-item">
<div class="mdui-row">
	<ul class="mdui-list">
		<li class="mdui-list-item th">
		  	<div class="mdui-col-xs-12 mdui-col-sm-7">
		  		<a href="?sort=name&order=<?php echo ($sort!='name'||$order=='desc')?"asc":"desc";?>">文件
				  <i class="mdui-icon material-icons icon-sort" data-sort="name" data-order="downward"><?php echo ($sort=='name')?($order=='desc'?"expand_more":"expand_less"):"";?></i>
				</a>
			</div>
		  	<div class="mdui-col-sm-3 mdui-text-right">
			  <a href="?sort=timeModified&order=<?php echo ($sort!='timeModified'||$order=='desc')?"asc":"desc";?>">修改时间
			 	<i class="mdui-icon material-icons icon-sort" data-sort="timeModified" data-order="downward"><?php echo ($sort=='timeModified')?($order=='desc'?"expand_more":"expand_less"):"";?></i>
				 </a></div>
		  	<div class="mdui-col-sm-2 mdui-text-right">
			  <a href="?sort=size&order=<?php echo ($sort!='size'||$order=='desc')?"asc":"desc";?>">大小
				<i class="mdui-icon material-icons icon-sort" data-sort="size" data-order="downward"><?php echo ($sort=='size')?($order=='desc'?"expand_more":"expand_less"):"";?></i>
				</a></div>
		  <i class="mdui-icon material-icons" style="opacity:0">file_download</i>
		</li>
		<?php if($path != '/'):?>
		<li class="mdui-list-item mdui-ripple">
			<a href="<?php echo TC::abspath($path,"../");?>">
			  <div class="mdui-col-xs-12 mdui-col-sm-7">
				<i class="mdui-icon material-icons">arrow_upward</i>
		    	..
			  </div>
			  <div class="mdui-col-sm-3 mdui-text-right"></div>
			  <div class="mdui-col-sm-2 mdui-text-right"></div>
		  	</a>
		</li>
		<?php endif;?>
		
		<?php foreach($folders as $item):?>
		<li class="mdui-list-item mdui-ripple">
			<a href="<?php echo TC::abspath($path,$item->name());?>/">
			  <div class="mdui-col-xs-12 mdui-col-sm-7 mdui-text-truncate">
				<i class="mdui-icon material-icons">folder_open</i>
		    	<span><?php echo $item->name();?></span>
			  </div>
			  <div class="mdui-col-sm-3 mdui-text-right"><?php echo $item->timeModified();?></div>
			  <div class="mdui-col-sm-2 mdui-text-right"></div>
		  	</a>
			<div class="forcedownload" >
			<?php if(method_exists($item,"zipDownload")){ ?>
			      <a title="打包下载" href="<?php echo TC::abspath($path,rawurlencode($item->name()));?>/?TC_zip">
			          <i class="mdui-icon material-icons">archive</i>
			      </a>
			<?php } ?>
			</div>
		</li>
		<?php endforeach;?>
		<?php foreach($files as $item):?>
		<li class="mdui-list-item file mdui-ripple">
			<a	data-name="<?php echo $item->name();?>"
				data-readypreview="<?php echo TC::ext($item->name());?>"
				<?php echo $file_ico($item->extension())=="image"?'class="glightbox" data-gallery="tcshare"':"";?>
				href="<?php echo TC::abspath($path,rawurlencode($item->name()));?>"
			>
			  	<div class="mdui-col-xs-12 mdui-col-sm-7 mdui-text-truncate">
					<i class="mdui-icon material-icons" data-thumbnail="<?php echo $item->thumbnail();?>"><?php echo $file_ico($item->extension());?></i>
		    		<span><?php echo $item->name() ;?></span>
			  	</div>
			  	<div class="mdui-col-sm-3 mdui-text-right"><?php echo $item->timeModified();?></div>
			  	<div class="mdui-col-sm-2 mdui-text-right"><?php echo TC::human_filesize($item->size());?></div>
		  	</a>
			<div class="forcedownload" >
			      <a title="直接下载" href="<?php echo TC::abspath($path,rawurlencode($item->name()));?>">
			          <i class="mdui-icon material-icons">file_download</i>
			      </a>
			</div>
		</li>
		<?php endforeach;?>
	</ul>
</div>
</div>
<div class="mdui-typo nexmoe-item mdui-shadow-3 readme-box" style="padding: 20px !important;display:none">
	<div class="mdui-chip">
	  <span class="mdui-chip-icon"><i class="mdui-icon material-icons">face</i></span>
	  <span class="mdui-chip-title">README.md</span>
	</div>
	<div class="readme-content"></div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/flyio/dist/fly.min.js"></script>
<?php
	TC::readypreview();
	TC::viewjs("/static/js/glightbox.min.js",false);
	TC::viewjs("theme.js");
?>
<a href="javascript:thumb();" class="mdui-fab mdui-fab-fixed mdui-ripple mdui-color-theme-accent"><i class="mdui-icon material-icons">format_list_bulleted</i></a>
    <?php
    });
?>