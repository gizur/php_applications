define([
  'jQuery',
  'Underscore',
  'Backbone',
  'text!templates/home/main.html'
], function($, _, Backbone, mainHomeTemplate){

  var mainHomeView = Backbone.View.extend({
    el: $("#page"),
    render: function(){
      alert('Am I being called');
      this.el.html(mainHomeTemplate);
      $.get('http://localhost/api/index.php/api/Contacts').error(function(jqXHR){
          alert(jqXHR.status);
      });
    }
  });
  return new mainHomeView;
});
