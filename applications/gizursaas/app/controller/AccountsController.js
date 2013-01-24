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
            alert('generateAPIKeyAndSecret1');
        });
        
        this.model.on('generateAPIKeyAndSecret2', function() {
            
        });
    }
});