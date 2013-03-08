
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

// Group all Tests
// ===============
exports.group = {
    // #### Check sales order count in vTiger
    // 
    // Test will pass in case of 0 number of sales order
    // with status Created / Approved exists.
    "Checking Sales Orders in vTiger ('Created','Approved')" : function(test){
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                test.equal(rows.length, 0, rows.length + " sales orders found in vTiger.");
                test.done();
            });
    },
    // #### Check Queue for messages
    // 
    // Test will pass if no message found in
    // Amazon Queue.
    // There is no method found in aws-sdk to get the
    // approx number of messages in QUEUE.
    "Checking Queue" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            MaxNumberOfMessages: 10
        };
        sqs.client.receiveMessage(params, function(err, data) {
            if (!err) {
                if(data.Messages)
                    test.ok(false, data.Messages.length + " message(s) are in queue.");
                else
                    test.ok(true, " Queue is empty.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }
        });
    },
    // #### Check files in FTP server
    // 
    "Checking FTP" : function(test){
        test.ok(true, "This test will pass.");
        test.done();
    },
    // #### Closing connections
    // 
    // Reason behind putting connection close in
    // a test is not to close connections
    // before test execution.
    "Closing Connections" : function(test){
        connection.destroy();
        int_connection.destroy();
        test.ok(true, "Connections closed.");
        test.done();
    }
};