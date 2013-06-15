require.config({
    "baseUrl": "applications/gizursaas",
    "paths": {
        "app": "app",
        "jquery": "lib/jquery-1.9.0.min",
        "stapes": "lib/stapes.min",
        "signals": "lib/signals",
        "crossroads": "lib/crossroads.min",
        "hasher": "lib/hasher.min",
        "jsSHA": "lib/sha256",
        "bootstrap": "lib/bootstrap/js/bootstrap.min",
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
    "shim": {
        "require.define": ['require']
    }
});

// Load the main app module to start the app
requirejs(["app/main"]);