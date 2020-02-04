jQuery("a[data-readypreview]").click(function(e){
    var ext=jQuery(this).data("readypreview");
    if(TC.preview_exts.indexOf(ext)!==-1){
        TC.askPreview(ext,jQuery(this)).then(function(){
            e.preventDefault();
            jQuery('<form method="post">').attr("action",this.href).appendTo("body").submit();
        }.bind(this)).catch(function(){});
    } 
});
window.TC=window.TC||{};
TC.askPreview=function(ext,link){
    return new Promise(function(resolve,reject){
       resolve(); 
    });
}