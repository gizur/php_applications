$(function() {
    $('#generateNewAPIAndSecretKey1Button').click(function(){
        account_controller.model.emit('generateAPIKeyAndSecret1');
    });
    $('#generateNewAPIAndSecretKey2Button').click(function(){
        account_controller.model.emit('generateAPIKeyAndSecret2');
    });
});