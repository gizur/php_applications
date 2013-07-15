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

// AccountView
//==================
//
// This class has event listeners, functions
// and is responsible for 
// showing error, success or alert messages.
//
define(["jquery", "stapes"], function($, Stapes) {
    "use strict";
    var AccountsView = Stapes.subclass({
        // constructor
        //===========
        //
        constructor: function(model) {
            this.model = model;
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

    // AccountsView prototype
    // ======================
    //
    // Here we are adding some methods to the AccountsView 
    // prototype to handle events
    //
    AccountsView.proto({
        'bindEventHandlers': function() {
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
            $('#first_name').on('change', function(e) {
                this.emit('updateInformation');
            }.bind(this));

            // Handle user request to change the last name
            // 
            // This will emit the updateInformation
            // event of this view.
            $('#last_name').on('change', function(e) {
                this.emit('updateInformation');
            }.bind(this));

            // Handle user request to change the last name
            // 
            // This will emit the updateInformation
            // event of this view.
            $('#vtigerResetPasswordButton').on('click', function(e) {
                this.emit('vtigerResetPasswordButton');
            }.bind(this));
            
            
            // Handle user request to display the background status
            // 
            // This will emit the background status Information
            // event of this view.
            $('#background-id-tab').on('shown', function(e) {
                this.emit('updateBackgroundTab');
            }.bind(this));
             
            
            // Handle copy client button click event
            // 
            $('#copyClientFormSubmit').on('click', function(e) {
                e.preventDefault();
                this.emit('copyClientFormSubmit');
            }.bind(this));
        }
    });
    return AccountsView;
});