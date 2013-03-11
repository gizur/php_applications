
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

// #### Connect with the databases.
connection.connect();
int_connection.connect();

// Configure Expected Test Result
// ==============================

var messagesInQueueBefore = 1, 
    messagesInQueueAfter = 6,
    fileInFTPBefore = 0,
    fileInFTPAfter = 5;
// Group all Tests
// ===============
exports.group = {
    // **Check Queue before hitting cron job 3**
    "Checking SQS for messages before hitting Cron job 3" : function(test){
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
    // **Hitting Cron Job 3**
    "Hitting Cron Job 3" : function(test){
        exec("chmod +x " + config.PHP_BATCHES_3, function (error, stdout, stderr) {
            if (error !== null)
                console.log("Error in chmod +x " + config.PHP_BATCHES_3);
            else{
                exec(config.PHP_BATCHES_3, function (error, stdout, stderr) {
                    if (error !== null){
                        test.ok(false, "Error executing " + config.PHP_BATCHES_3);
                        test.done();
                    }else{
                        test.ok(true, "Executed : " + config.PHP_BATCHES_3 + " : " + stdout);
                        test.done();
                    }
                });
            }
        });
    },
    // **Checking SQS messages after hitting cron job 3**
    "Checking SQS for messages after hitting Cron job 3" : function(test){
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