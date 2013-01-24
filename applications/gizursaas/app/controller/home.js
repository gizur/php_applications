$(function() {
    var account_controller = new AccountsController();
    $('#generateNewAPIAndSecretKey1Button').click(function(){
        account_controller.model.emit('generateAPIKeyAndSecret1');
    });
});