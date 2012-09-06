define([
  'jQuery',
  'Underscore',
  'Backbone',
  'models/accounts',
  'text!templates/home/main.html'
], function($, _, Backbone, accountsModel, mainHomeTemplate){

  var mainHomeView = Backbone.View.extend({
    el: $("#page"),
    render: function(){
      var that = this;
      if ($('#element_1').val()!='' && $('#element_1').val()!=undefined) {
	      var Account = new accountsModel;
	      Account.url = Account.url + $('#element_1').val();
	      Account.fetch({
		  type: 'GET',
		  success: function(account) {
		      $(that.el).html(_.template(mainHomeTemplate, {account: account.attributes.result, _:_}));
		  }
	      });
      } else {
	      $(that.el).html(mainHomeTemplate);
      }
    },
    events: {
        'click .create-account': 'createAccount'
    },
    createAccount: function() {
        var Account = new accountsModel;
        var attributes = new Object();
        var fields = $('#form_pnc').serializeArray();
        attributes['id'] = $('#element_1').val(); 
        for (index in fields) {
            attributes[fields[index]['name']] = fields[index]['value']; 
        }
 
        Account.set(attributes);
        Account.save(null,{
            type: 'POST',
            success: function (data) {
                console.log(data);
            }
        });
    }
  });
  return new mainHomeView;
});
