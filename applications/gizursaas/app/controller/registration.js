$(function() {
   $('#loginButton').click(function(){
       hasher.setHash('login');
   });
   var user_controller = new UsersController();
});