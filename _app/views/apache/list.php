<!DOCTYPE html>
<html>
    <head>
	    <title>Index of <?php echo $path;?></title>
        <meta charset="utf-8">
        <style>
		*{box-sizing:border-box}hr{border: 0;border-bottom:1px solid silver;}h1{border-bottom:1px solid silver;margin-bottom:10px;padding-bottom:10px;white-space:nowrap}table{border-collapse:collapse;font-family:Consolas,monaco,monospace}th{font-weight:700}.file-name{text-align:left}.file-size{padding-left:4em}.file-date-created,.file-date-modified{padding-left:2em}.file-date-created,.file-date-modified,.file-size{text-align:end;white-space:nowrap}.icon{padding-left:1.5em;text-decoration:none}.icon:hover{text-decoration:underline}.icon-file{background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAABnRSTlMAAAAAAABupgeRAAABHUlEQVR42o2RMW7DIBiF3498iHRJD5JKHurL+CRVBp+i2T16tTynF2gO0KSb5ZrBBl4HHDBuK/WXACH4eO9/CAAAbdvijzLGNE1TVZXfZuHg6XCAQESAZXbOKaXO57eiKG6ft9PrKQIkCQqFoIiQFBGlFIB5nvM8t9aOX2Nd18oDzjnPgCDpn/BH4zh2XZdlWVmWiUK4IgCBoFMUz9eP6zRN75cLgEQhcmTQIbl72O0f9865qLAAsURAAgKBJKEtgLXWvyjLuFsThCSstb8rBCaAQhDYWgIZ7myM+TUBjDHrHlZcbMYYk34cN0YSLcgS+wL0fe9TXDMbY33fR2AYBvyQ8L0Gk8MwREBrTfKe4TpTzwhArXWi8HI84h/1DfwI5mhxJamFAAAAAElFTkSuQmCC) left top no-repeat}.icon-dir{background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAd5JREFUeNqMU79rFUEQ/vbuodFEEkzAImBpkUabFP4ldpaJhZXYm/RiZWsv/hkWFglBUyTIgyAIIfgIRjHv3r39MePM7N3LcbxAFvZ2b2bn22/mm3XMjF+HL3YW7q28YSIw8mBKoBihhhgCsoORot9d3/ywg3YowMXwNde/PzGnk2vn6PitrT+/PGeNaecg4+qNY3D43vy16A5wDDd4Aqg/ngmrjl/GoN0U5V1QquHQG3q+TPDVhVwyBffcmQGJmSVfyZk7R3SngI4JKfwDJ2+05zIg8gbiereTZRHhJ5KCMOwDFLjhoBTn2g0ghagfKeIYJDPFyibJVBtTREwq60SpYvh5++PpwatHsxSm9QRLSQpEVSd7/TYJUb49TX7gztpjjEffnoVw66+Ytovs14Yp7HaKmUXeX9rKUoMoLNW3srqI5fWn8JejrVkK0QcrkFLOgS39yoKUQe292WJ1guUHG8K2o8K00oO1BTvXoW4yasclUTgZYJY9aFNfAThX5CZRmczAV52oAPoupHhWRIUUAOoyUIlYVaAa/VbLbyiZUiyFbjQFNwiZQSGl4IDy9sO5Wrty0QLKhdZPxmgGcDo8ejn+c/6eiK9poz15Kw7Dr/vN/z6W7q++091/AQYA5mZ8GYJ9K0AAAAAASUVORK5CYII=) left top no-repeat}.icon-up{background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmlJREFUeNpsU0toU0EUPfPysx/tTxuDH9SCWhUDooIbd7oRUUTMouqi2iIoCO6lceHWhegy4EJFinWjrlQUpVm0IIoFpVDEIthm0dpikpf3ZuZ6Z94nrXhhMjM3c8895977BBHB2PznK8WPtDgyWH5q77cPH8PpdXuhpQT4ifR9u5sfJb1bmw6VivahATDrxcRZ2njfoaMv+2j7mLDn93MPiNRMvGbL18L9IpF8h9/TN+EYkMffSiOXJ5+hkD+PdqcLpICWHOHc2CC+LEyA/K+cKQMnlQHJX8wqYG3MAJy88Wa4OLDvEqAEOpJd0LxHIMdHBziowSwVlF8D6QaicK01krw/JynwcKoEwZczewroTvZirlKJs5CqQ5CG8pb57FnJUA0LYCXMX5fibd+p8LWDDemcPZbzQyjvH+Ki1TlIciElA7ghwLKV4kRZstt2sANWRjYTAGzuP2hXZFpJ/GsxgGJ0ox1aoFWsDXyyxqCs26+ydmagFN/rRjymJ1898bzGzmQE0HCZpmk5A0RFIv8Pn0WYPsiu6t/Rsj6PauVTwffTSzGAGZhUG2F06hEc9ibS7OPMNp6ErYFlKavo7MkhmTqCxZ/jwzGA9Hx82H2BZSw1NTN9Gx8ycHkajU/7M+jInsDC7DiaEmo1bNl1AMr9ASFgqVu9MCTIzoGUimXVAnnaN0PdBBDCCYbEtMk6wkpQwIG0sn0PQIUF4GsTwLSIFKNqF6DVrQq+IWVrQDxAYQC/1SsYOI4pOxKZrfifiUSbDUisif7XlpGIPufXd/uvdvZm760M0no1FZcnrzUdjw7au3vu/BVgAFLXeuTxhTXVAAAAAElFTkSuQmCC) left top no-repeat}
        </style>
    </head>
    <body>
		<h1 id="heading">Index of <?php echo urldecode($path);?></h1>
		<table id="table">
			<tr>
			    <th class="file-name"><a href="?sort=name&order=<?php echo ($sort!='name'||$order=='desc')?"asc":"desc";?>">Name</a></th>
			    <th class="file-size"><a href="?sort=size&order=<?php echo ($sort!='size'||$order=='desc')?"asc":"desc";?>">Size</a></th>
			    <th class="file-date-modified"><a href="?sort=timeModified&order=<?php echo ($sort!='timeModified'||$order=='desc')?"asc":"desc";?>">Date Modified</a></th>
			</tr>
			<?php if($path != '/'):?>
				<tr>
					<td class="file-name">
						<a class="icon icon-up" href="<?php echo TC::abspath($path,"../");?>">..</a>
					</td>
					<td class="file-size"></td>
					<td class="file-date-modified"></td>
				</tr>
			<?php endif;?>
			<?php foreach($folders as $item):?>
					<tr>
						<td class="file-name"><a class="icon icon-dir" href="<?php echo TC::abspath($path,$item->name());?>"><?php echo $item->name();?>/</a></td>
						<td class="file-size"> - </td>
						<td class="file-date-modified"><?php echo $item->timeModified(); ?></td>
						<!-- <?php var_dump($item); ?> -->
					</tr>
			<?php endforeach;?>
			<?php foreach($files as $item):?>
					<tr>
						<td class="file-name"><a class="icon icon-file" data-readypreview="<?php echo TC::ext($item->name());?>" href="<?php echo TC::abspath($path,$item->name());?>"><?php echo $item->name();?></a></td>
						<td class="file-size"><?php echo TC::human_filesize($item->size());?></td>
						<td class="file-date-modified"><?php echo $item->timeModified();?></td>
						<!-- <?php var_dump($item); ?> -->
					</tr>
			<?php endforeach;?>
        	</table>
            <hr>
            <address>TCShare server at <?php echo $_SERVER['HTTP_HOST'];?> port <?php echo $_SERVER['SERVER_PORT'];?></address>
            <?php TC::readypreview(); ?>
            <script>
            TC.askPreview=function(){
                return new Promise(function(resolve,reject){
                   if(confirm("Press [Yes] to preview , [Cancel] to download."))return resolve();
                   reject();
                });
            }
            </script>
    </body>
</html>