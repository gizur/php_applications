/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage view
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

// UserView
//==================
//
// This class has event listeners, functions
// and is also responsible for showing 
// error, success or alert messages.
define(["jquery", "stapes"], function($, Stapes) {
    "use strict";
    var ClientsView = Stapes.subclass({
        // constructor
        //===========
        //
        // This will load the registration template and 
        // bind the event.
        constructor: function(model) {
            var self = this;
        },
        // success
        //===========
        //
        // This will be called to update the success msg
        'success': function(msg) {
            $('#errorMessageBox').empty().html(
                '<div class="alert alert-success">' +
                '<button data-dismiss="alert" class="close" ' +
                'type="button">×</button>' +
                msg + "</div>"
            );
        },
        // error
        //===========
        //
        // This will be called to update the error msg
        'error': function(msg) {
            $('#errorMessageBox').empty().html(
                '<div class="alert alert-error">' +
                '<button data-dismiss="alert" class="close" ' +
                'type="button">×</button>' +
                msg + "</div>"
            );
        },
        // alert
        //===========
        //
        // This will be called to update the alert msg
        'alert': function(msg) {
            $('#errorMessageBox').empty().html(
                '<div class="alert">' +
                '<button data-dismiss="alert" class="close" ' +
                'type="button">×</button>' +
                msg + "</div>"
            );
        }
    });

    // ClientsView prototype
    // ======================
    //
    // Here we are adding some methods to the UsersView 
    // prototype to handle events
    //
    ClientsView.proto({
        'bindEventHandlers': function() {
            // Handle click event on select
            // client radio button
            //
            $('input[name=client_key]:radio').on('click', function(e) {
                this.emit('selectClient');
            }.bind(this));
            // Handle copy client form submit event
            //
            $('#copyClientFormSubmit').on('click', function(e) {
                e.preventDefault();
                this.emit('copyClientFormSubmit');
            }.bind(this));
        }
    });
    return ClientsView;
});