$(function() {
   $('#loginButton').click(function(){
       hasher.setHash('login');
   });
   $('#registrationform').submit(function(e){
       e.preventDefault();
       user_controller.model.emit('registrationSubmit');
   });
});