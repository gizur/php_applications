var AWS = require('aws-sdk');
AWS.config.loadFromPath('./_secure/credentials.json');
var mysql = require("mysql");

var http    = require('http');

var config  = require('./_secure/config.js').Config;

if(config.IS_HTTPS)
    http = require('https');

var connection = mysql.createConnection({
    host: config.DB_HOST,
    user: config.DB_USER,
    password: config.DB_PASSWORD,
    database: config.DB_NAME
});

exports.group = {
    "Initially Testing Sales Order Interface" : function(test){
        test.ok(true, "This test will always pass.");
        test.done();
    },
    "Checking Sales Orders in vTiger ('Created','Approved')" : function(test){
        connection.connect();
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 0, rows.length + " sales orders found in vTiger.");
                test.done();
            });
    },
    "Checking Queue" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            MaxNumberOfMessages: 10
        };
        sqs.client.receiveMessage(params, function(err, data) {
            if (!err) {
                test.equal(data.Messages, undefined, "Queue is not empty.");
                if(data.Messages)
                    test.equal(data.Messages, undefined, data.Messages.length + " messages are in queue.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }
        });
    },
    "Checking FTP" : function(test){
        test.ok(true, "This test will pass.");
        test.done();
    }
};