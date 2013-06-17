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
// and is also responsible for updating 
// error, success or alert messages.
define(["jquery", "stapes", "hasher"], function($, Stapes, hasher) {
    "use strict";
    var UsersView = Stapes.subclass({
        // constructor
        //===========
        //
        // This will load the registration template and 
        // bind the events
        constructor: function(model) {
            var self = this;
            $.get('./applications/gizursaas/templates/registration.tmp.html?_=' +
                    Math.random(), {}, function(html) {
                $('#container').empty().html(html);
                self.bindEventHandlers();
            });
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

    // UsersView prototype
    // ======================
    //
    // Here we are adding some methods to the UsersView 
    // prototype to handle events
    //
    UsersView.proto({
        'bindEventHandlers': function() {
            // Handle click event on loginButton
            // 
            // This event will set the hasher to login,
            // which will process the login function.
            $('#loginButton').on('click', function(e) {
                this.success('Please wait ...');
                hasher.setHash('login');
            }.bind(this));

            // Handle registration form submit event
            // 
            // This will emit the registrationSubmit event
            // and prevent to submit form by default.
            $('#registrationform').on('submit', function(e) {
                e.preventDefault();
                this.emit('registrationSubmit');
            }.bind(this));

            // Handle click event on forgotPassword
            // 
            // This will emit the forgotPassword event
            // bound to the view.
            $('#forgotPasswordButton').on('click', function(e) {
                this.emit('forgotPassword');
            }.bind(this));

            // Handle click event on logout-btn
            // 
            // This will emit the logout event
            // bound to the view.
            $('#logout-btn').on('click', function(e) {
                this.emit('logout');
                $('#logout-btn').hide();
            }.bind(this));

            // Handle showing terms of services
            // 
            $('#showTermsCondition').on('click', function(e) {
                this.emit('showTermsCondition');
            }.bind(this));
        }
    });
    return UsersView;
});