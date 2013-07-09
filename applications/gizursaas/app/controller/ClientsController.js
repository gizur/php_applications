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

// ClientsController
//==================
//
// It has all the functions required to copy client to another client.
// 
define(["jquery", "config", "stapes", "ClientModel", "ClientsView", "jsSHA", "hasher"], function($, config, Stapes, ClientModel, ClientsView, jsSHA, hasher) {
    "use strict";
    var ClientsController = Stapes.subclass({
        // Intitialise the object
        constructor: function(DEFAULT_HASH) {
            //Create a alias of this
            var self = this;
            var adminUsername = 'gizuradmin';
            var adminPassword = 'gizurpassword';
            //Initialise the model and view
            this.model = new ClientModel();
            this.view = new ClientsView();

            this.loadView(adminUsername, adminPassword, DEFAULT_HASH);

            // The following code will prevent 
            // the forms not to submit by default.
            // 
            this.$el = $('form');
            this.$el.on('submit', function(e) {
                e.preventDefault();
            });

            this.view.on({
                // Event to tabulate client data
                //===============================
                //
                // This function tabulates client data in the view.
                //
                'tabulateData': function() {
                    $('#clientTabularDiv tbody').empty();
                    self.model.each(function(client, key) {
                        var $html = "<tr><td>" +
                                "<input type='radio'" +
                                " name='client_key' value='" + key + "'/>" +
                                "</td>" +
                                "<td>" + (client.clientid === undefined ? '-' : client.clientid) + "</td>" +
                                "<td>" + (client.name_1 === undefined ? '-' : client.name_1) + "</td>" +
                                "<td>" + (client.name_2 === undefined ? '-' : client.name_2) + "</td>" +
                                "<td>" + client.id + "</td>" +
                                "</tr>";
                        $('#clientTabularDiv tbody').append($html);
                    });
                },
                // Event to select client
                //=======================
                //
                // This function updates hidden variables from
                // selected client info.
                //
                'selectClient': function() {
                    var $client_key = $('input[name=client_key]:radio:checked').val();
                    var $client = self.model.get($client_key);
                    $('#from_id').attr('value', $client.id);
                },
                // Event to submit copy client form
                //=================================
                //
                // This function submits clients data to the API.
                //
                'copyClientFormSubmit': function() {

                    self.view.success('Processing ...');
                    var fromid = $('#from_id').val();
                    var password = $('#password').val();
                    var client_id = $('#client_id').val();
                    var email = $('#email').val();

                    var hashObj1 = new jsSHA(Math.random(), "TEXT");
                    var security_salt = hashObj1.getHash("SHA-256", "HEX");
                    var hashObj = new jsSHA(
                            password + security_salt, "TEXT"
                            );
                    var hashed_password = hashObj.getHash("SHA-256", "HEX");

                    //Make a registration request to the server
                    //
                    var _url_create = config.rest_server_url + 'Users/copyuser';
                    $.ajax({
                        url: _url_create,
                        type: "POST",
                        dataType: "json",
                        processData: false,
                        data: JSON.stringify({
                            "fromid": fromid,
                            "id": email,
                            "password": hashed_password,
                            "clientid": client_id,
                            "security_salt": security_salt
                        }),
                        headers: {
                            //Add username and password in the headers
                            // to validate the request
                            "X_USERNAME": adminUsername,
                            "X_PASSWORD": adminPassword
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
                                self.view.success('Account has been copied.');
                                $('#email').val('');
                                $('#password').val('');
                                $('#client_id').val('');
                                $('#from_id').val('');
                                self.model.each(function(client, key) {
                                    self.model.remove(key);
                                });
                                self.loadView(adminUsername, adminPassword, DEFAULT_HASH);
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
        },
        // loadview
        // =========
        // 
        // this function gets all the clients, load the template and
        // tabulate all the clients.
        //
        'loadView': function(adminUsername, adminPassword, DEFAULT_HASH) {
            var self = this;
            //Prepare the url to fetch the account details
            var _url = config.rest_server_url + 'Users';

            //Make a Ajax request
            $.ajax({
                url: _url,
                type: "GET",
                dataType: "json",
                headers: {
                    //Add username and password in the headers
                    // to validate the request
                    "X_USERNAME": adminUsername,
                    "X_PASSWORD": adminPassword
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
                    if (_data.success) {
                        self.model.push(_data.result);
                        $.get('./applications/gizursaas/templates/' +
                                'clients.tmp.html?_=' +
                                Math.random(), {}, function(html) {
                            $('#container').empty().html(html);
                            self.view.emit('tabulateData');
                            self.view.bindEventHandlers();
                        });
                    } else {
                        // If an error occured show and error and
                        // take the user to the login page.
                        self.view.error('Username or password is invalid.');
                        setTimeout(function() {
                            hasher.setHash(DEFAULT_HASH);
                        }, 1000);
                    }
                }
            });
        }
    });
    return ClientsController;
});