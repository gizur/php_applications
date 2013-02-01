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
    'assign_values' : function (first_name, last_name, email, 
        client_id, api_key_1, 
        api_key_2, secret_key_1, secret_key_2) {
        
        console.log(client_id + api_key_1 + api_key_2 + secret_key_1 + secret_key_2);
        
        this.first_name = first_name;
        this.last_name = last_name;
        this.email = email;
        this.client_id = client_id;
        this.api_key_1 = api_key_1;
        this.api_key_2 = api_key_2;
        this.secret_key_1 = secret_key_1;
        this.secret_key_2 = secret_key_2;
    },
    'validate' : function(){
        return true;
    },
    'map_values' : function(){
        $('#first_name').val(this.first_name);
        $('#last_name').val(this.last_name);
        $('#email').val(this.email);
        $('#old_email').val(this.email);
        
        $('#api_key_1').val(this.api_key_1);
        console.log(this.api_key_1);
        $('#api_key_2').val(this.api_key_2);
        console.log(this.api_key_2);
        $('#secret_key_1').text(this.secret_key_1);
        console.log(this.secret_key_1);
        $('#secret_key_2').text(this.secret_key_2);
        console.log(this.secret_key_2);
        //$('#old_email').val(_data.result.id);
        $('#client_id').val(this.client_id);
        console.log(this.client_id);
    }
});