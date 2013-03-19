'use strict';

var AccountsController = Stapes.subclass({
    constructor: function(DEFAULT_HASH, client_email) {
        var self = this;

        this.model = new AccountModel();
        this.view = new AccountsView();

        var _url = __rest_server_url + 'User/' + encodeURIComponent(client_email);

        $.ajax({
            url: _url,
            type: "GET",
            dataType: "json",
            headers: {
                "HTTP_X_USERNAME":user_controller.model.get('email'),
                "HTTP_X_PASSWORD":user_controller.model.get('password')
            },
            error: function() {
                self.view.error('Username or password is invalid.');
                setTimeout(function() {
                    hasher.setHash(DEFAULT_HASH);
                }, 1000);
            },
            success: function(_data) {
                if (_data.success) {
                    self.model.set({
                        "first_name": _data.result.name_1,
                        "last_name": _data.result.name_2,
                        "email": _data.result.id,
                        "client_id": _data.result.clientid,
                        "api_key_1": _data.result.apikey_1,
                        "api_key_2": _data.result.apikey_2,
                        "secret_key_1": _data.result.secretkey_1,
                        "secret_key_2": _data.result.secretkey_2,
                        "password": _data.result.password,
                        "address_1": _data.result.address_1,
                        "address_2": _data.result.address_2,
                        "city": _data.result.city,
                        "state": _data.result.state,
                        "postalcode": _data.result.postalcode,
                        "country": _data.result.country,
                        "phone_1": _data.result.phone_1,
                        "active_1": _data.result.active_1,
                        "active_2": _data.result.active_2,
                        "server": _data.result.server,
                        "port": _data.result.port,
                        "username": _data.result.username,
                        "dbpassword": _data.result.dbpassword,
                        "databasename": _data.result.databasename,
                        "security_salt": _data.result.security_salt,
                        "id_sequence": _data.result.id_sequence
                    });
                    self.model.map_values();
                } else {
                    self.view.error('Username or password is invalid.');
                    setTimeout(function() {
                        hasher.setHash(DEFAULT_HASH);
                    }, 1000);
                }
            }
        });

        this.$el = $('form');
        this.$el.on('submit', function(e) {
            e.preventDefault();
        });

        this.view.on({
            'generateAPIKeyAndSecret1': function() {
                var _url = __rest_server_url + 'User/keypair1/' + encodeURIComponent(self.model.get('email'));
                $.ajax({
                    url: _url,
                    type: "PUT",
                    dataType: "json",
                    headers: {
                        "HTTP_X_USERNAME":user_controller.model.get('email'),
                        "HTTP_X_PASSWORD":user_controller.model.get('password')
                    },
                    error: function() {
                        self.view.error('An error occured while re-generating the key pair. Please try again.');
                    },
                    success: function(_data) {
                        if (_data.success) {
                            self.view.success('Key pair has been generated successfully.');

                            //Set modified values to the Account Object
                            self.model.set({
                                'api_key_1': _data.result.apikey_1,
                                'secret_key_1': _data.result.secretkey_1
                            });
                            //Map values to the page
                            self.model.map_values();
                            $('#generateNewAPIAndSecretKey1Close').click();
                        } else {
                            self.view.error('An error occured while re-generating the key pair. Please try again.');
                        }
                    }
                });
            },
            'generateAPIKeyAndSecret2': function() {
                var _url = __rest_server_url + 'User/keypair2/' + encodeURIComponent(self.model.get('email'));
                $.ajax({
                    url: _url,
                    type: "PUT",
                    dataType: "json",
                    headers: {
                        "HTTP_X_USERNAME":user_controller.model.get('email'),
                        "HTTP_X_PASSWORD":user_controller.model.get('password')
                    },
                    error: function() {
                        self.view.error('An error occured while re-generating the key pair. Please try again.');
                    },
                    success: function(_data) {
                        if (_data.success) {
                            self.view.success('Key pair has been generated successfully.');

                            //Set modified values to the Account Object
                            self.model.set({
                                'api_key_2': _data.result.apikey_2,
                                'secret_key_2': _data.result.secretkey_2
                            });
                            //Map values to the page
                            self.model.map_values();
                            $('#generateNewAPIAndSecretKey2Close').click();
                        } else {
                            self.view.error('An error occured while re-generating the key pair. Please try again.');
                        }
                    }
                });
            },
            'updateInformation': function() {
                var _url = __rest_server_url + 'User';
                
                $.ajax({
                    url: _url,
                    type: "PUT",
                    dataType: "json",
                    processData: false,                    
                    headers: {
                        "HTTP_X_USERNAME":user_controller.model.get('email'),
                        "HTTP_X_PASSWORD":user_controller.model.get('password')
                    },
                    data: JSON.stringify({
                        "id": self.model.get('email'),
                        "password": self.model.get('password'),
                        "name_1": $('#first_name').val(),
                        "name_2": $('#last_name').val(),
                        "address_1": self.model.get('address_1'),
                        "address_2": self.model.get('address_2'),
                        "city": self.model.get('city'),
                        "state": self.model.get('state'),
                        "postalcode": self.model.get('postalcode'),
                        "country": self.model.get('country'),
                        "phone_1": self.model.get('phone_1'),
                        "clientid": self.model.get('client_id'),
                        "apikey_1": self.model.get('api_key_1'),
                        "secretkey_1": self.model.get('secret_key_1'),
                        "active_1": self.model.get('active_1'),
                        "apikey_2": self.model.get('api_key_2'),
                        "secretkey_2": self.model.get('secret_key_2'),
                        "active_2": self.model.get('active_2'),
                        "server": self.model.get('server'),
                        "port": self.model.get('port'),
                        "username": self.model.get('username'),
                        "dbpassword": self.model.get('dbpassword'),
                        "databasename": self.model.get('databasename'),
                        "security_salt": self.model.get('security_salt'),
                        "id_sequence": self.model.get('id_sequence')
                    }),
                    error: function() {
                        self.view.error('An error occured while updating the information. Please try again.');
                        //Revert back the values
                        self.model.map_values();
                    },
                    success: function(_data) {
                        if (_data.success) {
                            self.view.success('Information updated successfully.');

                            self.model.set({
                                'first_name': $('#first_name').val(),
                                'last_name': $('#last_name').val()
                            });
                            //Map values to the page
                            self.model.map_values();
                        } else {
                            self.view.error('An error occuredwhile updating the information. Please try again.');
                        }
                    }
                });
            }
        });
    }
});