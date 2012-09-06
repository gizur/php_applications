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
      $(that.el).html(_.template(mainHomeTemplate, {account: {}, _:_}));
    },
    events: {
        'click .save-account': 'saveAccount',
        'blur #email': 'searchAccount',
        'click #showSecretKey_1, #showSecretKey_2': 'showSecretKey'
    },
    showSecretKey: function(e) {
        var id = $(e.currentTarget).attr('id').split("_")[1];
        alert("Secret Key : " + $('#secretkey_' + id).val());
    },
    searchAccount: function() {
        var that = this;
	var Account = new accountsModel;
        if ($.trim($('#email').val())!='') {
	      Account.url = Account.url + encodeURIComponent($('#email').val());
	      Account.fetch({
		  type: 'GET',
		  success: function(account) {
		      $(that.el).html(_.template(mainHomeTemplate, {account: account.attributes.result, _:_}));
		  }
	      });
        }
    }, 
    saveAccount: function() {
        var Account = new accountsModel;
        var attributes = new Object();
        var fields_pnc = $('#form_pnc').serializeArray();
        var fields_apikeys = $('#form_apikeys').serializeArray();
        var fields_dbcredentials = $('#form_dbcredentials').serializeArray(); 
        attributes['id'] = $('#email').val(); 
        for (index in fields_pnc) {
            attributes[fields_pnc[index]['name']] = fields_pnc[index]['value']; 
        }

        for (index in fields_apikeys) {
            attributes[fields_apikeys[index]['name']] = fields_apikeys[index]['value']; 
        }

         for (index in fields_dbcredentials) {
            attributes[fields_dbcredentials[index]['name']] = fields_dbcredentials[index]['value']; 
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
