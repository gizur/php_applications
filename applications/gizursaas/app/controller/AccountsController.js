'use strict';

var AccountsController = Stapes.subclass({
    constructor : function() {
        var self = this;
        //Form
        this.$el = $("#apisettingform");
        var $client_id = this.$el.find("#client_id");
        var $api_key_1 = this.$el.find("#api_key_1");
        var $api_key_2 = this.$el.find("#api_key_2");
        var $secret_key_1 = $("#secret_key_1");
        var $secret_key_2 = $("#secret_key_2");

        this.model = new AccountModel($client_id.val(), $api_key_1.val(), $api_key_2.val(), $secret_key_1.text(), $secret_key_2.text());
        this.view = new AccountsView( this.model );
        
        this.$el.on('submit', function(e) {
            e.preventDefault();
        });
        
        this.model.on('generateAPIKeyAndSecret1', function() {
            console.log('generateAPIKeyAndSecret1');
            
            var _url = __rest_server_url + 'User/keypair1/' + encodeURIComponent(__client_email);
    
            $.ajax({
                url: _url,
                type: "PUT",
                dataType: "json",
                error: function() {
                    $('#errorMessageBox').removeClass('alert-success')
                    $('#errorMessageBox').addClass('alert alert-error')
                    .empty()
                    .html('<button data-dismiss="alert" class="close" type="button">×</button>An error occured while re-generating the key pair. Please try again.');
                },
                success : function(_data){
                    if(_data.success){
                        $('#errorMessageBox').removeClass('alert-error')
                        $('#errorMessageBox').addClass('alert alert-success')
                        .empty()
                        .html('<button data-dismiss="alert" class="close" type="button">×</button>Key pair has been generated successfully.');
                        $('generateNewAPIAndSecretKey1').hide();
                        //Set modified values to the Account Object
                        self.model.set({'api_key_1':_data.result.apikey_1,'secret_key_1':_data.result.secretkey_1});
                        //Map values to the page
                        self.model.emit('mapValues');
                        
                    }else{
                        $('#errorMessageBox').removeClass('alert-success')
                        $('#errorMessageBox').addClass('alert alert-error')
                        .empty()
                        .html('<button data-dismiss="alert" class="close" type="button">×</button>An error occured while re-generating the key pair. Please try again.');
                    }
                }
            });
        });
        
        this.model.on('generateAPIKeyAndSecret2', function() {
            console.log('generateAPIKeyAndSecret2');
            
            var _url = __rest_server_url + 'User/keypair2/' + encodeURIComponent(__client_email);
    
            $.ajax({
                url: _url,
                type: "PUT",
                dataType: "json",
                error: function() {
                    $('#errorMessageBox').removeClass('alert-success')
                    $('#errorMessageBox').addClass('alert alert-error')
                    .empty()
                    .html('<button data-dismiss="alert" class="close" type="button">×</button>An error occured while re-generating the key pair. Please try again.');
                },
                success : function(_data){
                    if(_data.success){
                        $('#errorMessageBox').removeClass('alert-error')
                        $('#errorMessageBox').addClass('alert alert-success')
                        .empty()
                        .html('<button data-dismiss="alert" class="close" type="button">×</button>Key pair has been generated successfully.');
                        
                        $('generateNewAPIAndSecretKey2').hide();
                        //Set modified values to the Account Object
                        self.model.set({'api_key_2':_data.result.apikey_2,'secret_key_2':_data.result.secretkey_2});
                        //Map values to the page
                        self.model.emit('mapValues');
                        
                    }else{
                        $('#errorMessageBox').removeClass('alert-success')
                        $('#errorMessageBox').addClass('alert alert-error')
                        .empty()
                        .html('<button data-dismiss="alert" class="close" type="button">×</button>An error occured while re-generating the key pair. Please try again.');
                    }
                }
            });
        });
    }
});