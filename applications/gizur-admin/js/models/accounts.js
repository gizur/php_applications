define([
  'Underscore',
  'Backbone'
], function(_, Backbone) {
  var accountsModel = Backbone.Model.extend({
     url: 'http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/User/',
  });
  return accountsModel;

});
