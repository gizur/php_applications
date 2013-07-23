/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage controller
 * @author     Prabhat Khera <gizur-ess-prabhat@gizur.com>
 * @version    SVN: $Id$
 *
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, 
 * <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * JavaScript
 *
 */

// UserController
//==================
//
// It controlles the user registration and login method.
// 

define(["jquery", "config", "hasher", "stapes", "UserModel", "UsersView", "jsSHA"], function($, config, hasher, Stapes, UserModel, UsersView, jsSHA) {
    "use strict";
    var UsersController = Stapes.subclass({
        // constructor
        //===========
        //
        // Initialise the object and event handlers
        //
        constructor: function() {
            //Alias this to self
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
                config.account_controller.model = null;
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
                    "first_name": $.trim($first_name.val()),
                    "last_name": $.trim($last_name.val()),
                    "email": $.trim($email.val()),
                    "password": $.trim($password.val()),
                    "re_password": $.trim($re_password.val()),
                    "terms": $.trim($terms.val()),
                    "client_id": $.trim($client_id.val())
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
                    var _url_create = config.rest_server_url + 'User/';
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
                            if (typeof jqXHR === 'undefined' || !self.isValidJSON(jqXHR.responseText)) {
                                self.view.error(config.messages["ACCOUNT_CREATE_ERROR"]);
                            } else {
                                var _data = JSON.parse(jqXHR.responseText);
                                if (!_data.success)
                                    self.view.error(config.messages[_data.error.code]);
                            }
                        },
                        // On success clean the form.
                        success: function(_data) {
                            if (_data.success) {
                                self.view.success(
                                    config.messages["ACCOUNT_REQUEST_RECEIVED"]
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
                                    config.messages["ACCOUNT_CREATE_ERROR"]
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
                if ($login_id.length === 0) {
                    $('#forgotPasswordError').addClass('alert alert-error')
                            .empty()
                            .html("Please enter login id.");
                    setTimeout(function() {
                        $('#forgotPasswordError').removeClass('alert alert-error')
                                .empty();
                    }, 1000);
                    return false;
                } else {
                    $('#forgotPasswordError').addClass('alert alert-success')
                            .empty().html('Processing ...');
                    //Make a forgotpassword request to the server
                    //
                    var _url_forgot = config.rest_server_url + 'User/forgotpassword';
                    $.ajax({
                        url: _url_forgot,
                        type: "POST",
                        dataType: "json",
                        processData: false,
                        data: JSON.stringify({
                            "id": $login_id
                        }),
                        // On error, display the error msg.
                        error: function(jqXHR, textStatus, errorThrown) {
                            var _data = JSON.parse(jqXHR.responseText);

                            $('#forgotPasswordError').removeClass('alert alert-error alert-success')
                                    .empty();
                            if (!_data.success)
                                self.view.error(config.messages[_data.error.code]);

                            $('#forgotPasswordClose').click();
                        },
                        // On success clean the form.
                        success: function(_data) {
                            $('#forgotPasswordError').removeClass('alert alert-error alert-success')
                                    .empty();
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

            // Show Terms of Services
            // ======================
            // This will be emitted when user will request to view 
            // terms of services.
            this.view.on('showTermsCondition', function() {
                // Load terns of services to the server
                //
                $.get('./applications/gizursaas/terms-of-service.txt?_=' +
                        Math.random(), {}, function(html) {
                    $('#termsConditionBody').empty().html(self.nl2br(html, true));
                });
            });

        },
        // Login
        // ======
        //
        // login event, it will be called when user will click on the login
        // login button.
        //
        "login": function(status) {
            var self = this;
            console.log(config.rest_server_url);
            var $email = $('#login_email');
            var $password = $('#login_password');

            if (status === 'success') {
                self.view.success('Login successfull. Please wait...');
                self.model.set({
                    'email': $email.val(),
                    'password': $password.val()
                });
                setTimeout(function() {
                    hasher.setHash('user/' + $email.val() + '/' + Math.random());
                }, 500);
            } else if (status === 'fail') {
                self.view.error('Username or password is invalid.');
            } else if (status === 'empty') {
                self.view.alert('Username or password can\'t be left blank.');
            } else {
                if ($email.val() !== '' && $password.val() !== '') {
                    var _url_login = config.rest_server_url + 'User/login';
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
        },
        // nl2br
        // ============
        // 
        // The function is converting next line char (LF / CR / CRLF) to BR.
        //
        'nl2br': function(str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        },
        // Find if string is a valid JSON
        // ==============================
        //
        // @str : A sring which needs to be validated.
        //
        'isValidJSON': function(str){
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }
    });
    return UsersController;
});