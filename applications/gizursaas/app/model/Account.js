'use strict';

var AccountModel = Stapes.subclass({
    "client_id" : '',
    "api_key_1" : '',
    "api_key_2" : '',
    "secret_key_1" : '',
    "secret_key_2" : '',
    constructor : function (client_id, api_key_1, api_key_2, secret_key_1, secret_key_2) {
        this.client_id = client_id;
        this.api_key_1 = api_key_1;
        this.api_key_2 = api_key_2;
        this.secret_key_1 = secret_key_1;
        this.secret_key_2 = secret_key_2;
    },
    'assign_values' : function (client_id, api_key_1, api_key_2, secret_key_1, secret_key_2) {
        this.client_id = client_id;
        this.api_key_1 = api_key_1;
        this.api_key_2 = api_key_2;
        this.secret_key_1 = secret_key_1;
        this.secret_key_2 = secret_key_2;
    },
    'validate' : function(){
        return true;
    },
    'mapValues' : function(){
        //$('#email').val(_data.result.id);
        $('#api_key_1').val(this.api_key_1);
        $('#api_key_2').val(this.api_key_2);
        $('#secret_key_1').text(this.secret_key_1);
        $('#secret_key_2').text(this.secret_key_2);
        //$('#old_email').val(_data.result.id);
        $('#client_id').val(this.client_id);
    },
    'setClientId' : function(client_id){
        this.client_id = client_id;
    },
    'setApiKey1' : function(api_key_1){
        this.api_key_1 = api_key_1;
    },
    'setApiKey2' : function(api_key_2){
        this.api_key_2 = api_key_2;
    },
    'setSecretKey1' : function(secret_key_1){
        this.secret_key_1 = secret_key_1;
    },
    'setSecretKey2' : function(secret_key_2){
        this.secret_key_2 = secret_key_2;
    }
});