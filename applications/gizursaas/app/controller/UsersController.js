'use strict';

var UsersController = Stapes.subclass({
    constructor : function() {
        var self = this;
        this.model = new UserModel();
        this.view = new UsersView( this.model );

        //Form
        this.$el = $("#registrationform");
        var $first_name = this.$el.find("#first_name");
        var $last_name = this.$el.find("#last_name");
        var $email = this.$el.find("#email");
        var $password = this.$el.find("#password");
        var $re_password = this.$el.find("#re_password");

        this.$el.on('submit', function(e) {
            e.preventDefault();

            self.model.assign_values( $first_name.val(), $last_name.val(), $email.val(), 
                $password.val(), $re_password.val());
            
            //Validate the User
            if(self.model.validate())
                self.model.emit('addUser');
            
            $first_name.val(''); 
            $last_name.val(''); 
            $email.val('');
            $password.val(''); 
            $re_password.val('');
        });
        
        this.model.on('addUser', function() {
            var text = self.model.getEmail();
            $("#list_users ul").append('<li>' + text + '</li>');
        });
    }
});