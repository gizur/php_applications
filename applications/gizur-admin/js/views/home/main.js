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
        'click #showSecretKey_1, #showSecretKey_2': 'showSecretKey',
        'click #makeInactive_1, #makeInactive_2': 'makeInactive',
        'change input[id!=email]': 'saveAccount'
    },
    makeInactive: function(e) {
        var id = $(e.currentTarget).attr('id').split("_")[1];
            if ($('label[for=apikey_'+id+']:contains((Active))').length == 1) {
                r = confirm("Are you sure the key should be inactivated, all clients using this key need to be updated with a new API Key and secret");
                if (r == true) {
                    $('label[for=apikey_'+id+']:contains((Active))').html('(Inactive)');
                    $(e.currentTarget).html('Make Active');
                    $('#active_' + id).val('No');
                    this.saveAccount();
                }
            } else {
                $('label[for=apikey_'+id+']:contains((Inactive))').html('(Active)');
                $(e.currentTarget).html('Make Inactive');
                $('#active_' + id).val('Yes');
                this.saveAccount();
            }
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
                      if ($('#active_1').val() == 'No') {
                          $('label[for=apikey_1]:contains((Active))').html('(Inactive)');
                          $('#makeInactive_1').html('Make Active');
                      }
                      if ($('#active_2').val() == 'No') {
                          $('label[for=apikey_2]:contains((Active))').html('(Inactive)');
                          $('#makeInactive_2').html('Make Active');
                      }
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
                //console.log(data);
            }
        });
    }
  });
  return new mainHomeView;
});
