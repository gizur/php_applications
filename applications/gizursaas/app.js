require.config({
    "baseUrl": "applications/gizursaas",
    "paths": {
        "app": "app",
        "bootstrap": "lib/bootstrap/js/bootstrap.min",
        "jquery": "lib/jquery-1.9.0.min",
        "stapes": "lib/stapes.min",
        "signals": "lib/signals",
        "crossroads": "lib/crossroads.min",
        "hasher": "lib/hasher.min",
        "jsSHA": "lib/sha256",
        "getHash": "lib/sha256",
        "getHMAC": "lib/sha256",
        "config": "app/config/config",
        "UserModel": "app/model/User",
        "UsersView": "app/view/UsersView",
        "UsersController": "app/controller/UsersController",
        "AccountModel": "app/model/Account",
        "AccountsView": "app/view/AccountsView",
        "AccountsController": "app/controller/AccountsController",
        "ClientModel": "app/model/Client",
        "ClientsView": "app/view/ClientsView",
        "ClientsController": "app/controller/ClientsController"
    },
    shim: {
        'jquery': {
            exports: 'jquery'
        },
        'bootstrap': {
            deps: ['jquery'],
            exports:  "$.fn.popover"
        },
        'jsSHA': {
            exports: 'jsSHA'
        },
        'getHash': {
            deps: ['jsSHA']
        },
        'getHMAC': {
            deps: ['jsSHA']
        }
    },
    enforceDefine: true
});
define("app", function(app) {
    console.log("App loaded.");
    return app;
});
require(['jquery', 'bootstrap'],
     function($){
         return {};
     }
);
// Load the main app module to start the app
requirejs(["app/main"]);
