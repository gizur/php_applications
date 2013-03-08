var AWS = require('aws-sdk');
AWS.config.loadFromPath('./_secure/credentials.json');
var mysql = require("mysql");

var http    = require('http');
var fs = require('fs');

var config  = require('./_secure/config.js').Config;

if(config.IS_HTTPS)
    http = require('https');

var connection = mysql.createConnection({
    host: config.DB_HOST,
    user: config.DB_USER,
    password: config.DB_PASSWORD,
    database: config.DB_NAME
});

var int_connection = mysql.createConnection({
    host: config.DB_I_HOST,
    user: config.DB_I_USER,
    password: config.DB_I_PASSWORD,
    database: config.DB_I_NAME
});

// Accept two parameters
// ====================
// test   : test case
// batch  : path to file

function testPhpBatch(test, batch){
    
    // Set the options
    var options = {
        hostname: config.HOSTNAME,
        port: config.SERVER_PORT,
        path: batch,
        method: 'GET'
    };

    // Request the host
    var req = http.request(options, function(res) {
        var body = '';
        // On success
        res.on('data', function (chunk) {
            body += chunk;            
        });
        res.on('end', function (){
            test.ok(true, "Success : " + body.message);
            test.done();
        });
    });
    
    // In case of error,
    // log the error message
    req.on('error', function(e) {
        test.ok(false, "Error : " + e.message);
        test.done();
    });
    
    //End the request
    req.end();
}

connection.connect();
int_connection.connect();

exports.group = {
    "Testing Sales Order Interface Cron Job 2" : function(test){
        test.ok(true, "This test will always pass.");
        test.done();
    },
    "Checking Sales Order In Integration Database before hitting Cron job 2" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM salesorder_interface " +
            "WHERE sostatus IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 1, rows.length + " sales orders found in integration db.");
                test.done();
            });
    },
    "Hitting Cron Job 2" : function(test){
        testPhpBatch(test, config.PHP_BATCHES_2);
    },
    "Checking Sales Order In Integration Database After hitting Cron job 2" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM salesorder_interface " +
            "WHERE sostatus IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 1, rows.length + " sales orders found in integration db.");
                test.done();
            });
    },
    "Checking SQS for messages" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            MaxNumberOfMessages: 1
        };
        sqs.client.receiveMessage(params, function(err, data) {
            if (!err) {
                test.notEqual(data.Messages, undefined, "Queue is empty.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }            
        });
    },
    "Closing Connections" : function(test){
        connection.destroy();
        int_connection.destroy();
        test.ok(true, "Connections closed.");
        test.done();
    }
};