$(function() {
    var _url = __rest_server_url + 'User/' + encodeURIComponent(__client_email);
    
    route2.matched.add(function(client_id, session_id){
        console.log('handler 2: '+ client_id + ' : ' + session_id);
        __client_email = client_id;
        __session_id = session_id;
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
});