define([
  'Underscore',
  'Backbone'
], function(_, Backbone) {
  var accountsModel = Backbone.Model.extend({
     url: 'http://http://gizurtrailerapp-env.elasticbeanstalk.com/applications/index.php/api/User/',
  });
  return accountsModel;

});
