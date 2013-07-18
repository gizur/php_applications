/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage model
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

// AccountModel
//==================
//
// This class has model properties, validation function
//
define(["jquery", "stapes"], function($, Stapes) {
    "use strict";
    var AccountModel = Stapes.subclass({
        "first_name": '',
        "last_name": '',
        "email": '',
        "client_id": '',
        "api_key_1": '',
        "api_key_2": '',
        "secret_key_1": '',
        "secret_key_2": '',
        "password": '',
        "address_1": '',
        "address_2": '',
        "city": '',
        "state": '',
        "postalcode": '',
        "country": "",
        "phone_1": "",
        "active_1": "",
        "active_2": "",
        "server": "",
        "port": "",
        "username": "",
        "dbpassword": "",
        "databasename": "",
        "security_salt": "",
        "id_sequence": '',
        "status": '',
        constructor: function() {
        },
        // map_values function
        // ===================
        //
        // This fuction is used to update values in html page.

        'map_values': function() {
            $('#first_name').val(this.get('first_name'));
            $('#last_name').val(this.get('last_name'));
            $('#email').val(this.get('email'));
            $('#old_email').val(this.get('email'));
            $('#api_key_1').val(this.get('api_key_1'));
            $('#api_key_2').val(this.get('api_key_2'));
            $('#secret_key_1').text(this.get('secret_key_1'));
            $('#secret_key_2').text(this.get('secret_key_2'));
            $('#client_id').val(this.get('client_id'));
        },
   
        // timeconverter function
        // ===================
        //
        // This fuction is used to convert timestamp to 
        // date values in background details page.        
        
        'timeConverter': function (UNIX_timestamp){
            var a = new Date(UNIX_timestamp*1000);
            return a.toLocaleString();
        }    
    });
    return AccountModel;
});