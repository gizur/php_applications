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

// Accept one parameter
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

exports.group = {
    "Testing Sales Order Interface Cron Job 1" : function(test){
        test.ok(true, "This test will always pass.");
        test.done();
    },
    "Run Cron Job 1" : function(test){
        testPhpBatch(test, config.PHP_BATCHES_1);
    },
    "Checking Sales Orders in vTiger ('Created','Approved')" : function(test){
        connection.connect();
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 1, rows.length + " sales orders found in vTiger.");
                test.done();
            });
    }
};