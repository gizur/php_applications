define([
  'jQuery',
  'Underscore',
  'Backbone',
  'models/accounts'
], function($, _, Backbone, accountsModel){
  var accountsCollection = Backbone.Collection.extend({
    model: accountsModel,
    initialize: function(){

    }

  });
 
  return new accountsCollection;
});
