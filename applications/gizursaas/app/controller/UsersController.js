/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage controller
 * @author     Prabhat Khera <prabhat.khera@essindia.co.in>
 * @version    SVN: $Id$
 *
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, 
 * <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * JavaScript
 *
 */

'use strict';

// UserController
//==================
//
// This class has user resistration and login method.
// 

var UsersController = Stapes.subclass({
    // constructor
    //===========
    //
    // This will load the home template and 
    // initialise the event handlers
    constructor: function() {
        //Alias this with self
        var self = this;
        // Initialse model and view
        // 
        // On initialisation of view it will load the registration
        // template.
        this.model = new UserModel();
        this.view = new UsersView(this.model);

        // Logout
        // ======
        // This event will be called when user will click on the
        // logout link.
        this.view.on('logout', function() {
            self.model = null;
            self.view.success('You have been successfully logged-out.');
            account_controller.model = null;
            hasher.setHash('logout');
        });
        
        // Registration Submit
        // ===================
        // This will be emitted when user will submit the 
        // registration form.
        this.view.on('registrationSubmit', function() {
            //Get values from the form on submission 
            //and assign it to model.
            this.$el = $("#registrationform");
            var $first_name = this.$el.find("#first_name");
            var $last_name = this.$el.find("#last_name");
            var $email = this.$el.find("#email");
            var $password = this.$el.find("#password");
            var $re_password = this.$el.find("#re_password");
            var $client_id = this.$el.find("#client_id");
            var $terms = this.$el.find("#terms:checked");

            self.model.set({
                "first_name": $first_name.val(),
                "last_name": $last_name.val(),
                "email": $email.val(),
                "password": $password.val(),
                "re_password": $re_password.val(),
                "terms": $terms.val(),
                "client_id": $client_id.val()
            });

            //Validate the User.
            if (self.model.validate()) {

                self.view.success('Processing ...');
                
                //Hash the password with the security salt.
                
                var hashObj1 = new jsSHA(Math.random(), "TEXT");
                var security_salt = hashObj1.getHash("SHA-256", "HEX");
                var hashObj = new jsSHA(
                        self.model.get('password') + security_salt, "TEXT"
                );
                var hashed_password = hashObj.getHash("SHA-256", "HEX");

                //Make a registration request to the server
                //
                var _url_create = __rest_server_url + 'User/';
                $.ajax({
                    url: _url_create,
                    type: "POST",
                    dataType: "json",
                    processData: false,
                    data: JSON.stringify({
                        "id": self.model.get('email'),
                        "password": hashed_password,
                        "name_1": self.model.get('first_name'),
                        "name_2": self.model.get('last_name'),
                        "address_1": "",
                        "address_2": "",
                        "city": "",
                        "state": "",
                        "postalcode": "",
                        "country": "",
                        "phone_1": "",
                        "clientid": self.model.get('client_id'),
                        "apikey_1": "",
                        "secretkey_1": "",
                        "active_1": "",
                        "apikey_2": "",
                        "secretkey_2": "",
                        "active_2": "",
                        "server": "",
                        "port": "",
                        "username": "",
                        "dbpassword": "",
                        "databasename": "",
                        "security_salt": security_salt
                    }),
                    //If error occured, it will display the error msg.
                    error: function(jqXHR, textStatus, errorThrown) {
                        var _data = JSON.parse(jqXHR.responseText);
                        
                        if (!_data.success)
                            self.view.error(__messages[_data.error.code]);
                    },
                    // On success clean the form.
                    success: function(_data) {
                        if (_data.success) {
                            self.view.success(
                                    'Your account has been created. ' +
                                    'You may login to your account.'
                            );
                            $first_name.val('');
                            $last_name.val('');
                            $email.val('');
                            $password.val('');
                            $re_password.val('');
                            $client_id.val('');
                            $terms.attr('checked', false);
                        } else {
                            self.view.error(
                                    'An error occured while creating your' +
                                    ' account. Please contact administrator.'
                            );
                        }
                    }
                });
            }
        });
        // Forgot Password
        // ===============
        // This will be emitted when user will request for
        // the forgot password.
        this.view.on('forgotPassword', function() {
            var $login_id = $('#login_id').val();
            if($login_id.length === 0){
                $('#forgotPasswordError').addClass('alert alert-error')
                        .empty()
                        .html("Please enter login id.");
                return false;
            }else{
                //Make a forgotpassword request to the server
                //
                var _url_forgot = __rest_server_url + 'User/forgotpassword';
                $.ajax({
                    url: _url_forgot,
                    type: "POST",
                    dataType: "json",
                    processData: false,
                    data: JSON.stringify({
                        "id": $login_id
                    }),
                    //If error occured, it will display the error msg.
                    error: function(jqXHR, textStatus, errorThrown) {
                        var _data = JSON.parse(jqXHR.responseText);
                        
                        if (!_data.success)
                            self.view.error(__messages[_data.error.code]);
                        
                        $('#forgotPasswordClose').click();
                    },
                    // On success clean the form.
                    success: function(_data) {
                        if (_data.success) {
                            self.view.success(
                                    'An email has been sent to' +
                                    ' your registered email for ' +
                                    'further instruction.'
                            );
                            $('#login_id').val('');
                            $('#forgotPasswordClose').click();
                        } else {
                            self.view.error(
                                    'An error occured while ' +
                                    'resetting your password. Please ' +
                                    'contact administrator.'
                            );
                            $('#forgotPasswordClose').click();
                        }
                    }
                });
            }                
        });
    },
    // Login
    // ======
    // This function will be called when user will click on the
    // login button.
    "login": function(status) {
        var self = this;

        var $email = $('#login_email');
        var $password = $('#login_password');

        if (status === 'success') {
            self.view.success('Login successfull. Please wait...');
            self.model.set({
                'email':$email.val(),
                'password':$password.val()
            });
            setTimeout(function() {
                hasher.setHash('user/' + $email.val() + '/' + Math.random());
            }, 500);
        } else if (status ==='fail') {
            self.view.error('Username or password is invalid.');
        } else if (status === 'empty') {
            self.view.alert('Username or password can\'t be left blank.');
        } else {
            if ($email.val() !== '' && $password.val() !== '') {
                var _url_login = __rest_server_url + 'User/login';
                $.ajax({
                    url: _url_login,
                    type: "POST",
                    dataType: "json",
                    processData: false,
                    data: JSON.stringify({
                        "id": $email.val(),
                        "password": $password.val()
                    }),
                    error: function(jqXHR, textStatus, errorThrown) {
                        var _data = JSON.parse(jqXHR.responseText);
                        //_data.error.code == "ERROR" && 
                        if (!_data.success)
                            hasher.setHash('login/fail');
                    },
                    success: function(_data) {
                        if (_data.success)
                            hasher.setHash('login/success');
                        else
                            hasher.setHash('login/fail');
                    }
                });
            } else {
                hasher.setHash('login/empty');
            }
        }
    }
});