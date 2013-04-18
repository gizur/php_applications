/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage view
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

// UserView
//==================
//
// This class has event listeners and functions
// and also this class is responsible for updating 
// error messages in the page.

var ClientsView = Stapes.subclass({
    // constructor
    //===========
    //
    // This will load the registration template and 
    // initialise the event handlers
    constructor : function(model) {
        var self = this;
    },
    // success
    //===========
    //
    // This will be called to update the success msg
    'success' : function(msg){
        $('#errorMessageBox').empty()
        .html(
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
    'error' : function(msg){
        $('#errorMessageBox')
        .html(
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
    'alert' : function(msg){
        $('#errorMessageBox')
        .html(
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
    'bindEventHandlers' : function() {        
        // Handle click event on logout-btn
        // 
        // This will emit the logout event
        // bound to the view.
        $('input[name=client_key]:radio').on('click', function(e){
            this.emit('selectClient');
        }.bind(this));
        
        $('#copyClientFormSubmit').on('click', function(e){
            e.preventDefault();
            this.emit('copyClientFormSubmit');
        }.bind(this));
    }
});