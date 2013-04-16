/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage model
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

// AccountModel
//==================
//
// This class has model properties, validation function
//

var ClientModel = Stapes.subclass({
    "first_name" : '',
    "last_name" : '',
    "email" : '',
    "client_id" : '',
    "api_key_1" : '',
    "api_key_2" : '',
    "secret_key_1" : '',
    "secret_key_2" : '',
    "password" : '',
    "address_1" : '',
    "address_2" : '',
    "city" : '',
    "state" : '',
    "postalcode" : '',
    "country" : "",
    "phone_1" : "",
    "active_1" : "",
    "active_2" : "",
    "server" : "",
    "port" : "",
    "username" : "",
    "dbpassword" : "",
    "databasename" : "",
    "security_salt" : "",
    "id_sequence" : '',
    constructor : function (){},
    // Validate
    // ========
    // This function is valodating the User model
    // for the the above properties.
    // 
    'validate' : function(){
        return true;
    }
});