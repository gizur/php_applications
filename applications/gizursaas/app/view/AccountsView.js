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

// AccountView
//==================
//
// This class has event listeners and controller functions
// and also this class is responsible for 
// updating error messages in the page.

var AccountsView = Stapes.subclass({
    // constructor
    //===========
    //
    constructor : function() {
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

// AccountsView prototype
// ======================
//
// Here we are adding some methods to the AccountsView 
// prototype to handle events
//
AccountsView.proto({
    'bindEventHandlers' : function() {
        // Handle user request to generate API and Secret key 1
        // 
        // This will emit the generateAPIKeyAndSecret1
        // event of this view.
        $('#generateNewAPIAndSecretKey1Button').on('click', function(e) {
            this.emit('generateAPIKeyAndSecret1');
        }.bind(this));

        // Handle user request to generate API and Secret key 2
        // 
        // This will emit the generateAPIKeyAndSecret2
        // event of this view.
        $('#generateNewAPIAndSecretKey2Button').on('click', function(e) {
            this.emit('generateAPIKeyAndSecret2');
        }.bind(this));
        
        // Handle user request to change the first name
        // 
        // This will emit the updateInformation
        // event of this view.
        $('#first_name').on('change', function(e){
            this.emit('updateInformation');
        }.bind(this));
        
        // Handle user request to change the last name
        // 
        // This will emit the updateInformation
        // event of this view.
        $('#last_name').on('change', function(e){
            this.emit('updateInformation');
        }.bind(this));
        
        // Handle user request to change the last name
        // 
        // This will emit the updateInformation
        // event of this view.
        $('#vtigerResetPasswordButton').on('click', function(e){
            this.emit('vtigerResetPasswordButton');
        }.bind(this));
    }
});