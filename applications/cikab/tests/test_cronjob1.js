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
    "Testing Sales Order Interface Cron Job 1" : function(test){
        test.ok(true, "This test will always pass.");
        test.done();
    },
    "Checking Sales Orders in vTiger ('Created','Approved') before hitting cron job 1" : function(test){
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 1, rows.length + " sales orders found in vTiger.");
                test.done();
            });
    },
    "Hitting Cron Job 1" : function(test){
        testPhpBatch(test, config.PHP_BATCHES_1);
    },
    "Checking Sales Orders in vTiger ('Created','Approved') after hitting cron job 1" : function(test){
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 0, rows.length + " sales orders found in vTiger.");
                test.done();
            });
    },
    "Checking Sales Order In Integration Database" : function(test){
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
    "Closing Connections" : function(test){
        connection.destroy();
        int_connection.destroy();
        test.ok(true, "Connections closed.");
        test.done();
    }
};