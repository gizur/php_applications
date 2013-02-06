'use strict';

var UserModel = Stapes.subclass({
    "first_name" : '',
    "last_name" : '',
    "email" : '',
    "password" : '',
    "re_password" : '',
    "terms" : '',
    "client_id" : '',
    constructor : function () {},
    'validate' : function(){
        if(this.get('first_name').length == 0){
            this.error('First name can\'t be left blank.');
            return false;
        }
        if(this.get('last_name').length == 0){
            this.error('Last name can\'t be left blank.');
            return false;
        }
        if(this.get('client_id').length == 0){
            this.error('Client Id can\'t be left blank.');
            return false;
        }
        if(this.get('email').length == 0){
            this.error('Email can\'t be left blank.');
            return false;
        }
        if(this.get('password').length == 0){
            this.error('Password can\'t be left blank.');
            return false;
        }
        if(this.get('re_password').length == 0){
            this.error('Re-password can\'t be left blank.');
            return false;
        }
        if(this.get('terms') != 'y'){
            this.error('You must agree to Gizur SaaS terms & conditions.');
            return false;
        }
        var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
        if(!pattern.test(this.get('email'))){
            this.error('Email is not a valid email.');
            return false;
        }
        if(this.get('re_password') != this.get('password')){
            this.error('Password and re-password do not match.');
            return false;
        }
        return true;
    },
    'success' : function(msg){
        $('#errorMessageBox').removeClass('alert-error')
        $('#errorMessageBox').addClass('alert alert-success')
        .empty()
        .html('<button data-dismiss="alert" class="close" type="button">×</button>' + msg);
    },
    'error' : function(msg){
        $('#errorMessageBox').removeClass('alert-success')
        $('#errorMessageBox').addClass('alert alert-error')
        .empty()
        .html('<button data-dismiss="alert" class="close" type="button">×</button>' + msg);
    }
});