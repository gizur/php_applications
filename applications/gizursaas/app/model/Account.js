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
        $('#first_name').val(this.first_name);
        $('#last_name').val(this.last_name);
        $('#email').val(this.email);
        $('#old_email').val(this.email);
        
        $('#api_key_1').val(this.api_key_1);
        $('#api_key_2').val(this.api_key_2);
        $('#secret_key_1').text(this.secret_key_1);
        $('#secret_key_2').text(this.secret_key_2);
        $('#client_id').val(this.client_id);
    }
});