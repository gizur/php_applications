define([
  'Underscore',
  'Backbone'
], function(_, Backbone) {
  var accountsModel = Backbone.Model.extend({
    defaults: {
      name: 10
    },
    initialize: function(){
       alert('Happy backboning ');
    }
    
  });
  return accountsModel;

});
