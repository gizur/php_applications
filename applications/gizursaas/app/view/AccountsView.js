'use strict';

var AccountsView = Stapes.subclass({
    constructor : function() {
        var self = this;
        $.get('templates/home.tmp.html?_=' + Math.random(),{},function(html){
            $('#container').empty().html(html);
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
            this.model.emit('generateAPIKeyAndSecret1');
        }.bind(this));

        $('#generateNewAPIAndSecretKey2Button').on('click', function(e) {
            this.model.emit('generateAPIKeyAndSecret2');
        }.bind(this));
    }
});