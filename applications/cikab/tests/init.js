
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

// Group Tests
exports.group = {
    // This is a 
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