
//------------------------------
//
// 2013-03-08, Prabhat Khera
//
// Copyright Gizur AB 2012
//
// Functions:
//  * Test jobs cron-style
//
// Install with dependencies: npm install 
//
// Documentation is 'docco style' - http://jashkenas.github.com/docco/
//
// Using Google JavaScript Style Guide - 
// http://google-styleguide.googlecode.com/svn/trunk/javascriptguide.xml
//
//------------------------------


"use strict";


// Includes
// =========
var AWS = require('aws-sdk');
var mysql = require("mysql");
var http    = require('http');
var fs = require('fs');
var exec = require('child_process').exec;

// Configs
// =======
// Load / read the AWS credentials
AWS.config.loadFromPath('./_secure/credentials.json');

// Load the configurations
var config  = require('./_secure/config.js').Config;

// If server requires secure connection
// use https
if(config.IS_HTTPS)
    http = require('https');

// Connection to vtiger MySQL db
var connection = mysql.createConnection({
    host: config.DB_HOST,
    user: config.DB_USER,
    password: config.DB_PASSWORD,
    database: config.DB_NAME
});

// Connection to integration MySQL db
var int_connection = mysql.createConnection({
    host: config.DB_I_HOST,
    user: config.DB_I_USER,
    password: config.DB_I_PASSWORD,
    database: config.DB_I_NAME
});

// ### Function testPhpBatch
// ========================
// 
// Used to test php batch files
// Accept 2 parameters
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
    
    // In case of error
    req.on('error', function(e) {
        test.ok(false, "Error : " + e.message);
        test.done();
    });
    
    //End the request
    req.end();
}

// #### Connect with the databases.
connection.connect();
int_connection.connect();

// Configure Expected Test Result
// ==============================

var messagesInQueueBefore = 1, 
    messagesInQueueAfter = 6,
    salesOrderIntegrationBefore = 5,
    salesOrderIntegrationAfter = 0;
// Group all Tests
// ===============
exports.group = {
    // **Check sales orders in vtiger before hitting cron job 2**
    "Checking Sales Order In Integration Database before hitting Cron job 2" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM salesorder_interface " +
            "WHERE sostatus IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err) throw err;
                
                var result = false;
                if(rows.length == salesOrderIntegrationBefore)
                    result = true;
                
                test.ok(result, salesOrderIntegrationBefore + " sales orders expected, " + rows.length + " found.");
                test.done();
            });
    },
    // **Check Queue before hitting cron job 2**
    "Checking SQS for messages before hitting Cron job 2" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {
                var result = false;
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                if(cnt == messagesInQueueBefore)
                    result = true;
                
                test.ok(result, messagesInQueueBefore + " messages expected, " + cnt + " found.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }            
        });
    },
    // **Hitting Cron Job 2**
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
                
                var result = false;
                if(rows.length == salesOrderIntegrationAfter)
                    result = true;
                
                test.ok(result, salesOrderIntegrationAfter + " sales orders expected, " + rows.length + " found.");
                test.done();
            });
    },
    // **Checking SQS messages after hitting cron job 2**
    "Checking SQS for messages" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {
                
                var result = false;
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                if(cnt == messagesInQueueAfter)
                    result = true;
                
                test.ok(result, messagesInQueueAfter + " messages expected, " + cnt + " found.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }            
        });
    },
    // #### Closing connections
    // 
    // Reason behind putting closing connections in
    // a test is, not to close connections
    // before test execution.
    "Closing Connections" : function(test){
        connection.destroy();
        int_connection.destroy();
        test.ok(true, "Connections closed.");
        test.done();
    }
};