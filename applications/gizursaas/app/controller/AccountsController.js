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

// AccountController
//==================
//
// This class is controlls all post login activies of user.
//
define(["jquery", "config", "hasher", "stapes", "AccountModel", "AccountsView"], function($, config, hasher, Stapes, AccountModel, AccountsView) {
    "use strict";
    var AccountsController = Stapes.subclass({
        // Intitialise the object
        constructor: function(DEFAULT_HASH, _email) {
            //Create a alias of this
            var self = this;

            //Initialise the model and view
            this.model = new AccountModel();
            this.view = new AccountsView(this.model);
            this.userModel = config.user_controller.model;

            //Prepare the url to fetch the account details
            var _url = config.rest_server_url + 'User/' +
                    encodeURIComponent(_email);

            //Make a Ajax request
            $.ajax({
                url: _url,
                type: "GET",
                dataType: "json",
                headers: {
                    // Add username and password in the headers
                    // to validate the request
                    "X_USERNAME": self.userModel.get('email'),
                    "X_PASSWORD": self.userModel.get('password')
                },
                error: function() {
                    // If an error occured show and error and
                    // take the user to the login page.
                    self.view.error('Username or password is invalid.');
                    setTimeout(function() {
                        hasher.setHash(DEFAULT_HASH);
                    }, 1000);
                },
                success: function(_data) {
                    // Map the values on sucess
                    // with model attributes
                    if (_data.success === true) {
                        self.model.set({
                            "first_name": _data.result.name_1,
                            "last_name": _data.result.name_2,
                            "email": _data.result.id,
                            "client_id": _data.result.clientid,
                            "api_key_1": _data.result.apikey_1,
                            "api_key_2": _data.result.apikey_2,
                            "secret_key_1": _data.result.secretkey_1,
                            "secret_key_2": _data.result.secretkey_2,
                            "password": _data.result.password,
                            "address_1": _data.result.address_1,
                            "address_2": _data.result.address_2,
                            "city": _data.result.city,
                            "state": _data.result.state,
                            "postalcode": _data.result.postalcode,
                            "country": _data.result.country,
                            "phone_1": _data.result.phone_1,
                            "active_1": _data.result.active_1,
                            "active_2": _data.result.active_2,
                            "server": _data.result.server,
                            "port": _data.result.port,
                            "username": _data.result.username,
                            "dbpassword": _data.result.dbpassword,
                            "databasename": _data.result.databasename,
                            "security_salt": _data.result.security_salt,
                            "id_sequence": _data.result.id_sequence,
                            "status": _data.result.status
                        });
                        $.get('./applications/gizursaas/templates/' +
                                'home.tmp.html?_=' +
                                Math.random(), {}, function(html) {
                            $('#container').empty().html(html);
                            self.model.map_values();                            
                            self.view.emit("updateCopyClientTab");
                            self.view.bindEventHandlers();
                            $('#logout-btn').show();
                            var vLink = window.location.protocol + '//' +
                                    window.location.host +
                                    '/' + self.model.get('client_id') + '/';
                            $('#vtigerLink').empty().html(
                                "<a href='" + vLink +
                                "' target='_blank'>Login to vTiger CRM</a>"
                            );
                        });
                    } else {
                        // If an error occured show and error and
                        // take the user to the login page.
                        self.view.error('Username / password is invalid.');
                        setTimeout(function() {
                            hasher.setHash(DEFAULT_HASH);
                        }, 1000);
                    }
                }
            });

            // The following code will prevent 
            // the forms not to submit by default.
            // 
            this.$el = $('form');
            this.$el.on('submit', function(e) {
                e.preventDefault();
            });

            this.view.on({
                // Event to generate API and SECRET key 1
                //=======================================
                //
                // This fuunction make PUT request to the server to
                // generate API and SECRET key 1
                'generateAPIKeyAndSecret1': function() {
                    var _url = config.rest_server_url + 'User/keypair1/' +
                            encodeURIComponent(self.model.get('email'));
                    $.ajax({
                        url: _url,
                        type: "PUT",
                        dataType: "json",
                        headers: {
                            //Add username and password in the headers
                            // to validate the request
                            "X_USERNAME": self.userModel.get('email'),
                            "X_PASSWORD": self.userModel.get('password')
                        },
                        error: function() {
                            // Show the error in case error received.
                            self.view.error(
                                'An error occured while re-generating ' +
                                'the key pair. Please try again.'
                            );
                        },
                        success: function(_data) {
                            if (_data.success) {
                                // Update the values on success
                                self.view.success(
                                    'Key pair has been generated successfully.'
                                );

                                //Set modified values to the Account Object
                                self.model.set({
                                    'api_key_1': _data.result.apikey_1,
                                    'secret_key_1': _data.result.secretkey_1
                                });
                                // Update page with the new values
                                self.model.map_values();
                                // Close the model dialog
                                $('#generateNewAPIAndSecretKey1Close').click();
                            } else {
                                self.view.error(
                                    'An error occured while re-generating ' +
                                    'the key pair. Please try again.'
                                );
                            }
                        }
                    });
                },
                // Event to generate API and SECRET key 2
                //=======================================
                //
                // This fuunction make PUT request to the server to
                // generate API and SECRET key 2
                'generateAPIKeyAndSecret2': function() {
                    var _url = config.rest_server_url + 'User/keypair2/' +
                            encodeURIComponent(self.model.get('email'));
                    $.ajax({
                        url: _url,
                        type: "PUT",
                        dataType: "json",
                        headers: {
                            //Add username and password in the headers
                            // to validate the request
                            "X_USERNAME": self.userModel.get('email'),
                            "X_PASSWORD": self.userModel.get('password')
                        },
                        error: function() {
                            // Show the error in case error received.
                            self.view.error(
                                'An error occured while re-generating the' +
                                ' key pair. Please try again.'
                            );
                        },
                        success: function(_data) {
                            if (_data.success) {
                                self.view.success(
                                    'Key pair has been generated successfully.'
                                );

                                //Set modified values to the Account Object
                                self.model.set({
                                    'api_key_2': _data.result.apikey_2,
                                    'secret_key_2': _data.result.secretkey_2
                                });
                                // Update page with the new values
                                self.model.map_values();
                                // Close the model dialog
                                $('#generateNewAPIAndSecretKey2Close').click();
                            } else {
                                self.view.error(
                                    'An error occured while re-generating the' +
                                    ' key pair. Please try again.'
                                );
                            }
                        }
                    });
                },
                // Event to update information
                //============================
                //
                // This fuunction make PUT request to the server to
                // information updated by the user
                'updateInformation': function() {

                    self.view.success('Please wait ...');
                    var _url = config.rest_server_url + 'User';

                    $.ajax({
                        url: _url,
                        type: "PUT",
                        dataType: "json",
                        processData: false,
                        headers: {
                            "X_USERNAME": self.userModel.get('email'),
                            "X_PASSWORD": self.userModel.get('password')
                        },
                        data: JSON.stringify({
                            "id": self.model.get('email'),
                            "password": self.model.get('password'),
                            "name_1": $.trim($('#first_name').val()),
                            "name_2": $.trim($('#last_name').val()),
                            "address_1": self.model.get('address_1'),
                            "address_2": self.model.get('address_2'),
                            "city": self.model.get('city'),
                            "state": self.model.get('state'),
                            "postalcode": self.model.get('postalcode'),
                            "country": self.model.get('country'),
                            "phone_1": self.model.get('phone_1'),
                            "clientid": self.model.get('client_id'),
                            "apikey_1": self.model.get('api_key_1'),
                            "secretkey_1": self.model.get('secret_key_1'),
                            "active_1": self.model.get('active_1'),
                            "apikey_2": self.model.get('api_key_2'),
                            "secretkey_2": self.model.get('secret_key_2'),
                            "active_2": self.model.get('active_2'),
                            "server": self.model.get('server'),
                            "port": self.model.get('port'),
                            "username": self.model.get('username'),
                            "dbpassword": self.model.get('dbpassword'),
                            "databasename": self.model.get('databasename'),
                            "security_salt": self.model.get('security_salt'),
                            "id_sequence": self.model.get('id_sequence'),
                            "status": self.model.get('status')
                        }),
                        error: function() {
                            self.view.error( 'An error occured while' + 
                                'updating the information. Please try again.'
                            );
                            //Revert back the values
                            self.model.map_values();
                        },
                        success: function(_data) {
                            if (_data.success) {
                                self.view.success('Information updated ' +
                                    'successfully.'
                                );

                                self.model.set({
                                    'first_name': $('#first_name').val(),
                                    'last_name': $('#last_name').val()
                                });
                                //Map values to the page
                                self.model.map_values();
                            } else {
                                self.view.error(
                                    'An error occuredwhile updating the' +
                                    ' information. Please try again.'
                                );
                            }
                        }
                    });
                },
                // Event to reset vTiger admin password
                //=====================================
                //
                // This fuunction make PUT request to the server to
                // reset vtiger admin password
                'vtigerResetPasswordButton': function() {

                    if (!confirm('Would you like to reset your vTiger admin password?'))
                        return false;

                    self.view.success('Please wait ...');
                    var _url = config.rest_server_url + 'User/vtiger/' +
                            self.userModel.get('email');

                    $.ajax({
                        url: _url,
                        type: "PUT",
                        dataType: "json",
                        processData: false,
                        headers: {
                            "X_USERNAME": self.userModel.get('email'),
                            "X_PASSWORD": self.userModel.get('password')
                        },
                        data: JSON.stringify({}),
                        error: function() {
                            self.view.error(
                                'An error occured while resetting ' +
                                'the password. Please try again.'
                            );
                        },
                        success: function(_data) {
                            if (_data.success) {
                                self.view.success(
                                    'Password has been reset successfully.' +
                                    ' Please check your email.'
                                );
                            } else {
                                self.view.error(
                                    'An error occured while resetting the' +
                                    ' password. Please try again.'
                                );
                            }
                        }
                    });
                },
                // Event to update current client info in copy-client tab
                // ======================================================
                "updateCopyClientTab": function() {
                    $('#from_id').attr('value', self.model.get("email"));
                    $('#copy-client table tbody').empty().html("<tr><td>" +
                        "Client Id</td><td>" + 
                        self.model.get("client_id") + "</td></tr>" +
                        "<tr><td>" +
                        "Email</td><td>" + 
                        self.model.get("email") + "</td></tr>"
                    );
                },
                
                // function to display background details
                // ======================================
                "updateBackgroundTab": function() {
                    var _url = config.rest_server_url + 
                            'Background/backgroundstatus';
                    $('#background-id table tbody').empty().
                        html("<tr><td>Loading ...</td></tr>");
                            
                    $.ajax({
                        url: _url,
                        type: "GET",
                        dataType: "json",
                        headers: {
                            //Add username and password in the headers
                            // to validate the request
                            "X_USERNAME": self.userModel.get('email'),
                            "X_PASSWORD": self.userModel.get('password'),
                            "X_CLIENTID": self.model.get('client_id')
                        },
                        error: function() {
                            // Show the error in case error received.
                            self.view.error(config.messages['ERROR']);
                        },
                        success: function(_data) {
                           var str='';
                            if (_data.success) {
                                var res = JSON.parse(_data.result);
                                if (res.length > 0) {
                                    str += "<tr><td> Client Id </td>";
                                    str += "<td> Ticket No </td>";
                                    str += "<td> Message </td>";
                                    str += "<td> Username </td>";
                                    str += "<td> Date </td></tr>";

                                    for (var ix in res) {
                                        var msg = JSON.parse(res[ix].message);
                                        str += "<tr><td>" + res[ix].clientid +
                                            "</td><td>" + res[ix].ticket_no +
                                            "</td><td>" + msg.join('<br/>') +
                                            "</td><td>" + res[ix].username +
                                            "</td><td>" +
                                            self.model.timeConverter(res[ix].datetime) +
                                            "</td><tr>";
                                    }
                                    $('#background-id table tbody').empty().html(str);
                                } else {
                                    $('#background-id table tbody').empty().
                                    html("<tr><td>No record found!!</td></tr>");
                                }
                            } else {
                                self.view.error(config.messages['ERROR']);
                            }
                        }
                    });
                 
                },
                // Event to submit copy client form
                //=================================
                //
                // This function submits clients data to the API.
                //
                'copyClientFormSubmit': function() {

                    var password = $.trim($('#new_password').val());
                    var client_id = $.trim($('#new_client_id').val());
                    var email = $.trim($('#new_email').val());

                    if(password.length === 0 ||
                       client_id.length === 0 ||
                       email.length === 0) {
                        self.view.error(
                            "New client id, email and password " +
                            "can not be left blank."
                        );
                        return false;
                    }
                    
                    self.view.success('Processing ...');
                    
                    var hashObj1 = new jsSHA(Math.random(), "TEXT");
                    var security_salt = hashObj1.getHash("SHA-256", "HEX");
                    var hashObj = new jsSHA(
                        password + security_salt, "TEXT"
                    );
                    var hashed_password = hashObj.getHash("SHA-256", "HEX");

                    //Make a registration request to the server
                    //
                    var _url_create = config.rest_server_url + 'User/copyuser';
                    $.ajax({
                        url: _url_create,
                        type: "POST",
                        dataType: "json",
                        processData: false,
                        data: JSON.stringify({
                            "id": email,
                            "password": hashed_password,
                            "clientid": client_id,
                            "security_salt": security_salt
                        }),
                        headers: {
                            //Add username and password in the headers
                            // to validate the request
                            "X_USERNAME": self.userModel.get('email'),
                            "X_PASSWORD": self.userModel.get('password')
                        },
                        //If error occured, it will display the error msg.
                        error: function(jqXHR, textStatus, errorThrown) {
                            var _data = JSON.parse(jqXHR.responseText);

                            if (!_data.success)
                                self.view.error(config.messages[_data.error.code]);
                        },
                        // On success clean the form.
                        success: function(_data) {
                            if (_data.success) {
                                self.view.success(
                                    'Request to copy your ' + 
                                    'account to new has been received. ' +
                                    'You\'ll receive an email shortly.'
                                );
                                $('#new_email').val('');
                                $('#new_password').val('');
                                $('#new_client_id').val('');
                                $('#from_id').val('');
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
        }
    });
    return AccountsController;
});