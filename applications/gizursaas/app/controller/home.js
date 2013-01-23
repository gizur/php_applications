$(function() {
    var _url = __rest_server_url + 'User/' + encodeURIComponent(__client_email);
    
    $.ajax({
        url:_url,
        dataType:'json',
        success:'callbackName',
        cache:true,
        crossDomain:true,
        error:function(){
            alert("Error");
        }
    });
    
    function callbackName(out){
        if(out != undefined){
            alert(out);
            if(out.success)
                alert("Success");
        }
    }
});