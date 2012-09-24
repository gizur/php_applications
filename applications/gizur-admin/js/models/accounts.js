define([
  'Underscore',
  'Backbone'
], function(_, Backbone) {
  var accountsModel = Backbone.Model.extend({
 //    url: 'http://localhost/gizurcloud/api/index.php/api/User/',
     url: RESTServerURL + 'User/',
  });
  return accountsModel;

});
