'use strict';

var AccountModel = Stapes.subclass({
    "first_name" : '',
    "last_name" : '',
    "email" : '',
    "client_id" : '',
    "api_key_1" : '',
    "api_key_2" : '',
    "secret_key_1" : '',
    "secret_key_2" : '',
    constructor : function (){},
    'validate' : function(){
        return true;
    },
    'map_values' : function(){
        $('#first_name').val(this.get('first_name'));
        $('#last_name').val(this.get('last_name'));
        $('#email').val(this.get('email'));
        $('#old_email').val(this.get('email'));
        
        $('#api_key_1').val(this.get('api_key_1'));
        $('#api_key_2').val(this.get('api_key_2'));
        $('#secret_key_1').text(this.get('secret_key_1'));
        $('#secret_key_2').text(this.get('secret_key_2'));
        $('#client_id').val(this.get('client_id'));
    }
});