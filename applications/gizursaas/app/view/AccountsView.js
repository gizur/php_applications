'use strict';

var AccountsView = Stapes.subclass({
    constructor : function(model) {
        var self = this;
        self.model = model;
    },
    'renderHome' : function(){
        var self = this;
        $.get('templates/home.tmp.html?_=' + Math.random(),{},function(html){
            $('#container').empty().html(html);
        });
        $('#generateNewAPIAndSecretKey1Button').click(function(){
            self.model.emit('generateAPIKeyAndSecret1');
        });
        $('#generateNewAPIAndSecretKey2Button').click(function(){
            self.model.emit('generateAPIKeyAndSecret2');
        });
        self.model.map_values();
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