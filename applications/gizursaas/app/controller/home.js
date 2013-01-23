$(function() {
    var _url = __rest_server_url + 'User/' + encodeURIComponent(__client_email);
    
    $.get(_url,{},function(_data){
        var data = {
            'email' : _data.result.id,
            'api_key_1' : _data.result.apikey_1,
            'api_key_2' : _data.result.apikey_2,
            'secret_key_1' : _data.result.secretkey_2,
            'secret_key_2' : _data.result.secretkey_1,
            'old_email' : _data.result.id
        };
        $.get('templates/home.tmp.html',{},function(html){
            var output = Plates.bind(html, data);
            $('#container').empty().html(output);
        });
    });
    
});