window.TC = window.TC||{};
/* 音频预览 */
TC.audio_exts=TC.audio_ext||[
    "mp3",
    "aac",
    "m4a",
    "flac",
    "ape",
    "ogg",
    "wav",
];
TC.preview_audio = function(aud){
    if(!TC.aplayer){
        TC.aplayerList=[];
        jQuery("a[data-readypreview]").each(function(){
            var ext = jQuery(this).data("readypreview");
            if(TC.audio_exts.indexOf(ext)!==-1){
                var n = jQuery(this).find("span").text();
                var l = n.replace("."+ext,".lrc");
                var la = jQuery('a[data-name="'+l+'"]');
                var lrc = undefined;
                if(la.length>0){
                    lrc = la[0].href+"?TC_direct";
                }
                TC.aplayerList.push({
                    name:n,
                    url:this.href,
                    artist:" ",
                    lrc:lrc
                });
            }
        })
        jQuery('<div id="aplayer">').appendTo("body");
        TC.aplayer = new APlayer({
            container: document.getElementById('aplayer'),
            fixed: true,
            audio: TC.aplayerList,
            lrcType: 3
        });
    }
    var k=-1;
    for(var i in TC.aplayerList){
        if(TC.aplayerList[i].name==jQuery(aud).data("name")){
            k=i;
            break;
        }
    }
    if(k>=0){
        TC.aplayer.list.switch(k);
        TC.aplayer.play();
        TC.aplayer.setMode("normal");
    }
}
TC.askPreview=function(ext,link){
    return new Promise(function(resolve,reject){
        if($('.mdui-fab i').text() != "apps")return resolve();
        if(confirm("请选择您的操作\n[确认]:预览\n[取消]:下载"))return resolve();
        reject();
    });
}
jQuery(".forcedownload").click(function(e){
   e.stopPropagation();
});
function downall() {
     let dl_link_list = Array.from(document.querySelectorAll("li a"))
         .map(x => x.href) // 所有list中的链接
         .filter(x => x.slice(-1) != "/"); // 筛选出非文件夹的文件下载链接

     let blob = new Blob([dl_link_list.join("\r\n")], {
         type: 'text/plain'
     }); // 构造Blog对象
     let a = document.createElement('a'); // 伪造一个a对象
     a.href = window.URL.createObjectURL(blob); // 构造href属性为Blob对象生成的链接
     a.download = "folder_download_link.txt"; // 文件名称，你可以根据你的需要构造
     a.click() // 模拟点击
     a.remove();
}
jQuery(".getlink-btn").click(function(){
	var dl_link_list = Array.from(jQuery('a[data-readypreview]'))
        .map(x => x.href) 				  // 所有list中的链接
	copyToClipboard(dl_link_list.join("\r\n"));
	mdui.alert("全部文件链接已复制到剪贴板");
})
function thumb(){
	if($('.mdui-fab i').text() == "apps"){
		$('.mdui-fab i').text("format_list_bulleted");
		$('.nexmoe-item').removeClass('thumb');
		$('.nexmoe-item .mdui-icon').show();
		$('.nexmoe-item .mdui-list-item').css("background","");
	}else{
		$('.mdui-fab i').text("apps");
		$('.nexmoe-item').addClass('thumb');
		$('.mdui-col-xs-12 i.mdui-icon').each(function(){
			if($(this).text() == "image" || $(this).text() == "ondemand_video"){
			    try{
			        var j = jQuery(this).data("thumbnail");
					if(!j||j.trim()=="")return;
				    jQuery(this).hide();
				    jQuery(this).parent().parent().parent().css("background","url("+j+")  no-repeat center");
			    }catch(e){
			        console.error(e)
			    }
			}
		});
	}
}
var lightbox = GLightbox();
/* README.md */
TC.readme_render = function(readmeLink,headerLink){
    var todo=[
        TC.loadScript("https://lib.baomitu.com/marked/0.8.0/marked.min.js")
    ]
    if(readmeLink){
        todo.push(fly.get(readmeLink));
    }else{
        todo.push(Promise.resolve(false));
    }
    if(headerLink){
        todo.push(fly.get(headerLink));
    }else{
        todo.push(Promise.resolve(false));
    }
    Promise.all(todo).then(function(reses){
        if(reses[1]){
            var readmeTxt = reses[1].data;
            jQuery(".readme-content").html(marked(readmeTxt));
            jQuery(".readme-box").show();
        }
        if(reses[2]){
            var headerTxt = reses[2].data;
            jQuery(".header-content").html(marked(headerTxt)).show();
        }
    })
};
(function(){
    var readme_link,header_link;
    jQuery("a[data-readypreview=md]").each(function(){
        if(jQuery(this).data("name").toLowerCase()=="readme.md"){
            readme_link = "?TC_getfile="+jQuery(this).data("name")+"&TC_direct";
        }
        if(jQuery(this).data("name").toLowerCase()=="header.md"){
            header_link = "?TC_getfile="+jQuery(this).data("name")+"&TC_direct";
        }
    })
    TC.readme_render(readme_link,header_link);
})();