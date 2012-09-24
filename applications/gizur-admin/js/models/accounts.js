define([
  'Underscore',
  'Backbone'
], function(_, Backbone) {
  var accountsModel = Backbone.Model.extend({
     url: RESTServerURL + 'User/',
  });
  return accountsModel;

});
