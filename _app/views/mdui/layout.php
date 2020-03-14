<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no"/>
	<title><?php echo $path;?> - <?php echo TC::get('name');?></title>
	<link crossorigin="anonymous" integrity="sha384-N/Iletid8Xog0I1zaAyyFWHPC0YFQjThjRguLT4DGi89QcfFUQx+dtgk1c7mmNCT" href="https://lib.baomitu.com/mdui/0.4.3/css/mdui.min.css" rel="stylesheet">
	<style>
		body{background-color:#f2f5fa;padding-bottom:60px;background-position:center bottom;background-repeat:no-repeat;background-attachment:fixed}.nexmoe-item{margin:20px -8px 0!important;padding:15px!important;border-radius:5px;background-color:#fff;-webkit-box-shadow:0 .5em 3em rgba(161,177,204,.4);box-shadow:0 .5em 3em rgba(161,177,204,.4);background-color:#fff}.mdui-img-fluid,.mdui-video-fluid{border-radius:5px;border:1px solid #eee}.mdui-list{padding:0}.mdui-list-item{margin:0!important;border-radius:5px;padding:0 10px 0 5px!important;border:1px solid #eee;margin-bottom:10px!important}.mdui-list-item:last-child{margin-bottom:0!important}.mdui-list-item:first-child{border:none}.mdui-toolbar{width:auto;margin-top:60px!important}.mdui-appbar .mdui-toolbar{height:56px;font-size:16px}.mdui-toolbar>*{padding:0 6px;margin:0 2px;opacity:.5}.mdui-toolbar>.mdui-typo-headline{padding:0 16px 0 0}.mdui-toolbar>i{padding:0}.mdui-toolbar>a:hover,a.mdui-typo-headline,a.active{opacity:1}.mdui-container{max-width:980px}.mdui-list>.th{background-color:initial}.mdui-list-item>a{width:100%;line-height:48px}.mdui-toolbar>a{padding:0 16px;line-height:30px;border-radius:30px;border:1px solid #eee}.mdui-toolbar>a:last-child{opacity:1;background-color:#1e89f2;color:#ffff}@media screen and (max-width:980px){.mdui-list-item .mdui-text-right{display:none}.mdui-container{width:100%!important;margin:0}.mdui-toolbar>*{display:none}.mdui-toolbar>a:last-child,.mdui-toolbar>.mdui-typo-headline,.mdui-toolbar>i:first-child{display:block}}
	</style>
	<script crossorigin="anonymous" integrity="sha384-GXQQyAWEQJOklZd/6CWH3BbffdVqZ85WoDiENXxSqLpjrdWzpX15CKmya8HIdM4r" src="https://lib.baomitu.com/mdui/0.4.3/js/mdui.min.js"></script>
</head>
<body class="mdui-theme-primary-blue-grey mdui-theme-accent-blue">
	
	<div class="mdui-container">
	    <div class="mdui-container-fluid">
	    <div class="mdui-toolbar nexmoe-item">
			<a href="<?php echo TC::abspath("/");?>"><?php echo TC::get('name');?></a>
			<?php 
			$navs=explode("/",$path);
			array_shift($navs);
			array_pop($navs);
			foreach($navs as $i=>$n):?>
			<i class="mdui-icon material-icons mdui-icon-dark" style="margin:0;">chevron_right</i>
			<a href="<?php echo TC::abspath("/".implode("/",array_slice($navs,0,$i+1))); ?>"><?php echo $n;?></a>
			<?php endforeach;?>
		</div>
		</div>
    	<?php $callback();?>
  	</div>
</body>
</html>
