'use strict';

var AccountsController = Stapes.subclass({
    constructor : function(DEFAULT_HASH) {
        var self = this;
        
        this.model = new AccountModel();
        this.view = new AccountsView();
        
        var _url = __rest_server_url + 'User/' + encodeURIComponent(__client_email);
    
        $.ajax({
            url: _url,
            type: "GET",
            dataType: "json",
            error: function() {
                self.view.error('Username or password is invalid.');
                setTimeout(function(){
                    hasher.setHash(DEFAULT_HASH);
                }, 1000);
            },
            success : function(_data){
                if(_data.success){
                    self.model.set({
                        "first_name" : _data.result.name_1,
                        "last_name" : _data.result.name_2,
                        "email" : _data.result.id,
                        "client_id" : _data.result.clientid,
                        "api_key_1" : _data.result.apikey_1,
                        "api_key_2" : _data.result.apikey_2,
                        "secret_key_1" : _data.result.secretkey_1,
                        "secret_key_2" : _data.result.secretkey_2,
                        "password" : _data.result.password,
                        "address_1" : _data.result.address_1,
                        "address_2" : _data.result.address_2,
                        "city" : _data.result.city,
                        "state" : _data.result.state,
                        "postalcode" : _data.result.postalcode,
                        "country" : _data.result.country,
                        "phone_1" : _data.result.phone_1,
                        "active_1" : _data.result.active_1,
                        "active_2" : _data.result.active_2,
                        "server" : _data.result.server,
                        "port" : _data.result.port,
                        "username" : _data.result.username,
                        "dbpassword" : _data.result.dbpassword,
                        "databasename" : _data.result.databasename,
                        "security_salt" : _data.result.security_salt,
                        "id_sequence" : _data.result.id_sequence
                    });
                    self.model.map_values();
                }else{
                    self.view.error('Username or password is invalid.');
                    setTimeout(function(){
                        hasher.setHash(DEFAULT_HASH);
                    }, 1000);
                }
            }
        });
        
        this.$el = $('form');
        this.$el.on('submit', function(e) {
            e.preventDefault();
        });
    
        this.view.on({
            'generateAPIKeyAndSecret1' : function() {
                console.log('generateAPIKeyAndSecret1');
            
                var _url = __rest_server_url + 'User/keypair1/' + encodeURIComponent(__client_email);
    
                $.ajax({
                    url: _url,
                    type: "PUT",
                    dataType: "json",
                    error: function() {
                        self.view.error('An error occured while re-generating the key pair. Please try again.');
                    },
                    success : function(_data){
                        if(_data.success){
                            self.view.success('Key pair has been generated successfully.');
                        
                            //Set modified values to the Account Object
                            self.model.set({
                                'api_key_1':_data.result.apikey_1,
                                'secret_key_1':_data.result.secretkey_1
                            });
                            //Map values to the page
                            self.model.map_values();
                            $('#generateNewAPIAndSecretKey1Close').click();
                        }else{
                            self.view.error('An error occured while re-generating the key pair. Please try again.');
                        }
                    }
                });
            },
            
            'generateAPIKeyAndSecret2': function() {
                console.log('generateAPIKeyAndSecret2');
            
                var _url = __rest_server_url + 'User/keypair2/' + encodeURIComponent(__client_email);
    
                $.ajax({
                    url: _url,
                    type: "PUT",
                    dataType: "json",
                    error: function() {
                        self.view.error('An error occured while re-generating the key pair. Please try again.');
                    },
                    success : function(_data){
                        if(_data.success){
                            self.view.success('Key pair has been generated successfully.');
                        
                            //Set modified values to the Account Object
                            self.model.set({
                                'api_key_2':_data.result.apikey_2,
                                'secret_key_2':_data.result.secretkey_2
                            });
                            //Map values to the page
                            self.model.map_values();
                            $('#generateNewAPIAndSecretKey2Close').click();
                        }else{
                            self.view.error('An error occured while re-generating the key pair. Please try again.');
                        }
                    }
                });
            }
        });
    }
});