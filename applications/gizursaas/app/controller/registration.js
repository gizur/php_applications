$(function() {
   $('#loginButton').click(function(){
       hasher.setHash('user/cikab@gizur.com/xyz');
   });
   var user_controller = new UsersController();
});