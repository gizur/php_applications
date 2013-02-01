'use strict';

var UsersController = Stapes.subclass({
    constructor : function() {
        var self = this;
        this.model = new UserModel();
        this.view = new UsersView( this.model );

        this.model.on('addUser', function() {
            var text = self.model.get('email');
            $("#list_users ul").append('<li>' + text + '</li>');
        });
        
        this.model.on('registrationSubmit', function(){
            //Form
            this.$el = $("#registrationform");
            var $first_name = this.$el.find("#first_name");
            var $last_name = this.$el.find("#last_name");
            var $email = this.$el.find("#email");
            var $password = this.$el.find("#password");
            var $re_password = this.$el.find("#re_password");
            var $client_id = this.$el.find("#client_id");
            var $terms = this.$el.find("#terms:checked");

            self.model.set({
                "first_name" : $first_name.val(),
                "last_name" : $last_name.val(),
                "email" : $email.val(),
                "password" : $password.val(),
                "re_password" : $re_password.val(),
                "terms" : $terms.val(),
                "client_id" : $client_id.val()
            });
            
            //Validate the User
            if(self.model.validate()){
                self.model.emit('addUser');
                
                $first_name.val(''); 
                $last_name.val(''); 
                $email.val('');
                $password.val(''); 
                $re_password.val('');
            }
        });
    }
});