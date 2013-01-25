'use strict';

var UserModel = Stapes.subclass({
    "first_name" : '',
    "last_name" : '',
    "email" : '',
    "password" : '',
    "re_password" : '',
    "terms" : '',
    constructor : function (first_name, last_name, email, password, re_password, terms) {
        this.first_name = first_name;
        this.last_name = last_name;
        this.email = email;
        this.password = password;
        this.re_password = re_password;
        this.terms = terms;        
    },
    'assign_values' : function (first_name, last_name, email, password, re_password, terms) {
        this.first_name = first_name;
        this.last_name = last_name;
        this.email = email;
        this.password = password;
        this.re_password = re_password;
        this.terms = terms;
    },
    'validate' : function(){
        return true;
    }
});