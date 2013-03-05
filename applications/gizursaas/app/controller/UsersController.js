'use strict';

var UsersController = Stapes.subclass({
    constructor : function() {
        var self = this;
        this.model = new UserModel();
        this.view = new UsersView( this.model );

        this.view.on('registrationSubmit', function(){
            //Form
            this.$el = $("#registrationform");
            var $first_name = this.$el.find("#first_name");
            var $last_name = this.$el.find("#last_name");
            var $email = this.$el.find("#email");
            var $password = this.$el.find("#password");
            var $re_password = this.$el.find("#re_password");
            var $client_id = this.$el.find("#client_id");
            var $terms = this.$el.find("#terms:checked");

            self.model.set({
                "first_name" : $first_name.val(),
                "last_name" : $last_name.val(),
                "email" : $email.val(),
                "password" : $password.val(),
                "re_password" : $re_password.val(),
                "terms" : $terms.val(),
                "client_id" : $client_id.val()
            });
            
            //Validate the User
            if(self.model.validate()){
                                
                var _url = __rest_server_url + 'User/' + encodeURIComponent(self.model.get('email'));
    
                self.view.success('Processing ...');
                
                /* 
                    var hashObj1 = new jsSHA(Math.random(), "TEXT");
                    var security_salt = hashObj1.getHash("SHA-256", "HEX");
                    var hashObj = new jsSHA(self.model.get('password') + security_salt, "TEXT");
                    var hashed_password = hashObj.getHash("SHA-256", "HEX"); 
                 */

                var _url_create = __rest_server_url + 'User/';
                $.ajax({
                    url: _url_create,
                    type: "POST",
                    dataType: "json",
                    processData: false,
                    data: JSON.stringify({
                        "id":self.model.get('email'),
                        "password":self.model.get('password'),
                        "name_1":self.model.get('first_name'),
                        "name_2":self.model.get('last_name'),
                        "address_1":"",
                        "address_2":"",
                        "city":"",
                        "state":"",
                        "postalcode":"",
                        "country":"",
                        "phone_1":"",
                        "clientid":self.model.get('client_id'),
                        "apikey_1":"",
                        "secretkey_1":"",
                        "active_1":"",
                        "apikey_2":"",
                        "secretkey_2":"",
                        "active_2":"",
                        "server":"",
                        "port":"",
                        "username":"",
                        "dbpassword":"",
                        "databasename":""
                    }),
                    error: function(jqXHR, textStatus, errorThrown ) {
                        var _data = JSON.parse(jqXHR.responseText);
                        //_data.error.code == "ERROR" && 
                        if(!_data.success){
                            self.view.error(__messages[_data.error.code]);
                        }
                    },
                    success : function(_data){
                        if(_data.success){
                            self.view.success('Your account has been created. You may login to your account.');
                            $first_name.val(''); 
                            $last_name.val(''); 
                            $email.val('');
                            $password.val(''); 
                            $re_password.val('');
                            $client_id.val('');
                            $terms.attr('checked', false);
                        }else{
                            self.view.error('An error occured while creating your account. Please contact administrator.');
                        }
                    }
                });
            }
        });
    },
    "login" : function(status){
        var self = this;
        
        var $email = $('#login_email');
        var $password = $('#login_password');
        
        if(status === 'success'){
            self.view.success('Login successfull. Please wait...');
            setTimeout(function(){
                hasher.setHash('user/' + $email.val() + '/test');
            }, 500);
        }else if(status === 'fail'){
            self.view.error('Username or password is invalid.');
        }else if(status === 'empty'){
            self.view.alert('Username or password can\'t be left blank.');
        }else{
            if($email.val() !== '' && $password.val() !== ''){
                var _url_login = __rest_server_url + 'User/login';
                $.ajax({
                    url: _url_login,
                    type: "POST",
                    dataType: "json",
                    processData: false,
                    data: JSON.stringify({
                        "id":$email.val(),
                        "password":$password.val()
                    }),
                    error: function(jqXHR, textStatus, errorThrown ) {
                        var _data = JSON.parse(jqXHR.responseText);
                        //_data.error.code == "ERROR" && 
                        if(!_data.success){
                            hasher.setHash('login/fail');
                        }
                    },
                    success : function(_data){
                        if(_data.success){
                            hasher.setHash('login/success');
                        } else {
                            hasher.setHash('login/fail');
                        }
                    }
                });                
            }else{
                hasher.setHash('login/empty');
            }
        }
    }
});