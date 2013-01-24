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
        $.get('templates/registration.tmp.html',{},function(html){
            $('#container').empty().html(html);
        });
    });
    
    route2.matched.add(function(client_id, session_id){
        console.log('handler 2: '+ client_id + ' : ' + session_id);
        __client_email = client_id;
        __session_id = session_id;
        
        var _url = __rest_server_url + 'User/' + encodeURIComponent(__client_email);
    
        $.ajax({
            url: _url,
            type: "GET",
            dataType: "json",
            error: function() {
                $('#errorMessageBox').addClass('alert alert-error')
                .empty()
                .html('<button data-dismiss="alert" class="close" type="button">×</button>Username or password is invalid.');
                setTimeout(function(){
                    hasher.setHash(DEFAULT_HASH);
                }, 1000);
            },
            success : function(_data){
                if(_data.success){
                    $.get('templates/home.tmp.html',{},function(html){
                        $('#container').empty().html(html);
                        $('#email').val(_data.result.id);
                        $('#api_key_1').val(_data.result.apikey_1);
                        $('#api_key_2').val(_data.result.apikey_2);
                        $('#secret_key_1').text(_data.result.secretkey_1);
                        $('#secret_key_2').text(_data.result.secretkey_2);
                        $('#old_email').val(_data.result.id);
                        $('#client_id').val(_data.result.clientid);
                    });
                }else{
                    $('#errorMessageBox').addClass('alert alert-error')
                    .empty()
                    .html('<button data-dismiss="alert" class="close" type="button">×</button>Username or password is invalid.');
                    setTimeout(function(){
                        hasher.setHash(DEFAULT_HASH);
                    }, 1000);
                }
            }
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