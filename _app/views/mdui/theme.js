window.TC = window.TC||{};
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