var __rest_server_url = 'http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/api/';
var __client_email = '';
var __session_id = '';
var __prepend_hash = '!';

$(function() {
    var DEFAULT_HASH = 'registration';
    //setup hasher
    hasher.prependHash = __prepend_hash;
    //setup crossroads
    var route1 = crossroads.addRoute('registration');
    var route2 = crossroads.addRoute('user/{client_id}/{session_id}');
    
    crossroads.routed.add(console.log, console); //log all routes
   
    route1.matched.add(function(){
        console.log('handler 1');
        var data = {};
        $.get('templates/registration.tmp.html',{},function(html){
            var output = Plates.bind(html, data);
            $('#container').empty().html(output);
        });
    });
    
    route2.matched.add(function(client_id, session_id){
        console.log('handler 2: '+ client_id + ' : ' + session_id);
        __client_email = client_id;
        __session_id = session_id;
        
        var _url = __rest_server_url + 'User/' + encodeURIComponent(__client_email);
    
        var _data = {'result' : {'apikey_1':'apikey_1111111'}};
        $.get(_url,{},function(_data){
            var data = {
                'email' : _data.result.id,
                'api_key_1' : _data.result.apikey_1,
                'api_key_2' : _data.result.apikey_2,
                'secret_key_1' : _data.result.secretkey_2,
                'secret_key_2' : _data.result.secretkey_1,
                'old_email' : _data.result.id
            };
            var map = Plates.Map();
            map.where('id').is('email').use('email').as('value');
            map.where('id').is('api_key_1').use('api_key_1').as('value');
            map.where('id').is('api_key_2').use('api_key_2').as('value');
            map.where('id').is('old_email').use('old_email').as('value');
            map.where('id').is('client_id').use('client_id').as('value');
            
            $.get('templates/home.tmp.html',{},function(html){
                var output = Plates.bind(html, data, map);
                $('#container').empty().html(output);
            });
        });    
    });
    
    //only required if you want to set a default value

    if(! hasher.getHash()){
        hasher.setHash(DEFAULT_HASH);
    }

    function parseHash(newHash, oldHash){
        // second parameter of crossroads.parse() is the "defaultArguments" and should be an array
        // so we ignore the "oldHash" argument to avoid issues.
        crossroads.parse(newHash);
    }

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for hash changes
});