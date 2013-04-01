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

var UsersView = Stapes.subclass({
    constructor : function(model) {
        var self = this;
        $.get('./applications/gizursaas/templates/registration.tmp.html?_=' + Math.random(),{},function(html){
            $('#container').empty().html(html);
            self.bindEventHandlers();
        });
    },
    'success' : function(msg){
        $('#errorMessageBox').removeClass('alert-error')
        .addClass('alert alert-success')
        .empty()
        .html('<button data-dismiss="alert" class="close" type="button">×</button>' + msg);
    },
    'error' : function(msg){
        $('#errorMessageBox').removeClass('alert-success')
        .addClass('alert alert-error')
        .empty()
        .html('<button data-dismiss="alert" class="close" type="button">×</button>' + msg);
    },
    'alert' : function(msg){
        $('#errorMessageBox').removeClass('alert-error')
        .removeClass('alert-success')
        .addClass('alert')
        .empty()
        .html('<button data-dismiss="alert" class="close" type="button">×</button>' + msg);
    }
});

UsersView.proto({
    'bindEventHandlers' : function() {
        $('#loginButton').on('click', function(e) {
            hasher.setHash('login');
        }.bind(this));
        $('#registrationform').on('submit', function(e) {
            e.preventDefault();
            this.emit('registrationSubmit');
        }.bind(this));
        $('#forgotPasswordButton').on('click', function(e){
            this.emit('forgotPassword');
        }.bind(this));
    }
});