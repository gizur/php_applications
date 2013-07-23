
/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage config
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

// Config
// =======
// 
// This file stores all the configuration needed through-out
// the application.

// Server URL
// ==========
// This is where all request will be made.
// This URL is actually, where this apllication will be host
// with the api path.
define(["jquery"], function($) {
    "use strict";
    var config = (typeof window === 'undefined') ? exports.Config = {} : window.Config = {};
    config.rest_server_url = 'https://c2.gizur.com/api/';

    // Messages
    // ========
    // Following are the set of messages showed to 
    // user on perticular response / error code.
    config.messages = {
        "CLIENT_ID_INVALID": 'Client ID is not available.',
        "EMAIL_INVALID": 'Email is already registred.',
        "ERROR": 'An error occured, Please contact administrator.',
        "WRONG_CREDENTIALS": 'Credentials are in valid.',
        "WRONG_FROM_CLIENT": 'From client is incorrect.',
        "INVALID_EMAIL": "Wrong email id provided.",
        "ACCOUNT_REQUEST_RECEIVED": "Your request to create an account has been received. You shall receive an email shortly for further instructions.",
        "ACCOUNT_CREATE_ERROR": 'An error occured while creating your account. Please contact administrator.',
        "MANDATORY_FIELDS_MISSING" : 'All fields are required.'
    };

    // Varibales to hold controllers object
    //
    config.user_controller = null;
    config.account_controller = null;
    config.clients_controller = null;


    return config;
});