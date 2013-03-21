
/**
 * This file contains routing function used throughout Gizur SaaS.
 *
 * @package    Gizur SaaS
 * @subpackage config
 * @author     Prabhat Khera <prabhat.khera@essindia.co.in>
 * @version    SVN: $Id$
 *
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, 
 * <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * CSS Version 3
 *
 */

//Instanciate the controllers
var user_controller = null;
var account_controller = null;

$(function() {
    var DEFAULT_HASH = 'registration';
    //setup hasher
    hasher.prependHash = "!";
    //setup crossroads
    var route1 = crossroads.addRoute('registration');
    var route2 = crossroads.addRoute('user/{client_id}/{session_id}');
    var route3 = crossroads.addRoute('login/:status:');
    
    //To log all routes un-comment the following line
    //crossroads.routed.add(console.log, console);
    
    route1.matched.add(function(){
        user_controller = new UsersController();
    });
    
    route3.matched.add(function(status){
        user_controller.login(status);
    });
    
    route2.matched.add(function(client_id, session_id){     
        account_controller = new AccountsController(DEFAULT_HASH, client_id);
    });
    
    //only required if you want to set a default value

    if(! hasher.getHash()){
        hasher.setHash(DEFAULT_HASH);
    }

    function parseHash(newHash, oldHash){
        // second parameter of crossroads.parse() is the "defaultArguments" and should be an array
        // so we ignore the "oldHash" argument to avoid issues.
        crossroads.parse(newHash);
    }

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for hash changes
});
