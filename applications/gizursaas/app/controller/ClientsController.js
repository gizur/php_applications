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

// ClientsController
//==================
//
// This class is responsible for fetching and updating information
// updated by the user

var ClientsController = Stapes.subclass({
    // Intitialise the object
    constructor: function(DEFAULT_HASH) {
        //Create a alias of this
        var self = this;

        //Initialise the model and view
        this.model = new ClientModel();
        this.view = new ClientsView();

        //Prepare the url to fetch the account details
        var _url = __rest_server_url + 'Users';

        //Make a Ajax request
        $.ajax({
            url: _url,
            type: "GET",
            dataType: "json",
            headers: {
                //Add username and password in the headers
                // to validate the request
                "X_USERNAME":'gizuradmin',
                "X_PASSWORD":'gizurpassword'
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
                            Math.random(),{},function(html){
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
            // This fuunction tabulate data in the view.
            //
            'tabulateData': function() {
                self.model.each(function(client,key) {
                    var $html = "<tr><td>" +
                        "<input type='radio'" +
                        " name='client_key' value='" + key + "'/>" + 
                        "</td>" +
                        "<td>" + client.clientid + "</td>" +
                        "<td>" + (client.name_1 === 'undefined' ? '-': client.name_1) + "</td>" +
                        "<td>" + (client.name_2 === 'undefined' ? '-': client.name_2) + "</td>" +
                        "<td>" + client.id + "</td>" +
                    "</tr>";
                    $('#clientTabularDiv tbody').append($html);
                });
            },
            'selectClient': function() {
                var $client_key = $('input[name=client_key]:radio:checked').val();
                console.log(self.model.get($client_key));
            }
        });
    }
});