'use strict';

var AccountsView = Stapes.subclass({
    constructor : function() {
        var self = this;
        $.get('templates/home.tmp.html?_=' + Math.random(),{},function(html){
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

AccountsView.proto({
    'bindEventHandlers' : function() {
        $('#generateNewAPIAndSecretKey1Button').on('click', function(e) {
            this.emit('generateAPIKeyAndSecret1');
        }.bind(this));

        $('#generateNewAPIAndSecretKey2Button').on('click', function(e) {
            this.emit('generateAPIKeyAndSecret2');
        }.bind(this));
        
        $('#first_name').on('change', function(e){
            this.emit('updateInformation');
        }.bind(this));
        
        $('#last_name').on('change', function(e){
            this.emit('updateInformation');
        }.bind(this));
    }
});